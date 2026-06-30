<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketCommentController;
use App\Http\Controllers\Api\TicketDocumentController;
use App\Http\Controllers\Api\TypeController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\AgencyController;
use App\Http\Controllers\Api\Admin\SlaController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\WhatsAppBotController;

/*
|--------------------------------------------------------------------------
| WEBHOOK WhatsApp
|--------------------------------------------------------------------------
*/
Route::post('/whatsapp/webhook', [WhatsAppBotController::class, 'handle']);
Route::get('/whatsapp/webhook', [WhatsAppBotController::class, 'verify']);

/*
|--------------------------------------------------------------------------
| AUTH PUBLIC
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (SANCTUM)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH USER
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/fcm-token', [AuthController::class, 'updateFcmToken']);
    Route::post('/onesignal-player-id', [AuthController::class, 'updateOneSignalPlayerId']);

    /*
    |--------------------------------------------------------------------------
    | DONNÉES FORMULAIRES
    |--------------------------------------------------------------------------
    */
    Route::get('/units',      [UnitController::class, 'index']);
    Route::get('/agencies',   [AgencyController::class, 'index']);
    Route::get('/types',      [TypeController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | TICKETS — LECTURE & CRÉATION
    |--------------------------------------------------------------------------
    */
    Route::get('/tickets',              [TicketController::class, 'index']);
    Route::get('/tickets/{ticket}',     [TicketController::class, 'show']);
    Route::post('/tickets',             [TicketController::class, 'store'])->middleware('role:CUSTOMER_SERVICE,MANAGER,SUPERVISOR');
    Route::post('/types/quick',         [TicketController::class, 'quickStore']);

    /*
    |--------------------------------------------------------------------------
    | TICKETS — ACTIONS
    |--------------------------------------------------------------------------
    */
    Route::get('/supervisors',                  [TicketController::class, 'supervisors']);
    Route::get('/supervisors/filter',           [TicketController::class, 'filterSupervisors']);
    Route::get('/companies/{company}/supervisors', [\App\Http\Controllers\CompanyController::class, 'supervisors']);

    // Assignation
    Route::put('/tickets/{ticket}/assign-technicians', [TicketController::class, 'assignTechnicians'])
        ->middleware('role:SUPERVISOR,MANAGER,ADMIN');

    Route::get('/tickets/{ticket}/technicians', [TicketController::class, 'getTechnicians']);

    // Actions principales
    Route::patch('/tickets/{ticket}/start',   [TicketController::class, 'start']);
    Route::match(['patch', 'post'], '/tickets/{ticket}/resolve', [TicketController::class, 'resolve']);
    Route::post('/tickets/{ticket}/close',    [TicketController::class, 'close']);
    Route::match(['patch', 'post'], '/tickets/{ticket}/hold',    [TicketController::class, 'hold']);
    Route::match(['patch', 'post'], '/tickets/{ticket}/resume',  [TicketController::class, 'resume']);
    Route::match(['patch', 'post'], '/tickets/{ticket}/transfer',[TicketController::class, 'transfer']);
    Route::match(['patch', 'post'], '/tickets/{ticket}/reopen',  [TicketController::class, 'reopen']);
    Route::post('/tickets/{ticket}/status',   [TicketController::class, 'changeStatus']);

    /*
    |--------------------------------------------------------------------------
    | DOCUMENTS
    |--------------------------------------------------------------------------
    */
    Route::get('/tickets/{ticket}/documents',           [TicketDocumentController::class, 'index']);
    Route::post('/tickets/{ticket}/documents',          [TicketController::class, 'storeDocument']);
    Route::get('/documents/{document}/download',        [TicketDocumentController::class, 'download']);

    /*
    |--------------------------------------------------------------------------
    | COMMENTAIRES
    |--------------------------------------------------------------------------
    */
    Route::get('/tickets/{ticket}/comments',            [TicketCommentController::class, 'index']);
    Route::post('/tickets/{ticket}/comments',           [TicketCommentController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | ADMIN PANEL
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:ADMIN')
        ->prefix('admin')
        ->group(function () {
            Route::apiResource('users',      UserController::class);
            Route::apiResource('categories', CategoryController::class);
            Route::apiResource('types',      TypeController::class);
            Route::apiResource('units',      UnitController::class);
            Route::apiResource('agencies',   AgencyController::class);
            Route::apiResource('slas',       SlaController::class);
            Route::apiResource('roles',      RoleController::class);
        });
});

Route::get('/companies', [CompanyController::class, 'index'])->middleware('auth:sanctum');