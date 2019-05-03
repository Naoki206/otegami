<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;
use App\User;
use Auth;
use Illuminate\Support\Facades\Storage;
use DB;
use App\Quotation;
use Abraham\TwitterOAuth\TwitterOAuth;

class OAuthLoginController extends Controller
{
	public function socialLogin($social) {
		return Socialite::driver($social)->redirect();
	}

	public function handleProviderCallback($social) {
		try {
			$userSocial = Socialite::driver($social)->user();

			$user = DB::table('users')->where(['email' => $userSocial->getEmail()])->first();

			if(is_null($user)) {
				if (is_null($userSocial->getNickname())) {
					$userSocialNickName = $userSocial->getName();
				} else {
					$userSocialNickName = $userSocial->getNickname();
				}

				$userd = User::create([
					'name' => $userSocialNickName,
					'email' => $userSocial->getEmail(),
				]);
			 } else {
				 $userd = User::find($user->id);
			 }

			if (is_null($userd->twitter_id)) {
				$userd->twitter_id = $userSocial->getId();
				if (is_null($userSocial->getNickname())) {
					$userd->twitter_name = $userSocial->getName();
				} else {
					$userd->twitter_name = $userSocial->getNickname();
				}
			}

			$twitter_config = config('twitter');
			$userd->access_token = $twitter_config["access_token"];
			$userd->access_token_secret = $twitter_config["access_token_secret"];

			$userd->save();
			auth()->login($userd, true);
			return redirect()->to('/');

		} catch (Exception $e) {
			return redirect()->route('/')->withErrors('ユーザー情報の取得に失敗しました。');
		}
	}

	public function getTimeline(Request $request) {
		$user = User::find(Auth::user()->user_id);
		$twitter = new TwitterOAuth(
			config('twitter.consumer_key'),
			config('twitter.consumer_secret')
		);
		 $timeline = $twitter->get('statuses/user_timeline', array(
		 	'user_id' => Auth::User()->twitter_id,
		 ));
		 dd($timeline);
	}

	public function UserInfo(Request $request) {
		$twitter_config = config('twitter');
		$twitter = new TwitterOAuth(
			config('twitter.consumer_key'),
			config('twitter.consumer_secret'),
			$twitter_config["access_token"],
			$twitter_config["access_token_secret"]
		);
		$twitter_user_info = $twitter->get('account/verify_credentials');
		dd($twitter_user_info);
	}

}
