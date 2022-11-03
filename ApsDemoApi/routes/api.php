<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * 以下是客製的Method
 **/

//取得機種清單
Route::post('items/list', 'ItemController@getItemList')->name('items.getItemList');
//取得機種OrderNo
Route::post('items/orderNo', 'ItemController@getOrderNo')->name('items.getOrderNo');
//產生Excel檔案，並轉為二進制回傳
Route::post('excel/{type}', 'ExcelController@getSapExcel')->name('excel.getSapExcel');
//一定要放最底下，不然上面的route會出錯
Route::resources([
    'items' => 'ItemController',
    'excel' => 'ExcelController',
]);
