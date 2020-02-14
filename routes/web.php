<?php

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

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::post('/slack/slash/command', 'SlashCommandController@execute')
    ->name('slashCommand.execute')
    ->middleware('guest');
Route::get('/slack/{nonce}', 'SlackBindUserController@bindUser')
    ->name('slack-nonce')
    ->middleware('auth');
