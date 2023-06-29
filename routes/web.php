<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'chat'], function () {
    Route::post('/post', ['as' => 'client.chat.message.post', 'uses' => 'App\Http\Controllers\Chat\ClientChatController@postMessage']);
    Route::post('/remove/{id}', ['as' => 'client.chat.message.remove', 'uses' => 'App\Http\Controllers\Chat\ClientChatController@removeMessage']);
    Route::get('/load', ['as' => 'client.chat.load', 'uses' => 'App\Http\Controllers\Chat\ClientChatController@loadMessages']);
});

