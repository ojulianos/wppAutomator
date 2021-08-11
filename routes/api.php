<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

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

Route::get('version', function () {
    return response()->json(['version' => config('app.version')]);
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    Log::debug('User:' . serialize($request->user()));
    return $request->user();
});


Route::namespace('App\\Http\\Controllers\\API\V1')->group(function () {
    Route::get('profile', 'ProfileController@profile');
    Route::put('profile', 'ProfileController@updateProfile');
    Route::post('change-password', 'ProfileController@changePassword');

    Route::apiResources([
        'user' => 'UserController',
        'phone' => 'PhoneController',
    ]);

    Route::group(['prefix' => 'phoneMessage'], function() {
        Route::get('/{phone}', 'MessageController@index');
        Route::post('/{phone}', 'MessageController@store');
        Route::get('/{id}', 'MessageController@show');
        Route::put('/{id}', 'MessageController@update');
        Route::delete('/{id}', 'MessageController@destroy');
    });
});

/*
 * php artisan jwt:secret
 * POST WPP_MESSAGE
 * POST LOGIN
 * GET PHONES
 * POST PHONES
 * PUT PHONES
 * DELETE PHONES
 * GET PHONE_MESSAGES
 * POST PHONE_MESSAGES
 * PUT PHONE_MESSAGES
 * DELETE PHONE_MESSAGES
 * */

Route::post('wpp_busca_questao', 'App\\Http\\Controllers\\API\V1\\WhatsappController@index');
Route::post('wpp/{session}/{platform}', 'App\\Http\\Controllers\\API\V1\\WhatsappController@send');
