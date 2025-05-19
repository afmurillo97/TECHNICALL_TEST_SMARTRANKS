<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;

Route::get('/', [WelcomeController::class, 'index']);

Route::view('/api/documentation', 'swagger');

Route::get('/api-docs.json', function () {
    return response()->file(storage_path('api-docs/api-docs.json'));
});