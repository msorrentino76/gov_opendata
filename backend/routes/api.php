<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

use App\Http\Controllers\api\v1\common\NotificaController;
use App\Http\Controllers\api\v1\common\ProfileController;

use App\Http\Controllers\api\v1\user\ActController;
use App\Http\Controllers\api\v1\user\SellController;

use App\Http\Controllers\api\v1\admin\QuoteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::post('register' , [AuthController::class, 'register']);
//Route::post('login'    , [AuthController::class, 'login']);

/* LOGIN */
Route::post('token'    , [AuthController::class, 'token']);

//Route::delete('logout' , [AuthController::class, 'logout']);

/* LOGOUT */
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('revoke' , [AuthController::class, 'revoke']);
});

Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    Route::get('all'      , [NotificaController::class, 'all']);
    Route::get('unread'   , [NotificaController::class, 'unread']);
    Route::put('mark/{id}', [NotificaController::class, 'markAsRead']);
});

Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
    Route::put('account/{id}' , [ProfileController::class, 'account']);
    Route::put('password/{id}', [ProfileController::class, 'password']);
});

Route::middleware(['auth:sanctum', 'ability:system:user'])->prefix('user')->group(function () {

    Route::get   ('acts'    , [ActController::class, 'index']);
    Route::post  ('act'     , [ActController::class, 'store']);
    //Route::get   ('act/{id}', [ActController::class, 'show']);
    //Route::put   ('act/{id}', [ActController::class, 'update']);
    //Route::delete('act/{id}', [ActController::class, 'destroy']);

    Route::get   ('sells'    , [SellController::class, 'index']);
    Route::post  ('sell'     , [SellController::class, 'store']);
    //Route::get   ('sell/{id}', [SellController::class, 'show']);
    //Route::put   ('sell/{id}', [SellController::class, 'update']);
    //Route::delete('sell/{id}', [SellController::class, 'destroy']);
    
});

Route::middleware(['auth:sanctum', 'ability:system:admin'])->prefix('admin')->group(function () {

    Route::get   ('quotes'    , [QuoteController::class, 'index']);
    Route::post  ('quote'     , [QuoteController::class, 'store']);
    Route::get   ('quote/{id}', [QuoteController::class, 'show']);
    Route::put   ('quote/{id}', [QuoteController::class, 'update']);
    Route::delete('quote/{id}', [QuoteController::class, 'destroy']);
    
});
