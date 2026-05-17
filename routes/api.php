<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TicketController;

use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\TypeController;
use App\Http\Controllers\Api\Admin\UnitController;
use App\Http\Controllers\Api\Admin\AgencyController;
use App\Http\Controllers\Api\Admin\SlaController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\UserController;

/*
|--------------------------------------------------------------------------
| AUTH PUBLIC
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| AUTH DEBUG (optionnel dev)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->get('/debug-user', function () {
    return auth()->user();
});

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

    /*
    |--------------------------------------------------------------------------
    | TICKETS
    |--------------------------------------------------------------------------
    */

    // 🔹 CREATE TICKET (CUSTOMER SERVICE / MANAGER / SUPERVISOR)
    Route::post('/tickets', [TicketController::class, 'store'])
        ->middleware('role:CUSTOMER_SERVICE,MANAGER,SUPERVISOR');

    // 🔹 ASSIGN TECHNICIANS
    Route::put('/tickets/{ticket}/assign-technicians', [TicketController::class, 'assignTechnicians'])
        ->middleware('role:SUPERVISOR');

    // 🔹 START TICKET
    Route::patch('/tickets/{ticket}/start', [TicketController::class, 'start']);

    // 🔹 RESOLVE TICKET
    Route::patch('/tickets/{ticket}/resolve', [TicketController::class, 'resolve']);

    // 🔹 CLOSE TICKET
    Route::post('/tickets/{ticket}/close', [TicketController::class, 'closeTicket']);

    // 🔹 CHANGE STATUS
    Route::post('/tickets/{ticket}/status', [TicketController::class, 'changeStatus']);

    /*
    |--------------------------------------------------------------------------
    | COMMENTS
    |--------------------------------------------------------------------------
    */

    // Route::get('/tickets/{ticket}/comments', [TicketCommentController::class, 'index']);

    // Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store']);

    Route::get('/tickets/{ticket}/comments', [TicketCommentController::class, 'index']);
    Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store']);

    Route::get('/tickets/{ticket}/documents', [TicketDocumentController::class, 'index']);
    Route::post('/tickets/{ticket}/documents', [TicketDocumentController::class, 'store']);

    Route::get('/documents/{document}/download', [TicketDocumentController::class, 'download']);

    /*
    |--------------------------------------------------------------------------
    | MY TICKETS
    |--------------------------------------------------------------------------
    */

    Route::get('/my-tickets', [TicketController::class, 'myTickets'])
        ->middleware('role:TECHNICIAN,ENTERPRISE');

    /*
    |--------------------------------------------------------------------------
    | ADMIN PANEL
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth:sanctum', 'role:ADMIN'])
    ->prefix('admin')
    ->group(function () {

        // USERS
        Route::apiResource('users', UserController::class);

        // CATALOGUE
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('types', TypeController::class);
        Route::apiResource('units', UnitController::class);
        Route::apiResource('agencies', AgencyController::class);

        // SLA
        Route::apiResource('slas', SlaController::class);

        // ROLES
        Route::apiResource('roles', RoleController::class);
    });
});