<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\HttpResponse\ApiResponse;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\NumberSpecificationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HistoryLogController;

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
    });

    Route::group([
        'prefix' => 'history-log'
    ], function ($router) {
        Route::get('/{type}', [HistoryLogController::class, 'index']);
        Route::post('', [HistoryLogController::class, 'create']);
    });
});

Route::any('{any}', function() {
    $response = ApiResponse::NotFound('Resource Not Found');
    return response()->json($response['json'], $response['status']);
})->where('any', '.*');