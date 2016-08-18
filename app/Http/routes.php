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


Route::any('/','LineChatController@index');
Route::get('/login','LineChatController@login');
Route::get('/verifined','LineChatController@verifined');
Route::get('/chat/{mid}','LineChatController@chat');

Route::get('/chat/demo','LineChatController@chatdemo');