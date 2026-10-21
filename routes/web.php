<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

// ---- Publik ----
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:6,1')->name('contact.store');

Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');

// ---- Tamu ----
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1')->name('register.store');
});

// ---- Pengguna terautentikasi ----
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::post('/events/{event:slug}/register', [RegistrationController::class, 'store'])->name('events.register');
    Route::get('/my-registrations', [RegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/my-registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');
    Route::patch('/my-registrations/{registration}/cancel', [RegistrationController::class, 'cancel'])->name('registrations.cancel');
});

// ---- Admin ----
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('events', Admin\EventController::class)->except('show')->scoped(['event' => 'id']);
    Route::resource('categories', Admin\CategoryController::class)->except(['show', 'create']);
    Route::get('/registrations', [Admin\RegistrationController::class, 'index'])->name('registrations.index');
    Route::patch('/registrations/{registration}', [Admin\RegistrationController::class, 'update'])->name('registrations.update');
});
