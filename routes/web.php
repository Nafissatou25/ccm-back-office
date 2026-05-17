<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketDocumentController;
use App\Http\Controllers\Admin\DashboardController;


/*
|--------------------------------------------------------------------------
| REDIRECTION ROOT
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});


Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth'])
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

    });

/*
|--------------------------------------------------------------------------
| AUTH (LOGIN / LOGOUT)
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (AUTH REQUIRED)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    | Dashboard
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    | Tickets
    */
    Route::get('/tickets', [TicketController::class, 'index'])
        ->name('tickets.index');

    Route::get('/tickets/create', [TicketController::class, 'create'])
        ->name('tickets.create');

    Route::post('/tickets', [TicketController::class, 'store'])
        ->name('tickets.store');

    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
        ->name('tickets.show');

    /*
    | Actions tickets
    */
    Route::patch('/tickets/{ticket}/assign', [TicketController::class, 'assign'])
        ->name('tickets.assign');

    Route::patch('/tickets/{ticket}/start', [TicketController::class, 'start'])
        ->name('tickets.start');

    Route::patch('/tickets/{ticket}/resolve', [TicketController::class, 'resolve'])
        ->name('tickets.resolve.');

    Route::patch('/tickets/{ticket}/close', [TicketController::class, 'close'])
        ->name('tickets.close');

    Route::patch('/tickets/{ticket}/reopen', [TicketController::class, 'reopen'])
        ->name('tickets.reopen');

    Route::patch('/tickets/{ticket}/transfer', [TicketController::class, 'transfer'])
        ->name('tickets.transfer');

    /*
    | Comments & documents
    */
    Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store'])
        ->name('tickets.comments.store');

    Route::post('/tickets/{ticket}/documents', [TicketDocumentController::class, 'store'])
        ->name('tickets.documents.store');
});