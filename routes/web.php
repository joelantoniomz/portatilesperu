<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Rutas Públicas de Autenticación
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas Protegidas del Panel Administrativo
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard administrativo principal (Fase 1: Layout base y sesión activa)
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/perfil', [AdminController::class, 'profile'])->name('profile');

    // Rutas protegidas por Rol
    Route::middleware(['role:superadmin,admin'])->group(function () {
        Route::get('/usuarios', [AdminController::class, 'users'])->name('users');
    });

    // Rutas protegidas por Permiso
    Route::middleware(['permission:audit.view'])->group(function () {
        Route::get('/auditoria', [AdminController::class, 'auditLogs'])->name('audit');
    });

    // Rutas de verificación de aislamiento por rol (para pruebas automatizadas)
    Route::get('/almacen-test', function () {
        return response()->json(['message' => 'Acceso autorizado al módulo de almacén.']);
    })->middleware(['role:almacen,superadmin'])->name('test.almacen');

    Route::get('/vendedor-test', function () {
        return response()->json(['message' => 'Acceso autorizado al módulo de ventas.']);
    })->middleware(['role:vendedor,superadmin'])->name('test.vendedor');
});
