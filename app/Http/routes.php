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




Route::group(array('prefix' => 'admin',
    //'middlewareGroups' => ['web','auth'],
    //'middleware' => ['role:admin,access_backend']
    ),

function()
{
    // Chat
    Route::get('chat','LineChatController@chatAdmin');
    
    Route::get('/', array('as'=>'admin.home', function(){
        return view('admin.dashboard');
    } ));
    Route::get('/logout', array('as'=>'admin.logout','uses' => 'Admin\ClientsController@logout' ));
    
    // Clients
    Route::get('/clients', array('as'=>'admin.clients','uses' => 'Admin\ClientsController@index' ));
    Route::get('/clients/{user_id}/apps', array('as'=>'admin.clients.apps','uses' => 'Admin\AppsController@index' ));
    Route::get('/clients/{user_id}/apps/create', array('as'=>'admin.clients.apps.create','uses' => 'Admin\AppsController@create' ));
    Route::post('/clients/{user_id}/apps/create', array('as'=>'admin.clients.apps.store','uses' => 'Admin\AppsController@store' ));
    Route::get('/clients/{user_id}/apps/{app_id}/edit', array('as'=>'admin.clients.apps.edit','uses' => 'Admin\AppsController@edit' ));
    Route::post('/clients/{user_id}/apps/{app_id}/edit', array('as'=>'admin.clients.apps.update','uses' => 'Admin\AppsController@update' ));
    Route::get('/clients/{user_id}/apps/{app_id}/delete', array('as'=>'admin.clients.apps.delete','uses' => 'Admin\AppsController@delete' ));


});
