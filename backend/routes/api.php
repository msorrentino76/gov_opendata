<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

use App\Http\Controllers\api\v1\common\NotificaController;
use App\Http\Controllers\api\v1\common\ProfileController;
use App\Http\Controllers\api\v1\common\DocumentController;

use App\Http\Controllers\api\v1\sysAdmin\LegalEntityAdminController;
use App\Http\Controllers\api\v1\sysAdmin\LegalEntityController;
use App\Http\Controllers\api\v1\sysAdmin\LogsController;

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


//Route::middleware(['auth:sanctum', 'ability:system:admin'])->prefix('sys_admin')->group(function () {
//    Route::get ('acts'    , [ActController::class, 'index']);
//});


Route::middleware(['auth:sanctum', 'ability:system:admin'])->prefix('sys_admin')->group(function () {
    
    Route::get   ('legals'        , [LegalEntityController::class, 'index']);
    Route::post  ('legal'         , [LegalEntityController::class, 'create']);
    Route::get   ('legal/{id}'    , [LegalEntityController::class, 'read']);
    Route::put   ('legal/{id}'    , [LegalEntityController::class, 'update']);
    Route::delete('legal/{id}'    , [LegalEntityController::class, 'destroy']);
    
    Route::post  ('legal_suggestions' , [LegalEntityController::class, 'legalSuggestions']);
    Route::post  ('legal_ipa_details' , [LegalEntityController::class, 'legalIPADetails']);
    
    Route::get   ('logs'          , [LogsController::class, 'index']);
    Route::get   ('log/{filename}', [LogsController::class, 'content']);
    Route::delete('log/{filename}', [LogsController::class, 'delete']);
    
});
