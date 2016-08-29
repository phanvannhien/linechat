<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


Route::any('/','\App\Lib\Chat\MultiRoomServer@index');
Route::get('/login',array( 'as' => 'line.login', 'uses' => 'LineChatController@login'));
Route::get('/line/verifined/token/{mid}',array( 'as' => 'line.verifined.token', 'uses' => 'LineChatController@verifinedToken'));
Route::get('/verifined','LineChatController@verifined');
Route::get('/chat/{user_id}','LineChatController@chat');
Route::get('/chat/screen/{user_id}','LineChatController@chatScreen');

Route::get('admin/chat','LineChatController@chatAdmin');