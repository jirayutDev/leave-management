<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LeaveRequestController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/auth/google', [LoginController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [LoginController::class, 'handleGoogleCallback']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Employees
    Route::resource('employees', EmployeeController::class);
    Route::get('employees/{employee}/quota', [EmployeeController::class, 'editQuota'])->name('employees.quota.edit');
    Route::put('employees/{employee}/quota', [EmployeeController::class, 'updateQuota'])->name('employees.quota.update');

    // Leave records
    Route::get('leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::post('leave', [LeaveController::class, 'store'])->name('leave.store');
    Route::delete('leave/{leaveRecord}', [LeaveController::class, 'destroy'])->name('leave.destroy');

    // Leave Requests
    Route::resource('leave-requests', LeaveRequestController::class);
    Route::get('leave-requests-calc-days', [LeaveRequestController::class, 'calcDays'])->name('leave-requests.calc-days');

    // CSV Import
    Route::get('import', [ImportController::class, 'showForm'])->name('import.form');
    Route::post('import', [ImportController::class, 'import'])->name('import.process');

    // Export
    Route::get('export', [ExportController::class, 'showForm'])->name('export.form');
    Route::post('export/excel', [ExportController::class, 'exportExcel'])->name('export.excel');
    Route::post('export/csv', [ExportController::class, 'exportCsv'])->name('export.csv');

    // Admin management
    Route::resource('admins', AdminController::class)->only(['index', 'create', 'store', 'destroy']);
});
