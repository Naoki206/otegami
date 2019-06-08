<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use Request
use DB;
use App\User;
use App\Post;
use Auth;
use Socialite;
use Illuminate\Support\Facades\Log;
use Abraham\TwitterOAuth\TwitterOAuth;

class FormController extends Controller
{
	function returnForm() {
		if (Auth::check()) {
			$user_id = Auth::user()->id;
			$posts = DB::table('posts')->where('user_id', $user_id)->paginate(10);
			return view('form', compact('posts'));
		}
		return view('welcome');
	}

	function messageSend(Request $request) {
		$user_id = Auth::user()->id;
		$latest_post_time =  DB::table('posts')->where('user_id', $user_id)->orderBy('created_at', 'desc')->first()->created_at;
		$now = date("Y-m-d H:i:s");
		$one_minute_later = (date("Y-m-d H:i:s",strtotime($latest_post_time . "+1 minute")));
		if ($now < $one_minute_later) {
			return redirect('/form')->with('flash_message', '少し時間を空けて再度送信し直しててください');
		}

		$validate_num = config('const.numberOfCharacter');
		$validated_data = $request->validate([
			'text' => 'required|max:' . $validate_num,
		]);
		$text = Input::get('text');
		$ng_words_list = DB::table('ngwords')->get();
		$user_id = Auth::user()->id;
		$random_user_record = DB::table('users')->inRandomOrder()->where('id', '<>', $user_id)->first();
		$random_user_id = $random_user_record->twitter_id;
		foreach ($ng_words_list as $ng_word) {
		 	$ng_word = $ng_word->ng_word;
			if(stripos($text, $ng_word) !== false) {
				DB::table('ng_messages')->insert([
				    'message' => $text,
					'destination_twitter_id' => $random_user_id,
					'user_id' => $user_id
				]);
				return redirect('/form')->with('flash_message', 'あなたが送信しようとしたメッセージには適切でない単語が含まれるため、送信    には管理側のチェックが必要になります。送信されるまで時間を要することがありますので、先ほどとは異なるメッセージを再送信することをおすすめします。');
			}
		}

		$uniq_id1 = uniqid();
		$uniq_id2 = uniqid();

		$token = DB::table('tokens')->get()->first();
		$twitter_access_token = $token->twitter_access_token;
		$twitter_access_token_secret = $token->twitter_access_token_secret;

		$connection = new TwitterOAuth(
			config('twitter.consumer_key'),
			config('twitter.consumer_secret'),
			$twitter_access_token,
			$twitter_access_token_secret
		);

		$message = $connection->post('direct_messages/events/new', [
			'event' => [
				'type' => 'message_create',
				'message_create' => [
					'target' => [
						'recipient_id' => $random_user_id  
					],
					'message_data' => [
						'text' => $text . ' (返信用URL) : ' . route('Replyform', ['reply_id' => $uniq_id1]) 
					]  
				]
			]
		], true);

		if (isset($message->errors)) {
			Log::info($message->errors[0]->message);
			return redirect('/form')->with('flash_message', '送信に失敗しました。');
		};

		$reciever_id  = $random_user_record->id;

		$post = Post::create([
			'text' => $text,
			'user_id' => $user_id,
			'reply_id' => $uniq_id1, 
			'destination_id' => $reciever_id,
			'post_id' => $uniq_id2
		]);

		$post->save();

		return redirect('/form')->with('flash_message', '送信しました。');
	}

	function returnReplyForm($reply_id) {
		if (Auth::check()) {
			$user_id = Auth::user()->id;
			$destination_record = DB::table('posts')->where('reply_id', $reply_id)->first();
			$post_id = $destination_record->post_id;
			$messages = DB::table('posts')->where('post_id', $post_id);
			$posts = $messages->paginate(10);
			$first_post = $messages->first();
			$received_message = $destination_record;

			return view('replyForm', compact('first_post', 'user_id', 'posts', 'reply_id', 'received_message'));
		}
		return view('welcome');
	}

	function replySend(Request $request) {
		$reply_id = Input::get('reply_id');
		$user_id = Auth::user()->id;
		$latest_post_time =  DB::table('posts')->where('user_id', $user_id)->orderBy('created_at', 'desc')->first()->created_at;
		$now = date("Y-m-d H:i:s");
		$one_minute_later = (date("Y-m-d H:i:s",strtotime($latest_post_time . "+1 minute")));
		if ($now < $one_minute_later) {
			return redirect('/replyForm/' . $reply_id)->with('flash_message', '少し時間を空けて再度送信し直しててください');
		}

		$validate_num = config('const.numberOfCharacter');
		$validated_data = $request->validate([
		'text' => 'required|max:' . $validate_num,
		]);
		$destination_record = DB::table('posts')->where('reply_id', $reply_id)->exists();

		if ($destination_record == false) { 
			return redirect()->route('Replyform', ['reply_id' => $reply_id])->with('flash_message', '無効なURLです。');
		}

		$received_message_data = DB::table('posts')->where('reply_id', $reply_id)->first();

		$post_id = $received_message_data->post_id;

		$destination_reply_flg = $received_message_data->reply_flg;

		if ($destination_reply_flg == 1) {
			return redirect()->route('Replyform', ['reply_id' => $reply_id])->with('flash_message', '一度送信したメッセージに再び返信することはできません。');
		}

		$text = Input::get('text');
		$ng_words_list = DB::table('ngwords')->get();
		$destination_user_id = $received_message_data->user_id;
		$destination_id = User::find($destination_user_id)->twitter_id;
		$user_id = Auth::user()->id;
		foreach ($ng_words_list as $ng_word) {
		 	$ng_word = $ng_word->ng_word;
			if(stripos($text, $ng_word) !== false) {
				DB::table('ng_messages')->insert([
				    'message' => $text,
					'destination_twitter_id' => $destination_id,
					'user_id' => $user_id,
					'reply_id' => $reply_id
				]);
				return redirect()->route('Replyform', ['reply_id' => $reply_id])->with('flash_message', 'あなたが送信しようとしたメッセージには適切でない単語が含まれるため、送信には管理側のチェックが必要になります。送信されるまで時間を要することがありますので、先ほどとは異なるメッセージを再送信することをおすすめします。');
			}
		}

		$token = DB::table('tokens')->get()->first();
		$twitter_access_token = $token->twitter_access_token;
		$twitter_access_token_secret = $token->twitter_access_token_secret;

		$connection = new TwitterOAuth(
			config('twitter.consumer_key'),
			config('twitter.consumer_secret'),
			$twitter_access_token,
			$twitter_access_token_secret
		);

		$uniq_id = uniqid();

		$message = $connection->post('direct_messages/events/new', [
			'event' => [
				'type' => 'message_create',
				'message_create' => [
					'target' => [
						'recipient_id' => $destination_id  
					],
					'message_data' => [
						'text' => $text . ' (返信用URL) : ' . route('Replyform', ['reply_id' => $uniq_id])
					]  
				]
			]
		], true);

		if (isset($message->errors)) {
			Log::info($message->errors[0]->message);
			return redirect('/form')->with('flash_message', '送信に失敗しました。');
		};

		$reciever_id  = DB::table('users')->where('twitter_id',$destination_id)->first()->id;

		$received_message_id = $received_message_data->id;

		$post = Post::create([
			'text' => $text,
			'user_id' => $user_id,
			'reply_id' => $uniq_id,
			'destination_id' => $reciever_id,
			'from_post_id' => $received_message_id,
			'post_id' => $post_id,
		]);

		$post->save();

		DB::table('posts')->where('reply_id', $reply_id)->update(['reply_flg' => 1]);

		return redirect('/form')->with('flash_message', '送信しました。');
	}

}
