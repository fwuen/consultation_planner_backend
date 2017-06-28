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

/*
|--------------------------------------------------------------------------
| Default Routes
|--------------------------------------------------------------------------
| GET         /models                index     models.index
| GET         /models/create         create    models.create
| POST        /models                store     models.store
| GET         /models/{model}        show      models.show
| GET         /models/{model}/edit   edit      models.edit
| PUT/PATCH   /models/{model}        update    models.update
| DELETE      /models/{model}        destroy   models.destroy
|
*/

Route::get('/', function () {
    return view('welcome');
})->middleware('auth.token');

Route::post('/login', "UserController@login");

Route::get('docent/{id}/meeting/coalition', 'DocentMeetingController@indexWithStudents')->middleware('auth.token');
Route::put('docent/{id}/meeting/{idOfFirstMeeting}/cancelseries', 'DocentMeetingController@cancelSeries')->middleware('auth.token');
Route::get('docent/search/{term}', 'DocentController@search')->middleware('auth.token');

Route::resource('docent', 'DocentController')->middleware('auth.token');
Route::resource('docent.meeting', 'DocentMeetingController')->middleware('auth.token');
Route::resource('docent.notification', 'DocentNotificationController')->middleware('auth.token');





Route::resource('student', 'StudentController')->middleware('auth.token');
Route::resource('student.participation', 'StudentParticipationController')->middleware('auth.token');
Route::resource('student.notification', 'StudentNotificationController')->middleware('auth.token');
Route::resource('student.meeting', 'StudentMeetingController')->middleware('auth.token');