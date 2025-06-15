<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/{any}', function () {
//     return view('welcome'); // Ensure 'app' is the correct Blade template for Vue
// })->where('any', '.*');
Route::get('/login', function () {
    return view('login');
})->name('login.form');

// Web routes
Route::get('/{any}', function () {
    return view('welcome'); // This serves your Vue SPA
})->where('any', '^(?!api).*$'); // This pattern excludes routes starting with "api"
