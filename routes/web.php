<?php

use Illuminate\Support\Facades\Route;

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
    return redirect('/login');
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::group(['middleware' => ['auth:sanctum', 'verified']], function () {

    Route::get('login/google/callback', 'User\GoogleController@callback')->name('login.google.callback');
    Route::get('calendar-list', 'User\GoogleController@calendarList')->name('google.calendar.list');

    Route::get('login/asana/callback', 'User\AsanaController@callback')->name('login.asana.callback');
    Route::get('login/amocrm/callback', 'User\AmoCRMController@callback')->name('login.amocrm.callback');
    Route::get('login/yandex/callback', 'User\YandexController@callback')->name('login.yandex.callback');

    Route::get('/test-ct', function () {
        return view('calltracker');
    });

});