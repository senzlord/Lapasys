<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'role:Super Admin|Admin'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// only for Admins
Route::middleware(['auth', 'role:Super Admin|Admin'])->group(function () {
    // User management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/json', [UserController::class, 'showJson'])->name('users.showJson');
    Route::post('/users/{user}/biodata', [UserController::class, 'updateBiodata'])->name('users.updateBiodata');
    Route::post('/users/{user}/employment', [UserController::class, 'addEmployment'])->name('users.addEmployment');
    Route::put('/users/{user}/employment/{employment}', [UserController::class, 'updateEmployment'])->name('users.updateEmployment');
    Route::post('/users/{user}/salary', [UserController::class, 'updateSalary'])->name('users.updateSalary');
    Route::post('/users/{user}/loan', [UserController::class, 'storeLoan'])->name('users.storeLoan');

    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll/{user}/pay', [PayrollController::class, 'pay'])->name('payroll.pay');
    Route::get('/payroll/{user}/loan-options', [PayrollController::class, 'loanJson'])->name('payroll.loanJson');
    Route::get('/payroll/{user}/check-prorate', [PayrollController::class, 'checkProrate'])->name('payroll.checkProrate');

    // Activity log
    Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');
});

require __DIR__.'/auth.php';
