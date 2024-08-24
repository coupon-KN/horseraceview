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

    Route::post('/horserace/logincheck', 'loginCheck')->name("api.horse.logincheck");
    Route::post('/horserace/login', 'login')->name("api.horse.login");
    Route::middleware("check.login")->group(function() {
        Route::post('/horserace/kaisaibaba', 'getKaisaiBaba')->name("api.horse.kaisaibaba");
        Route::post('/horserace/racelist', 'getRaceList')->name("api.horse.racelist");
        Route::post('/horserace/scrapingracedata', 'scrapingRaceData')->name("api.horse.scrapingracedata");

        Route::post('/horserace/setting/schedule', 'getSettingScheduleData');
        Route::post('/horserace/setting/schedule/update', 'updateScheduleData');
    });
});

Route::controller(\App\Http\Controllers\api\RaceScoringApi::class)->group(function() {
    Route::post('/scoring/{race_id}', 'scoring')->name("api.race.scoring");

});


/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/
