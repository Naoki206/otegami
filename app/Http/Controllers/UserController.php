<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Abraham\TwitterOAuth\TwitterOAuth;
use DB;
use App\User;
use App\Post;
use Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
	function confilmUnsubscribe() {
		if (Auth::check()) {
			return view('user.confilm_unsubscribe');
		}

		return redirect('/');
	}

	function unsubscribe(Request $request) {
		if (Auth::check()) {
			$user_id = Auth::user()->id;
			$user = User::find($user_id);
			$user->delete();
			return redirect('/');
		}

		return redirect('/');
	}
}
