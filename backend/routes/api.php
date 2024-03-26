<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

use App\Http\Controllers\api\v1\common\NotificaController;
use App\Http\Controllers\api\v1\common\ProfileController;
use App\Http\Controllers\api\v1\common\DocumentController;

use App\Http\Controllers\api\v1\user\DashboardController;
use App\Http\Controllers\api\v1\user\ActController;
use App\Http\Controllers\api\v1\user\SellController;

use App\Http\Controllers\api\v1\admin\QuoteController;
use App\Http\Controllers\api\v1\admin\LogsController;

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

Route::middleware(['auth:sanctum'])->prefix('document')->group(function () {
    Route::post('upload'       , [DocumentController::class, 'upload']);
    Route::get ('download/{id}', [DocumentController::class, 'download'])->name('download');
    Route::delete('remove/{id}', [DocumentController::class, 'remove']);
});

Route::middleware(['auth:sanctum', 'ability:system:user'])->prefix('user')->group(function () {

    Route::get   ('dashboard', [DashboardController::class, 'index']);
    
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

    Route::get   ('quote' , [QuoteController::class, 'new']);
    Route::post  ('quote' , [QuoteController::class, 'store']);    
    Route::get   ('quotes', [QuoteController::class, 'historical']);
 
    Route::get   ('quote/period', [QuoteController::class, 'period']);
    
    //Route::put   ('quote/{id}', [QuoteController::class, 'update']);
    //Route::delete('quote/{id}', [QuoteController::class, 'destroy']);
    
    Route::get   ('logs'          , [LogsController::class, 'index']);
    Route::get   ('log/{filename}', [LogsController::class, 'content']);
    Route::delete('log/{filename}', [LogsController::class, 'delete']);
    
});
