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
		$validateNum = config('const.numberOfCharacter');
		$validatedData = $request->validate([
			'text' => 'required|max:' . $validateNum,
		]);
		$text = Input::get('text');
		$ng_words_list = DB::table('ngwords')->get();
		$user_id = Auth::user()->id;
		$randomUserId = DB::table('users')->inRandomOrder()->where('id', '<>', $user_id)->first()->twitter_id;
		foreach ($ng_words_list as $ng_word) {
		 	$ng_word = $ng_word->ng_word;
			if(stripos($text, $ng_word) !== false) {
				DB::table('ng_messages')->insert([
				    'message' => $text,
					'destination_twitter_id' => $randomUserId,
					'user_id' => $user_id
				]);
				return redirect('/form')->with('flash_message', 'あなたが送信しようとしたメッセージには適切でない単語が含まれるため、送信    には管理側のチェックが必要になります。送信されるまで時間を要することがありますので、先ほどとは異なるメッセージを再送信することをおすすめします。');
			}
		}

		$uniq_id = uniqid();

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
						'recipient_id' => $randomUserId  
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

		$post = Post::create([
			'text' => $text,
			'user_id' => $user_id,
			'reply_id' => $uniq_id 
		]);

		$post->save();

		return redirect('/form')->with('flash_message', '送信しました。');
	}

	function returnReplyForm($reply_id) {
		if (Auth::check()) {
			$user_id = Auth::user()->id;
			$posts = DB::table('posts')->where('user_id', $user_id)->paginate(10);
			$received_message = DB::table('posts')->where('reply_id', $reply_id)->get()[0]->text;
			return view('replyForm', compact('posts', 'reply_id', 'received_message'));
		}
		return view('welcome');
	}

	function replySend(Request $request) {
		$validateNum = config('const.numberOfCharacter');
		$validatedData = $request->validate([
		'text' => 'required|max:' . $validateNum,
		]);
		$reply_id = Input::get('reply_id');
		$destination_record = DB::table('posts')->where('reply_id', $reply_id)->exists();

		if ($destination_record == false) { 
			return redirect()->route('Replyform', ['reply_id' => $reply_id])->with('flash_message', '無効なURLです。');
		}

		$received_message_data = DB::table('posts')->where('reply_id', $reply_id)->first();
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

		$post = Post::create([
			'text' => $text,
			'user_id' => $user_id,
			'reply_id' => $uniq_id 
		]);

		$post->save();

		DB::table('posts')->where('reply_id', $reply_id)->update(['reply_flg' => 1]);

		return redirect('/form')->with('flash_message', '送信しました。');
	}

}
