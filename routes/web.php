<?php

//Artisan::call('cache:clear');

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

Route::get('/', function () {
    return view('welcome');
});



//Route::get('/hb/save', function(){

 file_put_contents('./body0.txt', print_r( $_REQUEST,1 ));

Route::post('feedback/{company_id}', 'TelegramController@feedback')->name('feedback');

Route::post('updatedata/{post_hash}', 'TelegramController@updateData')->name('hb_update');
//
//Route::get('get_token', 'TelegramController@getToken' )->name('hb_start');
//
Route::get('get_channel/{channel_id}', 'TelegramController@getChannel')->name('get_channel');
Route::get('get_channels/{company_id}', 'TelegramController@getChannels')->name('get_channels');
Route::get('get_post/{hash}', 'TelegramController@getPost')->name('get_post');
Route::get('post/{hash}/show/increase', 'TelegramController@increasePostShows')->name('increase_post_shows');

Route::get('get_user_auth_hash/{ls_key}', 'TelegramController@getUserAuthHash')->name('get_user_auth_hash');

Route::get('get_posts_unsent/{channel_id}', 'TelegramController@getChannelPostsUnSent')->name('hb_posts_unsent');

Route::get('get_posts_sent/{channel_id}', 'TelegramController@getChannelPostsSent')->name('hb_posts_sent');

Route::get('get_posts/{channel_id}', 'TelegramController@getChannelPosts')->name('hb_posts');
Route::get('get_token', function () {

    $cs = csrf_field();


    return response($cs, 200);
//        ->
//        ->header('Content-Type', 'text/plain');
});



Route::group(['prefix' => 'hb'], function () {

    Route::get('start', 'TelegramController@start')->name('hb_start');

});
Route::post('identuser/{auth_key}', 'TelegramController@identUser')->name('ident_user');

Route::post('channel/{channelId}', 'TelegramController@updateChannel')->name('update_channel');
Route::post('channel', 'TelegramController@createChannel')->name('create_channel');
Route::post('savedata/{channelId}', 'TelegramController@saveData')->name('save_data');



Route::get('/telegram/'.config('telegram.bot_token').'/removeWH', 'TelegramController@removeWH')->name('rm_wh');
Route::get('/telegram/'.config('telegram.bot_token').'/setWH', 'TelegramController@setWH')->name('set_wh');
Route::post('/telegram/'.config('telegram.bot_token').'/webhook', 'TelegramController@webhook')->name('webhook');
Route::get('/get_auth_data/{hash}', ['middleware'=>'cors','uses'=>'TelegramController@getAuthData'])->name('get_auth_data');
