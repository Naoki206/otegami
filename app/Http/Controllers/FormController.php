<?php

namespace App\Http\Controllers;

use Request;
use Abraham\TwitterOAuth\TwitterOAuth;
use DB;
use App\User;
use App\Post;
use Auth;
use Socialite;

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
		$user_id = Auth::user()->id;
		$uniq_id = uniqid();

		$post = Post::create([
			'text' => $text,
			'user_id' => $user_id,
			'reply_id' => $uniq_id 
		]);

		$post->save();

		$connection = new TwitterOAuth(
			config('twitter.consumer_key'),
			config('twitter.consumer_secret'),
			config('twitter.access_token'),
			config('twitter.access_token_secret')
		);

		$randomUserId = DB::table('users')->inRandomOrder()->where('id', '<>', $user_id)->first()->twitter_id;

		$connection->post('direct_messages/events/new', [
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
		$destination = DB::table('posts')->where('reply_id', $reply_id)->get();
		foreach($destination as $d) {
			$destination_user_id = $d->user_id;
			$destination_reply_flg = $d->reply_flg;
		}
		$destination_id = User::find($destination_user_id)->twitter_id;
		$text = Request::input('text');
		$user_id = Auth::user()->id;
		$uniq_id = uniqid();

		$post = Post::create([
			'text' => $text,
			'user_id' => $user_id,
			'reply_id' => $uniq_id 
		]);

		$post->save();

		$connection = new TwitterOAuth(
			config('twitter.consumer_key'),
			config('twitter.consumer_secret'),
			config('twitter.access_token'),
			config('twitter.access_token_secret')
		);

		if ($destination_reply_flg == 1) {
			echo "一度返信したメッセージに再度返信することはできません。";
			exit;
		}

		$connection->post('direct_messages/events/new', [
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

		DB::table('posts')
			->where('reply_id', $reply_id) 
			->update([
				'reply_flg' => 1
			]);

		return view('sent');
	}

	function join() {
		$user = User::find(1);
		$posts = $user->posts;

		$posts = Post::all();

		foreach ($posts as $post) {
			echo $post . "<br/>";
		}

		foreach ($posts as $post) {
			echo $post->user->name . "<br/>";
		}

	}
}
