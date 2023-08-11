<?php

use App\Http\Controllers\WebhookMSController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/webhook/customerorder/',[WebhookMSController::class, 'customerorder']);
Route::post('/webhook/demand/',[WebhookMSController::class, 'customerorder']);
Route::post('/webhook/salesreturn/',[WebhookMSController::class, 'customerorder']);
