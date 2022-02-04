<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use \Carbon\Carbon;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\ShopController;
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
    
    Route::group([
        'prefix' => 'auth'
    ], function ($router) {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/profile', [AuthController::class, 'userProfile']);
        Route::get('/check', [AuthController::class, 'check']);
    });

    Route::group([
        'prefix' => 'shop'
    ], function ($router) {
        Route::post('', [ShopController::class, 'create']);
        Route::get('', [ShopController::class, 'index']);
        Route::put('/{id}', [ShopController::class, 'update']);
    });
});

Route::any('{any}', function() {
    return response()->json([
    	'success' => false,
        'message' => 'Resource not found',
        'date' => Carbon::now()->format('Y-m-d H:i:s')
    ], 404);
})->where('any', '.*');