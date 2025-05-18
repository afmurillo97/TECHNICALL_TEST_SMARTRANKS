<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/api/documentation', 'swagger');

// Ruta para el JSON generado (asegúrate que existe)
Route::get('/api-docs.json', function () {
    return response()->file(storage_path('api-docs/api-docs.json'));
});