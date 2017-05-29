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

Route::get('docent/search/{term}', 'DocentController@search');

Route::resource('docent', 'DocentController');

Route::resource('meeting', 'MeetingController');

Route::resource('meetingseries', 'MeetingSeriesController');

Route::resource('student', 'StudentController');

Route::resource('studentnotification', 'StudentNotificationController');

Route::resource('docentnotification', 'DocentNotificationController');

Route::get('student/{id}/notification', 'StudentController@getStudentNotifications');

Route::get('docent/{id}/notification', 'DocentController@getDocentNotifications');

Route::post('meeting/{id}/participation', 'MeetingController@create');

Route::delete('meeting/{id}/participation', 'MeetingController@delete');