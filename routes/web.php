<?php

Artisan::call('cache:clear');

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

Route::post('updatedata/{post_id}', 'TelegramController@updateData')->name('hb_update');
//
//Route::get('get_token', 'TelegramController@getToken' )->name('hb_start');
//
Route::get('get_post/{hash}', 'TelegramController@getPost')->name('get_post');
Route::get('post/{hash}/show/increase', 'TelegramController@increasePostShows')->name('increase_post_shows');

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

Route::post('savedata', 'TelegramController@saveData')->name('hb_save');
//Route::post(['prefix' => 'savedata'], function () {
//
//
//
//});

//Route::any('savedata', 'TelegramController@saveData')->name('hb_save');

//function () {
//    $body = json_encode(file_get_contents('php://input'));
//
//    file_put_contents('/body4.txt',$body);
////    Route::post('save', 'TelegramController@saveData')->name('hb_save');
//});
//Route::group(['prefix' => 'savedata'], function () {
//    $body = json_encode(file_get_contents('php://input'));
//
//    file_put_contents('./body2.txt',print_r($body,1));
//     Route::post('save', 'TelegramController@saveData')->name('hb_save');
//});