<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FilmController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Routes films protégées par authentification
Route::middleware('auth')->group(function () {
    Route::get('/films', [FilmController::class, 'index'])->name('films.index');

    // Formulaire de création et soumission d'un nouveau film
    Route::get('/films/create', [FilmController::class, 'create'])->name('films.create');
    Route::post('/films', [FilmController::class, 'store'])->name('films.store');

    // Suppresion d'un film
    Route::delete('/films/{id}', [FilmController::class, 'destroy'])->name('films.destroy');
    
    // Edition et mise à jour d'un film
    Route::get('/films/{id}/edit', [FilmController::class, 'edit'])->name('films.edit');
    Route::match(['put','patch'],'/films/{id}', [FilmController::class, 'update'])->name('films.update');

    // Affichage d'un film par id (doit être après la route create pour éviter les collisions)
    Route::get('/films/{id}', [FilmController::class, 'show'])->name('films.show');
});
