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
		if (Auth::check() && Auth::user()->admin_flg) {
			return view('admin.index');
		}

		return redirect('/')->with('flash_message', '不正なアクセス');
	}

	public function show_ng_list(Request $request) {
		if (Auth::check() && Auth::user()->admin_flg) {
			$ng_words = DB::table('ngwords')->paginate(10);  
			return view('admin.ng_words', compact('ng_words'));
		}

		return redirect('/')->with('flash_message', '不正なアクセス');
	}

	public function delete_ng_word($id) {
		if (Auth::check() && Auth::user()->admin_flg) {
			$ng_words = DB::table('ngwords')->where('id', $id)->delete();  
			return redirect()->route('ng_words');
		}

		return redirect('/')->with('flash_message', '不正なアクセス');

	}

	public function show_add_form() {
		if (Auth::check() && Auth::user()->admin_flg) {
			return view('admin.add_ng_word_form');
		}

		return redirect('/')->with('flash_message', '不正なアクセス');
	}

	public function add_ng_word() {
		if (Auth::check() && Auth::user()->admin_flg) {
			$ng_word = Input::get('word');
			DB::table('ngwords')->insert([
				'ng_word' => $ng_word
			]);

			return redirect()->route('ng_words');
		}

		return redirect('/')->with('flash_message', '不正なアクセス');
	}

	public function ng_messages() {
		if (Auth::check() && Auth::user()->admin_flg) {
			$ng_messages = DB::table('ng_messages')->paginate(10);
			return view('admin.ng_messages', compact('ng_messages'));
		}

		return redirect('/')->with('flash_message', '不正なアクセス');
	}

	public function send_ng_messages($id) {
		if (Auth::check() && Auth::user()->admin_flg) {
			$message_data = DB::table('ng_messages')->where('id', $id)->first();

			if ($message_data->reply_id) {
				$reply_id = $message_data->reply_id;
				$destination_data = DB::table('posts')->where('reply_id', $reply_id)->first();
				$destination_reply_flg = $destination_data->reply_flg;
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

			$uniq_id1 = uniqid();

			$message = $connection->post('direct_messages/events/new', [
				'event' => [
					'type' => 'message_create',
					'message_create' => [
						'target' => [
							'recipient_id' => $destination_id  
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

			 DB::table('ng_messages')->where('id', $id)->delete();
			 $reciever_id = User::where('twitter_id', $destination_id)->first()->id;

			if ($message_data->reply_id) {
				$destination_message_id = $destination_data->id;
				$post_id = $destination_data->post_id;

				$post = Post::create([
					'text' => $text,
					'user_id' => $sender_id,
					'reply_id' => $uniq_id1, 
					'destination_id' => $reciever_id,
					'from_post_id' => $destination_message_id,
					'post_id' => $post_id,
				]);

				DB::table('posts')->where('reply_id', $reply_id)->update(['reply_flg' => 1]);

				return redirect()->route('ng_messages')->with('flash_message', '送信しました。');
			}

			$uniq_id2 = uniqid();

			$post = Post::create([
				'text' => $text,
				'user_id' => $sender_id,
				'reply_id' => $uniq_id1, 
				'destination_id' => $reciever_id,
				'post_id' => $uniq_id,
			]);

			return redirect()->route('ng_messages')->with('flash_message', '送信しました。');
		}

		return redirect('/')->with('flash_message', '不正なアクセス');
	}
}
