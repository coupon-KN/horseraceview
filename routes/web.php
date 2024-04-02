<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


// 設定 - スケジュールを作成する画面

Route::middleware("is.pc")->group(function() {
    // 設定 - レースデータ取得をキックする画面
    Route::controller(\App\Http\Controllers\setting\LoadRaceController::class)->group(function() {
        Route::get("/setting/raceload", "index")->name("setting.raceload");
        Route::post("/setting/raceload", "getRaceData")->name("setting.raceload.getRaceData");
        Route::post("/setting/racebulkload", "getBulkRaceData")->name("setting.raceload.getBulkRaceData");
    });
    // 設定 - 競走馬データ取得をキックする画面
    Route::controller(\App\Http\Controllers\setting\LoadHorseController::class)->group(function() {
        Route::get("/setting/horseload/{race_id}", "index")->name("setting.horseload");
        Route::post("/setting/horseload", "getHorseData")->name("setting.horseload.getHorseData");
    });

    // ビューワー
    Route::controller(\App\Http\Controllers\RaceViewController::class)->group(function() {
        Route::get("/raceview/{race_id}", "index")->name("raceview");
    });

    Route::view("/", 'index');
});


Route::get('{any}', function () {
    return view('mobile/index');
})->where('any','.*')->name("mobile");
