<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ViewingAppointmentController;


Route::get('/', function () {return Inertia::render('welcome');})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::resource('properties', PropertyController::class)
        ->only(['index', 'create', 'store', 'update']);

    Route::resource('viewing-appointments', ViewingAppointmentController::class)
        ->only(['index', 'create', 'store', 'update']);
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';

