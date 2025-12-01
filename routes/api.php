<?php

use App\Http\Controllers\API\StoreController;
use App\Http\Controllers\API\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\DepositController;
use App\Http\Controllers\API\AppsController;

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

Route::controller(RegisterController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::middleware('auth:sanctum')->group( function () {
    Route::middleware('role:admin')->group( function () {
        Route::resource('users', UsersController::class);
        Route::post('deposit', [DepositController::class, 'store']);
        Route::post('withdraw', [TransactionController::class, 'withdraw']);
        Route::post('transfer', [TransactionController::class, 'transfer']);
        Route::post('app-register', [AppsController::class, 'store']);
        Route::get('app/{app}/key', [AppsController::class, 'getAppKey']);
    });

    Route::middleware('role:customer')->group( function () {
        Route::resource('transactions', TransactionController::class);
    });

});

Route::middleware('verify.app')->post('payment', [TransactionController::class, 'payment']);
