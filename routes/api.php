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


Route::post('signup', 'HomeController@signUp');
Route::post('login', 'HomeController@login');
Route::middleware('wsr_auth')->group(function () {
    Route::post('logout', 'HomeController@logout');
    Route::patch('photo/{id}', 'PhotoController@editImage');
    Route::post('photo', 'PhotoController@createImage');
    Route::get('photo/{id}', 'PhotoController@getPhoto');
    Route::delete('photo/{id}', 'PhotoController@deletePhoto');
    Route::get('photo', 'PhotoController@allPhotos');
    Route::post('user/{id}/share', 'PhotoController@sharePhotos');
    Route::get('user', 'HomeController@searchUser');
});
