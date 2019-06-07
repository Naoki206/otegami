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

Route::get('/receivedMessages', 'UserController@receivedMessageList')->name('receivedMessageList');

Route::get('/form', 'FormController@returnForm')->name('form');
Route::post('/form', 'FormController@messageSend')->name('formConfilm');

Route::get('/replyForm/{reply_id}', 'FormController@returnReplyForm')->name('Replyform');
Route::post('/replyForm', 'FormController@replySend')->name('ReplyformConfilm');

Route::get('/confilm_unsubscribe', 'UserController@confilmUnsubscribe')->name('confilm_unsubscribe');
Route::get('/unsubscribe', 'UserController@unsubscribe')->name('unsubscribe');

Route::get('/terms', 'HomeController@terms')->name('terms');

Route::get('/admin', 'AdminController@index')->name('admin_index');
Route::get('/ng_words', 'AdminController@show_ng_list')->name('ng_words');
Route::get('/ng_words{id}', 'AdminController@delete_ng_word')->name('delete_ng_word');
Route::get('/add_ng_word', 'AdminController@show_add_form')->name('add_ng_word_form');
Route::post('/add_ng', 'AdminController@add_ng_word')->name('add_ng_word');
Route::get('/ng_messages', 'AdminController@ng_messages')->name('ng_messages');
Route::get('/ng_messages/{id}', 'AdminController@send_ng_messages')->name('send_ng_messages');

