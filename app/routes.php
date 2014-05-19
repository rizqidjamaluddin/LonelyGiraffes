<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get(
     '/',
         function () {
             return View::make('hello');
         }
);

Route::api(
     ['version' => 'v1'],
         function () {
             Route::get('posts', ['uses' => 'PostController@index']);
             Route::post('posts/{resource}/comments', ['uses' => 'PostController@addComment']);

             Route::get('events', ['uses' => 'EventController@index']);
             Route::get('events/{resource}', ['uses' => 'EventController@index']);
             Route::delete('events/{resource}', ['uses' => 'EventController@delete']);

             Route::get('shouts', ['uses' => 'ShoutController@index']);
             Route::post('shouts', ['uses' => 'ShoutController@store']);
         }
);