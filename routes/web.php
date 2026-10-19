<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CambioPasswordController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\SeccionController;
use App\Http\Controllers\MatriculaController;

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
    Route::get('estudiantes/plantilla', [EstudianteController::class, 'plantilla'])
        ->name('estudiantes.plantilla');
    Route::get('estudiantes/importar', [EstudianteController::class, 'importar'])
        ->name('estudiantes.importar');
    Route::post('estudiantes/importar', [EstudianteController::class, 'procesarImportacion'])
        ->name('estudiantes.importar.procesar');

    Route::resource('estudiantes', EstudianteController::class)
        ->parameters(['estudiantes' => 'estudiante'])
        ->except(['show']);
});

Route::middleware(['auth', 'verified', 'rol:admin,administrativo'])->group(function () {
    Route::resource('secciones', SeccionController::class)
        ->parameters(['secciones' => 'seccion'])
        ->except(['show']);
});

Route::get('secciones/{seccion}/matriculas', [MatriculaController::class, 'index'])
    ->name('matriculas.index');
Route::post('secciones/{seccion}/matriculas', [MatriculaController::class, 'store'])
    ->name('matriculas.store');
Route::delete('matriculas/{matricula}', [MatriculaController::class, 'destroy'])
    ->name('matriculas.destroy');
require __DIR__.'/auth.php';
