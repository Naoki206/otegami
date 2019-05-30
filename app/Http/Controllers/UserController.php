<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Auth;

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
