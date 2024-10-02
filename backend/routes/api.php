<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

use App\Http\Controllers\api\v1\common\NotificaController;
use App\Http\Controllers\api\v1\common\ProfileController;
use App\Http\Controllers\api\v1\common\AssistenzaController;
use App\Http\Controllers\api\v1\common\DocumentController;

use App\Http\Controllers\api\v1\common\UserController;

use App\Http\Controllers\api\v1\sysAdmin\LegalEntityController;
use App\Http\Controllers\api\v1\sysAdmin\LicenseController;
use App\Http\Controllers\api\v1\sysAdmin\StatisticheController;
use App\Http\Controllers\api\v1\sysAdmin\LogsController;
use App\Http\Controllers\api\v1\sysAdmin\MainteinanceController;

use App\Http\Controllers\api\v1\legalEntityAdmin\OrganizationalUnitController;
use App\Http\Controllers\api\v1\legalEntityAdmin\OuUserController;
use App\Http\Controllers\api\v1\legalEntityAdmin\IstatController;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('assistance' , [AssistenzaController::class, 'send']);
});

Route::middleware(['auth:sanctum'])->prefix('document')->group(function () {
    Route::post('upload'       , [DocumentController::class, 'upload']);
    Route::get ('download/{id}', [DocumentController::class, 'privateDownload'])->name('privateDownload');
    Route::delete('remove/{id}', [DocumentController::class, 'remove']);
});

Route::prefix('public')->group(function () {
    Route::get ('download/{id}'          , [DocumentController::class, 'publicDownload'])->name('publicDownload');
    //Route::get ('download/{id}/thumbnail', [DocumentController::class, 'publicDownload'])->name('publicDownloadThumbnail');
});

//Route::middleware(['auth:sanctum', 'ability:system:admin'])->prefix('sys_admin')->group(function () {
//    Route::get ('acts'    , [ActController::class, 'index']);
//});

Route::middleware(['auth:sanctum', 'ability:system:admin,legal_entity:admin'])->group(function () {
    Route::get   ('users'        , [UserController::class, 'index']);
    Route::post  ('user'         , [UserController::class, 'create']);
    Route::get   ('user/{id}'    , [UserController::class, 'read']);
    Route::put   ('user/{id}'    , [UserController::class, 'update']);
    Route::delete('user/{id}'    , [UserController::class, 'destroy']); 
    Route::put   ('resetpwd/{id}', [UserController::class, 'resetPwd']);
    Route::put   ('toggle/{id}'  , [UserController::class, 'toggle']);
    Route::get   ('user_activities/{id}'  , [UserController::class, 'userActivities']);    
});

Route::middleware(['auth:sanctum', 'ability:system:admin'])->prefix('sys_admin')->group(function () { 
    
    Route::get   ('legals'        , [LegalEntityController::class, 'index']);
    Route::post  ('legal'         , [LegalEntityController::class, 'create']);
    Route::get   ('legal/{id}'    , [LegalEntityController::class, 'read']);
    Route::put   ('legal/{id}'    , [LegalEntityController::class, 'update']);
    Route::delete('legal/{id}'    , [LegalEntityController::class, 'destroy']);    
    Route::post  ('legal_suggestions' , [LegalEntityController::class, 'legalSuggestions']);
    Route::post  ('legal_ipa_details' , [LegalEntityController::class, 'legalIPADetails']);
    Route::get   ('legal_activities/{id}'  , [LegalEntityController::class, 'legalActivities']);
    
    Route::get   ('logs'          , [LogsController::class, 'index']);
    Route::get   ('log/{filename}', [LogsController::class, 'content']);
    Route::delete('log/{filename}', [LogsController::class, 'delete']);
    
    Route::get   ('licences'        , [LicenseController::class, 'index']);
    Route::get   ('available'       , [LicenseController::class, 'available']); 
    Route::post  ('licence'         , [LicenseController::class, 'create']);
    Route::get   ('licence/{id}'    , [LicenseController::class, 'read']);
    Route::put   ('licence/{id}'    , [LicenseController::class, 'update']);
    Route::delete('licence/{id}'    , [LicenseController::class, 'destroy']);    
    Route::get   ('licence_activities/{id}'  , [LicenseController::class, 'licenceActivities']);
    
    Route::get   ('accessi'       , [StatisticheController::class, 'index']);
    Route::get   ('ultimi'        , [StatisticheController::class, 'last']);    
    
    Route::prefix('manteinance')->group(function () {
        Route::get('codelist'     , [MainteinanceController::class, 'codelist']);
    });
});
 
Route::middleware(['auth:sanctum', 'ability:legal_entity:admin'])->prefix('le_admin')->group(function () {
    
    Route::get   ('ous'        , [OrganizationalUnitController::class, 'index']);
    Route::post  ('ou'         , [OrganizationalUnitController::class, 'create']);
    Route::get   ('ou/{id}'    , [OrganizationalUnitController::class, 'read']);
    Route::put   ('ou/{id}'    , [OrganizationalUnitController::class, 'update']);
    Route::delete('ou/{id}'    , [OrganizationalUnitController::class, 'destroy']); 
    Route::get   ('ou_autofill'         , [OrganizationalUnitController::class, 'ouAutofill']);
    Route::get   ('ou_activities/{id}'  , [OrganizationalUnitController::class, 'ouActivities']);    
    
    Route::get   ('users_ous'       , [OuUserController::class, 'index']);
    Route::put   ('user_ou/{id}'    , [OuUserController::class, 'update']);
    Route::get   ('user_ou_act/{id}', [OuUserController::class, 'ouuserActivities']);
    
    Route::get   ('dataset'    , [IstatController::class, 'index']);
    Route::post  ('datafilter' , [IstatController::class, 'datafilter']);
});

Route::middleware(['auth:sanctum', 'ability:legal_entity:admin,ou:user'])->prefix('manuali')->group(function () {
    
    Route::get ('/download'   , [UserController::class, 'scaricaManuale'])->withoutMiddleware('api');
    
});