<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ModelController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\BrandController;
use App\Http\Controllers\Web\CrewController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\InvoiceController;
use App\Http\Controllers\Web\MeetingController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Web\ActivityLogController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('models', ModelController::class);
    Route::resource('projects', ProjectController::class);
    Route::resource('brands', BrandController::class);
    Route::post('brands/{brand}/followup', [BrandController::class, 'addFollowup'])->name('brands.followup');
    Route::resource('crew', CrewController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::patch('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status');
    Route::resource('meetings', MeetingController::class);
    Route::get('/search', [SearchController::class, '__invoke'])->name('search');
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    Route::get('/activity-graph/data', [\App\Http\Controllers\Web\ActivityGraphController::class, 'data'])->name('activity-graph.data');
});
