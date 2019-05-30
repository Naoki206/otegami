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
		if (Auth::check()) {
			return  view('form', compact('reply_id'));
		}
		return Socialite::driver($social)->redirect();
	}

	public function handleProviderCallback($social) {
		try {
			$userSocial = Socialite::driver($social)->user();
			$twitter_id = $userSocial->id;

			$user = DB::table('users')->where('twitter_id', $twitter_id)->where('deleted_at', NULL)->first();

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
			return redirect()->route('form');

		} catch (Exception $e) {
			return redirect()->route('/')->withErrors('ユーザー情報の取得に失敗しました。');
		}
	}

	public function logout() {
		Auth::logout();
		return redirect()->to('/');
	}

}
