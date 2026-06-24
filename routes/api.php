<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketCommentController;
use App\Http\Controllers\Api\TicketDocumentController;

use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\TypeController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\AgencyController;
use App\Http\Controllers\Api\Admin\SlaController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\WhatsAppBotController;

Route::post('/whatsapp/webhook', [WhatsAppBotController::class, 'handle']);
Route::get('/whatsapp/webhook', [WhatsAppBotController::class, 'verify']); // pour test

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
    Route::post('/onesignal-player-id', [App\Http\Controllers\Api\AuthController::class, 'updateOneSignalPlayerId']);

    /*
    |--------------------------------------------------------------------------
    | DONNÉES FORMULAIRES MOBILES (tous rôles authentifiés)
    |--------------------------------------------------------------------------
    */

    Route::get('/units',      [UnitController::class,     'index']);
    Route::get('/agencies',   [AgencyController::class,   'index']);
    Route::get('/types',      [TypeController::class,     'index']);

    /*
    |--------------------------------------------------------------------------
    | TICKETS — LECTURE
    |--------------------------------------------------------------------------
    */

    Route::get('/tickets',              [TicketController::class, 'index']);
    Route::get('/tickets/{ticket}',     [TicketController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | TICKETS — CRÉATION
    |--------------------------------------------------------------------------
    */

    Route::post('/tickets', [TicketController::class, 'store'])
        ->middleware('role:CUSTOMER_SERVICE,MANAGER,SUPERVISOR');

    /*
    |--------------------------------------------------------------------------
    | TICKETS — ACTIONS
    |--------------------------------------------------------------------------
    */

    Route::get('/supervisors', [TicketController::class, 'supervisors'])->middleware('auth:sanctum');

    // Assignation techniciens (SUPERVISOR uniquement)
    Route::put('/tickets/{ticket}/assign-technicians', [TicketController::class, 'assignTechnicians'])
        ->middleware('role:SUPERVISOR,MANAGER,ADMIN');

    Route::get('/tickets/{ticket}/technicians', [TicketController::class, 'getTechnicians']);

    // Démarrage (TECHNICIAN)
    Route::patch('/tickets/{ticket}/start',   [TicketController::class, 'start']);

    // Résolution (TECHNICIAN)
    Route::patch('/tickets/{ticket}/resolve', [TicketController::class, 'resolve']);
    Route::post('tickets/{ticket}/resolve', [TicketController::class, 'resolve']);

    // Clôture (CUSTOMER_SERVICE, SUPERVISOR, MANAGER, ADMIN)
    Route::post('/tickets/{ticket}/close',    [TicketController::class, 'closeTicket']);

    // Mise en attente
    Route::patch('/tickets/{ticket}/hold',    [TicketController::class, 'hold']);
    Route::post('/tickets/{ticket}/hold',    [TicketController::class, 'hold']);

    // Reprise après attente
    Route::patch('/tickets/{ticket}/resume',  [TicketController::class, 'resume']);
    Route::post('/tickets/{ticket}/resume',  [TicketController::class, 'resume']);

    // Transfert
    Route::patch('/tickets/{ticket}/transfer', [TicketController::class, 'transfer']);
    Route::post('/tickets/{ticket}/transfer', [TicketController::class, 'transfer']);

    // Réouverture
    Route::patch('/tickets/{ticket}/reopen',  [TicketController::class, 'reopen']);
    Route::post('/tickets/{ticket}/reopen',  [TicketController::class, 'reopen']);

    Route::post(
    '/tickets/{ticket}/documents',
    [TicketController::class, 'addDocument']
);

    // Changement de statut générique
    Route::post('/tickets/{ticket}/status',   [TicketController::class, 'changeStatus']);

    /*
    |--------------------------------------------------------------------------
    | COMMENTAIRES
    |--------------------------------------------------------------------------
    */

    Route::get('/tickets/{ticket}/comments',  [TicketCommentController::class, 'index']);
    Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | DOCUMENTS
    |--------------------------------------------------------------------------
    */

    Route::get('/tickets/{ticket}/documents',      [TicketDocumentController::class, 'index']);
    Route::post('/tickets/{ticket}/documents',     [TicketDocumentController::class, 'store']);
    Route::get('/documents/{document}/download',   [TicketDocumentController::class, 'download']);

    Route::get('/supervisors/filter', [App\Http\Controllers\TicketController::class, 'filterSupervisors']);
Route::get('/companies/{company}/supervisors', [App\Http\Controllers\CompanyController::class, 'supervisors']);
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