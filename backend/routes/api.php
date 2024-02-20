<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

use App\Http\Controllers\api\v1\admin\GroupController;
use App\Http\Controllers\api\v1\admin\UserController;

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

Route::post('register' , [AuthController::class, 'register']);
Route::post('login'    , [AuthController::class, 'login']);
Route::post('token'    , [AuthController::class, 'token']);
//Route::delete('revoke' , [AuthController::class, 'revoke']);
Route::delete('logout' , [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum', 'ability:system:admin'])->prefix('admin')->group(function () {

    Route::get   ('groups'    , [GroupController::class, 'index']);
    Route::post  ('group'     , [GroupController::class, 'store']);
    Route::get   ('group/{id}', [GroupController::class, 'show']);
    Route::put   ('group/{id}', [GroupController::class, 'update']);
    Route::delete('group/{id}', [GroupController::class, 'destroy']);
    
});
