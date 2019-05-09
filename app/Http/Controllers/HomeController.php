<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    /*public function __construct()
    {
        $this->middleware('auth');
    }*/

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		if (Auth::check()) {
			$user_id = Auth::user()->id;
			$posts = DB::table('posts')->where('user_id', $user_id)->paginate(10);
			return view('form', compact('posts')); 
		}
        return view('welcome');
    }
}
