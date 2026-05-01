<?php

use App\Http\Controllers\Api\PortalApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/portal/config', [PortalApiController::class, 'config']);
Route::get('/portal/routers', [PortalApiController::class, 'routers']);
Route::post('/portal/router-heartbeat', [PortalApiController::class, 'heartbeat']);
Route::post('/portal/sessions', [PortalApiController::class, 'createSession']);
Route::post('/portal/ad-access', [PortalApiController::class, 'adAccess']);
Route::post('/portal/subscription-access', [PortalApiController::class, 'subscriptionAccess']);
