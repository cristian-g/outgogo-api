<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// ------------------------------
// Vehicles
// ------------------------------
Route::get('/vehicles', 'VehicleController@index')->middleware('jwt');
Route::get('/vehicle/{id}', 'VehicleController@show')->middleware('jwt');
Route::put('/vehicle/{id}', 'VehicleController@update')->middleware('jwt');
Route::post('/vehicle', 'VehicleController@store')->middleware('jwt');
Route::get('/vehicle/{id}/user/{user_id}', 'VehicleController@show_balance')->middleware('jwt');

// ------------------------------
// Actions
// ------------------------------
Route::get('/vehicle/{vehicle_id}/actions', 'ActionController@index')->middleware('jwt');

// ------------------------------
// Outgoes
// ------------------------------
Route::post('/vehicle/{vehicle_id}/outgo', 'OutgoController@store')->middleware('jwt');
Route::get('/outgo/{id}', 'OutgoController@show')->middleware('jwt');
Route::put('/outgo/{id}', 'OutgoController@update')->middleware('jwt');

// ------------------------------
// Consumption
// ------------------------------
Route::post('/vehicle/{vehicle_id}/consumption', 'OutgoController@storeConsumption')->middleware('jwt');
Route::put('/consumption/{id}', 'OutgoController@updateConsumption')->middleware('jwt');

// ------------------------------
// Payments
// ------------------------------
Route::post('/vehicle/{vehicle_id}/payment', 'PaymentController@store')->middleware('jwt');
Route::get('/payment/{id}', 'PaymentController@show')->middleware('jwt');
Route::put('/payment/{id}', 'PaymentController@update')->middleware('jwt');
