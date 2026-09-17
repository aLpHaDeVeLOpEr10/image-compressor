<?php

use App\Http\Controllers\Admin\AuthenticatedSessionController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ToolController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('tools', [ToolController::class, 'index'])->name('tools.index');
        Route::get('tools/create', [ToolController::class, 'create'])->name('tools.create');
        Route::post('tools', [ToolController::class, 'store'])->name('tools.store');
        Route::get('tools/trash', [ToolController::class, 'trash'])->name('tools.trash');
        Route::patch('tools/trash/{toolKey}/restore', [ToolController::class, 'restore'])->name('tools.restore');
        Route::delete('tools/trash/{toolKey}', [ToolController::class, 'forceDestroy'])->name('tools.force-destroy');
        Route::get('tools/{toolKey}/edit', [ToolController::class, 'edit'])->name('tools.edit');
        Route::put('tools/{toolKey}', [ToolController::class, 'update'])->name('tools.update');
        Route::delete('tools/{toolKey}', [ToolController::class, 'destroy'])->name('tools.destroy');
        Route::post('tools/{toolKey}/sync-defaults', [ToolController::class, 'syncDefaults'])->name('tools.sync-defaults');
        Route::get('tools/{toolKey}/children/create', [ToolController::class, 'createChild'])->name('tools.children.create');
        Route::post('tools/{toolKey}/children', [ToolController::class, 'storeChild'])->name('tools.children.store');

        Route::get('messages', [ContactMessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [ContactMessageController::class, 'show'])->name('messages.show');
        Route::patch('messages/{message}/read', [ContactMessageController::class, 'toggleRead'])->name('messages.toggle-read');
        Route::delete('messages/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');
    });
});
