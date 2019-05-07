<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
});*/

//Auth::routes();

Route::get('/', 'HomeController@index')->name('home');
Route::get('/logout', 'Auth\OAuthLoginController@logout')->name('logout');

Route::get('/login/{social}', 'Auth\OAuthLoginController@socialLogin')->where('social', 'twitter');
Route::get('/login/{social}/callback', 'Auth\OAuthLoginController@handleProviderCallback')->where('social', 'twitter');

Route::get('/index1', 'Auth\OAuthLoginController@GetTimeLine');
Route::get('/index2', 'Auth\OAuthLoginController@UserInfo');

Route::get('/form', 'FormController@returnForm')->name('form');
Route::post('/form', 'FormController@messageSend')->name('formConfilm');

Route::get('/replyForm/{reply_id?}', 'FormController@returnReplyForm')->name('Replyform');
Route::post('/replyForm', 'FormController@replySend')->name('ReplyformConfilm');


