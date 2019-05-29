<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\User;
use App\Post;
use Auth; 
use Illuminate\Support\Facades\Input;
use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
	public function index () {
		if (!Auth::user()->admin_flg) {
			return redirect('/')->with('flash_message', '不正なアクセス');
		}
		return view('admin.index');
	}

	public function show_ng_list(Request $request) {
		if (!Auth::user()->admin_flg) {
			return redirect('/')->with('flash_message', '不正なアクセス');
		}
		$ng_words = DB::table('ngwords')->paginate(10);  
		return view('admin.ng_words', compact('ng_words'));
	}

	public function delete_ng_word($id) {
		if (!Auth::user()->admin_flg) {
			return redirect('/')->with('flash_message', '不正なアクセス');
		}
		$ng_words = DB::table('ngwords')->where('id', $id)->delete();  

		return redirect()->route('ng_words');
	}

	public function show_add_form() {
		if (!Auth::user()->admin_flg) {
			return redirect('/')->with('flash_message', '不正なアクセス');
		}

		return view('admin.add_ng_word_form');
	}

	public function add_ng_word() {
		if (!Auth::user()->admin_flg) {
			return redirect('/')->with('flash_message', '不正なアクセス');
		}

		$ng_word = Input::get('word');
		DB::table('ngwords')->insert([
			'ng_word' => $ng_word
		]);

		return redirect()->route('ng_words');
	}

	public function ng_messages() {
		if (!Auth::user()->admin_flg) {
			return redirect('/')->with('flash_message', '不正なアクセス');
		}
		$ng_messages = DB::table('ng_messages')->paginate(10);
		return view('admin.ng_messages', compact('ng_messages'));
	}

	public function send_ng_messages($id) {
		if (!Auth::user()->admin_flg) {
			return redirect('/')->with('flash_message', '不正なアクセス');
		}

		$message_data = DB::table('ng_messages')->where('id', $id)->first();

		if ($message_data->reply_id) {
			$reply_id = $message_data->reply_id;
			$destination_reply_flg = DB::table('posts')->where('reply_id', $reply_id)->first()->reply_flg;
			if ($destination_reply_flg == 1) {
		 		DB::table('ng_messages')->where('id', $id)->delete();
				return redirect()->route('ng_messages')->with('flash_message', 'すでにこのメッセージとは異なる返信をユーザーはしています。このメッセージはテーブルから削除しておきます。');
			}
		}

		$sender_id = $message_data->user_id;
		$text = $message_data->message;
		$destination_id  = $message_data->destination_twitter_id;

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

		 DB::table('ng_messages')->where('id', $id)->delete();

		$post = Post::create([
			'text' => $text,
			'user_id' => $sender_id,
			'reply_id' => $uniq_id 
		]);

		if ($message_data->reply_id) {
			DB::table('posts')->where('reply_id', $reply_id)->update(['reply_flg' => 1]);
		}

		return redirect()->route('ng_messages')->with('flash_message', '送信しました。');

	}
}
