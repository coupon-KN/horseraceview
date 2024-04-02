<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::controller(\App\Http\Controllers\api\HorseRaceApi::class)->group(function() {
    Route::post('/horserace/schdule', 'getSchdule')->name("api.horse.schdule");
    Route::post('/horserace/racedata', 'getRaceData')->name("api.horse.racedata");
});


/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/
