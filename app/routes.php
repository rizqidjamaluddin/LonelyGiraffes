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

Route::filter(
     'access-control',
         function () {
             header('Access-Control-Allow-Origin: *');
//             header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
         }
);

Route::post('oauth', function()
    {
        return \AuthorizationServer::performAccessTokenFlow();
    });

Route::when('*', 'access-control');

Route::api(
     ['version' => 'v1'],
         function () {

             // additional feeds use query strings; GET /posts?feed=buddies
             Route::get('posts', ['uses' => 'PostController@index']);
             Route::get('posts/{resource}', ['uses' => 'PostController@show']);
             Route::post('posts/{resource}/comments', ['uses' => 'PostController@addComment']);

             Route::get('events', ['uses' => 'EventController@index']);
             Route::get('events/{resource}', ['uses' => 'EventController@show']);
             Route::post('events', ['uses' => 'EventController@store']);
             Route::delete('events/{resource}', ['uses' => 'EventController@delete']);
             Route::put('events/{resource}', ['uses' => 'EventController@update']);

             Route::get('events/{resource}/invites', ['uses' => 'EventController@indexInvite']);
             Route::post('events/{resource}/invites/{invitation}/accept', ['uses' => 'EventController@acceptInvite']);
             Route::delete('events/{resource}/invites/{invitation}', ['uses' => 'EventController@deleteInvite']);
             Route::post('events/{resource}/invites', ['uses' => 'EventController@createInvite']);

             Route::get('events/{resource}/requests', ['uses' => 'EventController@indexRequest']);
             Route::post('events/{resource}/requests', ['uses' => 'EventController@createRequest']);
             Route::post('events/{resource}/requests/{request}/accept', ['uses' => 'EventController@acceptRequest']);
             Route::delete('events/{resource}/requests/{request}', ['uses' => 'EventController@deleteRequest']);

             Route::post('shouts', ['uses' => 'ShoutController@store']);
             Route::get('shouts/{resource}', ['uses' => 'ShoutController@show']);
             Route::get('shouts/user/{resource}', ['uses' => 'ShoutController@showAll']);
             Route::delete('shouts/{resource}', ['uses' => 'ShoutController@destroy']);

             Route::post('users', ['uses' => 'UserController@store']);
             Route::delete('users/{resource}', ['uses' => 'UserController@destroy']);
             Route::get('users/{resource}', ['uses' => 'UserController@show']);
             Route::put('users/{resource}', ['uses' => 'UserController@update']);
             Route::post('users/{resource}/promote', ['uses' => 'UserController@promote']);
             Route::get('users', ['uses' => 'UserController@index']);

             Route::get('users/{resource}/profile', ['uses' => 'UserProfileController@show']);
             Route::put('users/{resource}/profile', ['uses' => 'UserProfileController@update']);


             Route::get('users/{resource}/buddies', ['uses' => 'BuddyController@index']);
             Route::delete('users/{resource}/buddies/{buddy}', ['uses' => 'BuddyController@destroy']);

             Route::get('users/{resource}/buddy-requests', ['uses' => 'BuddyRequestController@requestIndex']);
             Route::post('users/{resource}/buddy-requests', ['uses' => 'BuddyRequestController@create']);
             Route::post('users/{resource}/buddy-requests/{request}/accept', ['uses' => 'BuddyRequestController@accept']);
             Route::delete('users/{resource}/buddy-requests/{request}', ['uses' => 'BuddyRequestController@destroy']);

             Route::get('users/{resource}/outgoing-buddy-requests', ['uses' => 'BuddyRequestController@outgoingIndex']);

             Route::get('conversations', ['uses' => 'ConversationController@index']);
             Route::post('conversations', ['uses' => 'ConversationController@create']);
             Route::delete('conversations/{conversation}', ['uses' => 'ConversationController@leave']);
             Route::post('conversations/{conversation}/messages', ['uses' => 'ConversationController@createMessage']);
             Route::delete(
                  'conversations/{conversation}/messages/{message}',
                  ['uses' => 'ConversationController@deleteMessage']
             );
             Route::post('conversations/{conversation}/invite', ['uses' => 'ConversationController@invite']);

             Route::get('notifications', ['uses' => 'NotificationController@index']);
             Route::delete('notifications/{notification}', ['uses' => 'NotificationController@destroy']);
             Route::post('notifications/clear', ['uses' => 'NotificationController@dismissAll']);

             Route::get('locations', ['uses' => 'LocationController@search']);

         }
);