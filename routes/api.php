<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrcodeAPIController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/qr-code/{instance}', [QrcodeAPIController::class, 'index']);
Route::get('/qr-code/reload/{instance}', [QrcodeAPIController::class, 'reload']);