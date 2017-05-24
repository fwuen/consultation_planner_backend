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

Route::get('docent/search/{term}', 'DocentController@search');

Route::resource('docent', 'DocentController');

Route::resource('meeting', 'MeetingController');

Route::resource('meetingSeries', 'MeetingSeriesController');

Route::resource('student', 'StudentController');
