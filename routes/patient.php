<?php

use App\Http\Controllers\AppointmentBookingController;
use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:patient'])->prefix('patient')->name('patient.')->group(function (): void {
    Route::get('/', [PatientController::class, 'dashboard'])->name('dashboard');
    Route::get('/appointments', [PatientController::class, 'appointments'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentBookingController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentBookingController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{appointment}', [PatientController::class, 'showAppointment'])->whereNumber('appointment')->name('appointments.show');
    Route::patch('/appointments/{appointment}/cancel', [PatientController::class, 'cancelAppointment'])->whereNumber('appointment')->name('appointments.cancel');
    Route::get('/profile', [PatientController::class, 'profile'])->name('profile');
    Route::patch('/profile', [PatientController::class, 'updateProfile'])->name('profile.update');
    Route::get('/messages', [PatientController::class, 'messages'])->name('messages.index');
    Route::post('/messages', [PatientController::class, 'storeMessage'])->name('messages.store');
});
