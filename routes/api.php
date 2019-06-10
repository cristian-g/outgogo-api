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
// Payments
// ------------------------------
Route::post('/vehicle/{vehicle_id}/payment', 'PaymentController@store')->middleware('jwt');
Route::get('/payment/{id}', 'PaymentController@show')->middleware('jwt');
Route::put('/payment/{id}', 'PaymentController@update')->middleware('jwt');

// ------------------------------
// From vehicle
// ------------------------------
Route::post('drive', 'OutgoController@storeFromVehicle');
Route::post('list', 'OutgoController@indexFromVehicle');

// ------------------------------
// Fake
// ------------------------------
Route::get('/fake0', 'VehicleController@fake0');
Route::get('/fake1', 'VehicleController@fake1');
Route::get('/fake2', 'VehicleController@fake2');
Route::get('/fake3', 'VehicleController@fake3');
Route::get('/signup1', 'VehicleController@signup1');
Route::get('/signup2', 'VehicleController@signup2');
Route::get('/signup3', 'VehicleController@signup3');

// Reset database
Route::get('reset-database', function (Request $request) {
    //return shell_exec('php artisan migrate:rollback') . shell_exec('php artisan migrate');
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    \Illuminate\Support\Facades\Artisan::call('migrate:rollback');
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    \Illuminate\Support\Facades\Artisan::call('migrate');
    return "Yey! Data has been reset. Go back to your tests, little grasshopper!";
});
