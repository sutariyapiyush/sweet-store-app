<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionLogController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Raw Materials Routes
    Route::resource('raw-materials', RawMaterialController::class);

    // Products Routes
    Route::resource('products', ProductController::class);

    // Production Logs Routes
    Route::resource('production-logs', ProductionLogController::class);

    // Users Routes (Admin only)
    Route::resource('users', UserController::class)->middleware('admin');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
