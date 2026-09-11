<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::get('/about', [PublicSiteController::class, 'about'])->name('about');
Route::get('/services', [PublicSiteController::class, 'services'])->name('services');
Route::get('/departments', [PublicSiteController::class, 'departments'])->name('departments.index');
Route::get('/departments/{department}', [PublicSiteController::class, 'showDepartment'])->name('departments.show');
Route::get('/doctors', [PublicSiteController::class, 'doctors'])->name('doctors.index');
Route::get('/doctors/{doctor}', [PublicSiteController::class, 'showDoctor'])->name('doctors.show');
Route::get('/contact', [PublicSiteController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicSiteController::class, 'storeContact'])->name('contact.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::post('/logout', LogoutController::class)->middleware('auth')->name('logout');

require __DIR__.'/patient.php';
require __DIR__.'/doctor.php';
require __DIR__.'/admin.php';
