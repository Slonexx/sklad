<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\Config\collectionOfPersonalController;
use App\Http\Controllers\Config\DeleteVendorApiController;
use App\Http\Controllers\Entity\PopapController;
use App\Http\Controllers\Entity\PrintController;
use App\Http\Controllers\Entity\widgetController;
use App\Http\Controllers\initialization\indexController;
use App\Http\Controllers\Setting\AccessController;
use App\Http\Controllers\Setting\ChangeController;
use App\Http\Controllers\Setting\CreateAuthTokenController;
use App\Http\Controllers\Setting\DocumentController;
use App\Http\Controllers\Setting\KassaController;
use App\Http\Controllers\Setting\ReportController;
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


Route::get('/kassa/change/{accountId}', [ChangeController::class, 'getChange']);
Route::get('/kassa/MoneyOperation/{accountId}', [ChangeController::class, 'MoneyOperation']);
Route::get('/kassa/MoneyOperation/viewCash/{accountId}', [ChangeController::class, 'viewCash']);
Route::get('/kassa/XReport/{accountId}', [ReportController::class, 'XReport']);
Route::get('/kassa/ZReport/{accountId}', [ReportController::class, 'ZReport']);




Route::get('/widget/{object}', [widgetController::class, 'widgetObject']);
Route::get('/widget/Info/Attributes', [widgetController::class, 'widgetInfoAttributes']);

Route::get('/Popup/{object}', [PopapController::class, 'Popup']);
Route::get('/Popup/{object}/show', [PopapController::class, 'showPopup']);
Route::post('/Popup/{object}/send', [PopapController::class, 'sendPopup']);
Route::get('/Popup/print/{accountId}/{entity_type}/{object}', [PrintController::class, 'PopupPrint']);






Route::get('delete/{accountId}/', [DeleteVendorApiController::class, 'delete']);
Route::get('setAttributes/{accountId}/{tokenMs}', [AttributeController::class, 'setAllAttributesVendor']);
//для админа
Route::get('/web/getPersonalInformation/', [collectionOfPersonalController::class, 'getPersonal']);
Route::get('/collectionOfPersonalInformation/{accountId}/', [collectionOfPersonalController::class, 'getCollection']);
