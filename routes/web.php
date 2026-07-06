<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketDocumentController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\WhatsappRequestController;



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

        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::resource('units', \App\Http\Controllers\Admin\UnitController::class);
        Route::resource('agencies', \App\Http\Controllers\Admin\AgencyController::class);
        Route::resource('types', \App\Http\Controllers\Admin\TypeController::class);
        Route::resource('slaRules', \App\Http\Controllers\Admin\SlaRuleController::class);
         Route::resource('companies', CompanyController::class)->except(['show']);
    Route::patch('companies/{id}/restore', [CompanyController::class, 'restore'])->name('companies.restore');

         Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/',                         [WhatsappRequestController::class, 'index'])->name('index');
        Route::get('/{whatsappRequest}',        [WhatsappRequestController::class, 'show'])->name('show');
        Route::post('/{whatsappRequest}/convert', [WhatsappRequestController::class, 'convert'])->name('convert');
    });
    });

    /*
|--------------------------------------------------------------------------
| USER DASHBOARD
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

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

    // Assignation techniciens (SUPERVISOR uniquement)
    Route::put('/tickets/{ticket}/assign-technicians', [TicketController::class, 'assignTechnicians'])
        ->middleware('role:SUPERVISOR,MANAGER,ADMIN');

    /*
    | Tickets
    */
    Route::get('/tickets', [TicketController::class, 'index'])
        ->name('tickets.index');

    Route::get('/tickets/create', [TicketController::class, 'create'])
        ->name('tickets.create');

    Route::middleware(['auth'])->post('/types/quick', [TicketController::class, 'quickStore'])->name('types.quick');

    Route::post('/tickets', [TicketController::class, 'store'])
        ->name('tickets.store');

    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
        ->name('tickets.show');

    /*
    | Actions tickets
    */
    Route::patch('/tickets/{ticket}/assign', [TicketController::class, 'assign'])
        ->name('tickets.assign');

        Route::post('/tickets/{ticket}/documents', [TicketController::class, 'storeDocument'])
    ->name('tickets.documents.store');

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
    
    Route::patch('/tickets/{ticket}/hold', [TicketController::class, 'hold'])
    ->name('tickets.hold');

    Route::patch('/tickets/{ticket}/resume', [TicketController::class, 'resume'])
    ->name('tickets.resume');

    Route::post('/tickets/{ticket}/resume', [TicketController::class, 'resume'])->name('tickets.resume');
    

    /*
    | Comments & documents
    */
    Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store'])
        ->name('tickets.comments.store');

    
        Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
});