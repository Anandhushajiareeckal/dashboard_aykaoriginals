<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ModelApiController;
use App\Http\Controllers\Api\ProjectApiController;
use App\Http\Controllers\Api\MeetingApiController;
use App\Http\Controllers\Api\AccountApiController;

Route::prefix('v1')->group(function () {
    // Public
    Route::post('/login', [AuthController::class, 'login']);

    // Protected
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Models
        Route::get('/models', [ModelApiController::class, 'index']);
        Route::get('/models/{model}', [ModelApiController::class, 'show']);

        // Projects
        Route::get('/projects', [ProjectApiController::class, 'index']);
        Route::get('/projects/{project}', [ProjectApiController::class, 'show']);

        // Meetings
        Route::get('/meetings', [MeetingApiController::class, 'index']);

        // Accounts
        Route::get('/accounts/invoices', [AccountApiController::class, 'invoices']);
        Route::get('/accounts/summary', [AccountApiController::class, 'summary']);
    });
});