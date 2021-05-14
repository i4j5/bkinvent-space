<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// namespace App\Http\Controllers\API\AddGoogleCalendarEventController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('add-google-calendar-event', 'API\AddGoogleCalendarEventController');

Route::get('payment', 'API\Payment@index');
Route::post('payment', 'API\Payment@store');
Route::get('payment/{id}', 'API\Payment@show');
Route::delete('payment/{id}', 'API\Payment@destroy');


Route::post('add-google-calendar-event', 'API\AddGoogleCalendarEventController');


Route::post('webhook/amocrm-closing-lead', 'Webhooks\AmoCRMClosingLeadController');

Route::post('google-drive/create-project-folder', 'API\GoogleDriveFoldersController@CreateProjectFolder');
// Route::get('google-drive/create-client-folder', 'API\GoogleDriveFoldersController@CreateСlientFolder');
Route::post('google-drive/rename-project-folder', 'API\GoogleDriveFoldersController@RenameProjectFolder');
Route::post('google-drive/rename-client-folder', 'API\GoogleDriveFoldersController@RenameСlientFolder');