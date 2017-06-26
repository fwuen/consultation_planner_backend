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
});

Route::get('docent/{id}/meeting/coalition', 'DocentMeetingController@indexWithStudents');
Route::put('docent/{id}/meeting/{idOfFirstMeeting}/cancelseries', 'DocentMeetingController@cancelSeries');
Route::get('docent/search/{term}', 'DocentController@search');
Route::resource('docent', 'DocentController');
Route::resource('docent.meeting', 'DocentMeetingController');
Route::resource('docent.notification', 'DocentNotificationController');

Route::resource('student', 'StudentController');
Route::resource('student.participation', 'StudentParticipationController');
Route::resource('student.notification', 'StudentNotificationController');
Route::resource('student.meeting', 'StudentMeetingController');