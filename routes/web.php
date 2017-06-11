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

Route::resource('docent', 'DocentController');
Route::resource('docent.meeting', 'DocentMeetingController');
Route::resource('docent.notification', 'DocentNotificationController');
Route::get('docent/search/{term}', 'DocentController@search');
/*
Route::get('docent/{id}/meeting', 'DocentController@getMeetingsByDocent');
Route::post('docent/{id}/meeting', 'DocentController@storeMeeting');
Route::put('docent/{id}/meeting', 'DocentController@updateMeeting');
Route::get('docent/{id}/notification', 'DocentController@getNotificationsByDocent');*/

Route::resource('student', 'StudentController');
Route::resource('student.participation', 'StudentParticipationController');
Route::resource('student.notification', 'StudentNotificationController');
/*
Route::post('student/{id}/participation', 'StudentController@createParticipation');
Route::put('student/{id}/participation', 'StudentController@updateParticipation');
Route::delete('student/{id}/participation', 'StudentController@deleteParticipation');
Route::get('student/{id}/participation', 'StudentController@getParticipationsByStudent');
Route::get('student/{id}/notification', 'StudentController@getNotificationsByStudent');*/