<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\InventoryController;

Route::get('/', function () {
    return redirect()->route('login');
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

    // Routes pour la gestion du stock (inventaire)
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');

    // Formulaire de création et soumission d'un nouveau DVD
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');

    // Suppression d'un DVD
    Route::delete('/inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

    // Edition et mise à jour d'un DVD
    Route::get('/inventory/{id}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::match(['put','patch'],'/inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update');

});
