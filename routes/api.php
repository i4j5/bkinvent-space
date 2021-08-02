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

// Закрытие сделок
Route::post('webhook/amocrm-closing-lead', 'Webhooks\AmoCRMClosingLeadController');

// Создание папок
Route::post('google-drive/create-project-folder', 'API\GoogleDriveFoldersController@CreateProjectFolder');
// Route::get('google-drive/create-client-folder', 'API\GoogleDriveFoldersController@CreateСlientFolder');
Route::post('google-drive/rename-project-folder', 'API\GoogleDriveFoldersController@RenameProjectFolder');
Route::post('google-drive/rename-client-folder', 'API\GoogleDriveFoldersController@RenameСlientFolder');

// Аналитика
Route::post('amocrm-analytics/new-leads', 'API\AmoCRMAnalyticsController@NewLeads');
Route::post('amocrm-analytics/active-leads', 'API\AmoCRMAnalyticsController@ActiveLeads');
Route::post('amocrm-analytics/production-leads', 'API\AmoCRMAnalyticsController@ProductionLeads');
Route::post('amocrm-analytics/closed-leads', 'API\AmoCRMAnalyticsController@ClosedLeads');
Route::get('amocrm-analytics/leads', 'API\AmoCRMAnalyticsController@Leads');

// Сайты
Route::prefix('site')->group(function () {

    // Создание заявки
    Route::post('create-lead', 'API\SiteController@createLead'); 

    // Сбор данных по визитам сайта
    Route::prefix('visitor')->group(function () {
        Route::post('create', 'API\VisitorController@create');
        Route::post('update', 'API\VisitorController@update');
    });
});

Route::match(['GET', 'POST'], 'webhook/call-tracker', 'Webhooks\CallTrackerController');

Route::prefix('avito')->group(function () {

    // Создание заявки
    Route::post('ad-stats', 'API\AvitoController@AdStats'); 
    Route::get('get-ads', 'API\AvitoController@GetAds'); 
    Route::post('all', 'API\AvitoController@All'); 
});

Route::prefix('asana')->group(function () {
    Route::post('create-deal-project', 'API\AsanaController@createDealProject');
    Route::post('update-deal-project', 'API\AsanaController@updateDealProject');
    Route::any('webhook/{deal_id}/{project_id}', 'API\AsanaController@webhook');
    Route::post('delete-webhook', 'API\AsanaController@deleteWebhook');
});

// use App\Actions\AmoCRM\RequestActions;
// Route::get('amo-dir',  function () {
//     $deal_ids = [

//     ];

//     $amoCRM = new RequestActions;

//     $tasks = [];

//     foreach ($deal_ids as $id) {

//         $data = $amoCRM->execute('/api/v4/tasks','get', [
//             'filter' => [
//                 'entity_type' => 'leads',
//                 'entity_id' => $id,
//                 'is_completed' => 0
//             ]
//         ]);

//         if (isset($data->_embedded)) {
//             $data->_embedded->tasks;
//             $tasks = array_merge($tasks, $data->_embedded->tasks);
//         }

//         $lead = $amoCRM->execute('/api/v2/leads', 'get', ['id' => $id])->_embedded->items[0];

//         if (isset($lead->main_contact)) {
//             $contac_id = $lead->main_contact->id;


//             $data = $amoCRM->execute('/api/v4/tasks','get', [
//                 'filter' => [
//                     'entity_type' => 'contacts',
//                     'entity_id' => $contac_id,
//                     'is_completed' => 0
//                 ]
//             ]);
    
//             if (isset($data->_embedded)) {
//                 $data->_embedded->tasks;
//                 $tasks = array_merge($tasks, $data->_embedded->tasks);
//             }
//         }
//     }

//     foreach ($tasks as $task) {
//         $amoCRM->execute("/api/v4/tasks/$task->id", 'patch', [
//             'responsible_user_id' => 6345826
//         ]);
//     }
// });