<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'auth:api'], function(){

    Route::post('createEvent' , 'UserController@createEvent')->name('createEvent');

    Route::get('eventsToGo' , 'UserController@eventsToGo')->name('eventsToGo');

    Route::get('invite' , 'UserController@invite')->name('invite');

    Route::post('respondEvent' , 'UserController@respondEvent')->name('respondEvent');

    Route::get('myEvents', 'UserController@myEvents')->name('myEvents');
    
    Route::get('getEventStatus', 'UserController@getEventStatus')->name('getEventStatus');

});

Route::post('login' , 'UserController@login')->name('login');

Route::post('logout' , 'UserController@logout')->name('logout');

Route::post('register' , 'UserController@register')->name('register');

