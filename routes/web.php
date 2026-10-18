<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CambioPasswordController;
use App\Http\Controllers\EstudianteController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::resource('usuarios', UserController::class)
        ->parameters(['usuarios' => 'user'])
        ->except(['show']);

    Route::patch('usuarios/{user}/estado', [UserController::class, 'cambiarEstado'])
        ->name('usuarios.estado');
});
Route::middleware('auth')->group(function () {
    Route::get('cambiar-password', [CambioPasswordController::class, 'edit'])
        ->name('password.forzar');
    Route::put('cambiar-password', [CambioPasswordController::class, 'update'])
        ->name('password.forzar.update');
});
Route::middleware(['auth', 'verified', 'rol:admin,administrativo'])->group(function () {
    Route::resource('estudiantes', EstudianteController::class)
        ->parameters(['estudiantes' => 'estudiante'])
        ->except(['show']);
});
require __DIR__.'/auth.php';
