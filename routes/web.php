<?php

use Illuminate\Support\Facades\Route;

// Root: giriş yapılmışsa admin paneline, yapılmamışsa login'e yönlendir
Route::get('/', function () {
    return auth()->check()
        ? redirect('/admin')
        : redirect('/admin/login');
});

// Laravel'in auth middleware'i giriş yapılmamış kullanıcıları route('login')'e yönlendirir.
// Filament'ın login sayfası /admin/login olduğu için buradan oraya köprü kuruyoruz.
Route::get('/login', fn () => redirect('/admin/login'))->name('login');
