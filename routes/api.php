<?php

use Illuminate\Http\Request;
use App\HttpResponse\ApiResponse;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HistoryLogController;
use App\Http\Controllers\NumberSpecificationController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
    'middleware' => ['api'],
], function ($router) {

    Route::get('license/check', [LicenseController::class, 'checkLicense']);
    Route::post('license/activate', [LicenseController::class, 'activate']);
    Route::post('license/save-token', [LicenseController::class, 'saveToken']);
    Route::get('license/device', [LicenseController::class, 'device']);
    
    Route::group([
        'prefix' => 'device'
    ], function ($router) {
        Route::get('/', [DeviceController::class, 'index']);
        Route::post('/', [DeviceController::class, 'create']);
        Route::put('/{id}', [DeviceController::class, 'update']);
        Route::get('/first', [DeviceController::class, 'first']);
        Route::post('/first', [DeviceController::class, 'firstDeviceCreate']);
    });

    Route::group([
        'prefix' => 'auth'
    ], function ($router) {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/profile', [AuthController::class, 'userProfile']);
        Route::get('/check', [AuthController::class, 'check']);
        Route::get('', [AuthController::class, 'index']);
        Route::delete('/{id}',[AuthController::class, 'delete']);
        Route::put('/{id}',[AuthController::class, 'update']);
        Route::put('/psw/{id}',[AuthController::class, 'passwordUpdate']);
    });

    Route::group([
        'prefix' => 'shop'
    ], function ($router) {
        Route::post('', [ShopController::class, 'create']);
        Route::get('', [ShopController::class, 'index']);
        Route::put('/{id}', [ShopController::class, 'update']);
    });

    Route::group([
        'prefix' => 'number-specification'
    ], function ($router) {
        Route::get('', [NumberSpecificationController::class, 'index']);
        Route::get('/check', [NumberSpecificationController::class, 'check']);
        Route::put('/{id}', [NumberSpecificationController::class, 'update']);
    });

    Route::group([
        'prefix' => 'category'
    ], function ($router) {
        Route::get('', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'category']);
        Route::post('', [CategoryController::class, 'create']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'delete']);
        Route::delete('', [CategoryController::class, 'deleteMultiple']);
    });

    Route::group([
        'prefix' => 'history-log'
    ], function ($router) {
        Route::get('/{type}', [HistoryLogController::class, 'index']);
        Route::post('', [HistoryLogController::class, 'create']);
    });

    Route::group([
        'prefix' => 'items'
    ], function ($router) {
        Route::get('', [ItemController::class, 'index']);
        Route::get('/export', [ItemController::class, 'export']);
        Route::get('/import', [ItemController::class, 'import']);
        Route::post('', [ItemController::class, 'create']);
        Route::put('/{id}', [ItemController::class, 'update']);
        Route::post('/percent', [ItemController::class, 'changePercent']);
        Route::get('/{id}', [ItemController::class, 'detail']);
        Route::delete('/{id}', [ItemController::class, 'delete']);
        Route::delete('', [ItemController::class, 'deleteMultiple']);
    });

    Route::group([
        'prefix' => 'customer'
    ], function ($router) {
        Route::get('', [CustomerController::class, 'index']);
        Route::post('', [CustomerController::class, 'create']);
        Route::put('/{id}', [CustomerController::class, 'update']);
        Route::get('/{id}', [CustomerController::class, 'detail']);
        Route::delete('/{id}', [CustomerController::class, 'delete']);
        Route::delete('', [CustomerController::class, 'deleteMultiple']);
    });

    Route::group([
        'prefix' => 'invoice'
    ], function ($router) {
        Route::get('', [InvoiceController::class, 'index']);
        Route::get('/export', [InvoiceController::class, 'export']);
        Route::get('byDate', [InvoiceController::class, 'listByDate']);
        Route::post('', [InvoiceController::class, 'create']);
        Route::post('store', [InvoiceController::class, 'test']);
        Route::delete('/{id}', [InvoiceController::class, 'delete']);
        Route::get('restore', [InvoiceController::class, 'restore']);
        Route::get('deleted', [InvoiceController::class, 'deletedList']);
        Route::delete('permanentDel/{id}', [InvoiceController::class, 'permanentDelete']);
    });

});

Route::any('{any}', function() {
    $response = ApiResponse::NotFound('Resource Not Found');
    return response()->json($response['json'], $response['status']);
})->where('any', '.*');
