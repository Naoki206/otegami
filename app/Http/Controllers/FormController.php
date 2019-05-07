<?php

namespace App\Http\Controllers;

use Request;
use Abraham\TwitterOAuth\TwitterOAuth;
use DB;
use App\User;
use App\Post;
use Auth;
use Socialite;
use Illuminate\Support\Facades\Log;

class FormController extends Controller
{
	function returnForm() {
		if (Auth::check()) {
			return  view('form');
		}
		return view('welcome');
	}

	function messageSend() {
		$text = Request::input('text');
		$ng_words_list = DB::table('ngwords')->get();
		foreach ($ng_words_list as $ng_word) {
		 	$ng_word = $ng_word->ng_word;
			if(stripos($text, $ng_word) !== false) {
				return redirect('/form')->with('flash_message', '適切でない単語が含まれるため、送信できません。やり直してください。');
			}
		}
		$user_id = Auth::user()->id;
		$uniq_id = uniqid();

		$randomUserId = DB::table('users')->inRandomOrder()->where('id', '<>', $user_id)->first()->twitter_id;

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
						'text' => $text . ' (返信用URL) :' .  "http://otegami-kamatsuka.com/replyForm?reply_id=" . $uniq_id 
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

		return view('sent');
	}

	function returnReplyForm() {
		$reply_id = Request::input('reply_id');
		if (Auth::check()) {
			return  view('replyForm', compact('reply_id'));
		}
		return view('welcome');
	}

	function replySend() {
		$reply_id = Request::input('reply_id');
		$destination_record = DB::table('posts')->where('reply_id', $reply_id)->exists();
		if ($destination_record == false) { 
			return redirect('/replyForm/$reply_id=' . $reply_id)->with('flash_message', '無効なURLです。');
		}
		$destination_reply_flg = DB::table('posts')->where('reply_id', $reply_id)->first()->reply_flg;

		if ($destination_reply_flg == 1) {
			return redirect('/replyForm/$reply_id=' . $reply_id)->with('flash_message', '一度返信したメッセージに返信することはできません。');
		}

		$text = Request::input('text');
		$ng_words_list = DB::table('ngwords')->get();
		foreach ($ng_words_list as $ng_word) {
		 	$ng_word = $ng_word->ng_word;
			if(stripos($text, $ng_word) !== false) {
				return redirect('/replyForm/?reply_id=' . $reply_id)->with('flash_message', '適切でない単語が含まれるため、送信できません。やり直してください。');
			}
		}
		$destination_user_id = DB::table('posts')->where('reply_id', $reply_id)->first()->user_id;
		$destination_id = User::find($destination_user_id)->twitter_id;
		$user_id = Auth::user()->id;
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
						'recipient_id' => $destination_id  
					],
					'message_data' => [
						'text' => $text . ' (返信用URL) :' .  "http://otegami-kamatsuka.com/replyForm?reply_id=" . $uniq_id
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

		return view('sent');
	}

}
