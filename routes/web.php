<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\Config\collectionOfPersonalController;
use App\Http\Controllers\Config\DeleteVendorApiController;
use App\Http\Controllers\initialization\indexController;
use App\Http\Controllers\Setting\AccessController;
use App\Http\Controllers\Setting\CreateAuthTokenController;
use App\Http\Controllers\Setting\DocumentController;
use App\Http\Controllers\Setting\KassaController;
use Illuminate\Support\Facades\Route;


Route::get('/', [indexController::class, 'initialization']);
Route::get('/{accountId}/', [indexController::class, 'index'])->name('main');


Route::get('/Setting/createAuthToken/{accountId}', [CreateAuthTokenController::class, 'getCreateAuthToken']);
Route::post('/Setting/createAuthToken/{accountId}', [CreateAuthTokenController::class, 'postCreateAuthToken']);
Route::get('/Setting/Create/AuthToken/{accountId}', [CreateAuthTokenController::class, 'createAuthToken']);


Route::get('/Setting/Kassa/{accountId}', [KassaController::class, 'getKassa'])->name('getKassa');
Route::get('/Setting/Kassa/profile_id/{accountId}', [KassaController::class, 'profile_id']);
Route::get('/Setting/Kassa/cashbox/{accountId}', [KassaController::class, 'cashbox_id']);
Route::get('/Setting/Kassa/sale_point/{accountId}', [KassaController::class, 'cashbox_id']);
Route::post('/Setting/Kassa/{accountId}', [KassaController::class, 'postKassa']);


Route::get('/Setting/Document/{accountId}', [documentController::class, 'getDocument'])->name('getDocument');
Route::post('/Setting/Document/{accountId}', [documentController::class, 'postDocument']);


Route::get('/Setting/Worker/{accountId}', [AccessController::class, 'getWorker'])->name('getWorker');
Route::post('/Setting/Worker/{accountId}', [AccessController::class, 'postWorker']);







Route::get('delete/{accountId}/', [DeleteVendorApiController::class, 'delete']);
Route::get('setAttributes/{accountId}/{tokenMs}', [AttributeController::class, 'setAllAttributesVendor']);
//для админа
Route::get('/web/getPersonalInformation/', [collectionOfPersonalController::class, 'getPersonal']);
Route::get('/collectionOfPersonalInformation/{accountId}/', [collectionOfPersonalController::class, 'getCollection']);
