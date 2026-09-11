<?php

use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->name('doctor.')->group(function (): void {
    Route::get('/', [DoctorController::class, 'dashboard'])->name('dashboard');
    Route::get('/appointments', [DoctorController::class, 'appointments'])->name('appointments.index');
    Route::get('/appointments/{appointment}', [DoctorController::class, 'showAppointment'])->whereNumber('appointment')->name('appointments.show');
    Route::patch('/appointments/{appointment}', [DoctorController::class, 'updateAppointment'])->whereNumber('appointment')->name('appointments.update');
    Route::get('/patients', [DoctorController::class, 'patients'])->name('patients.index');
    Route::get('/patients/{patient}', [DoctorController::class, 'showPatient'])->whereNumber('patient')->name('patients.show');
    Route::get('/schedule', [DoctorScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/schedule', [DoctorScheduleController::class, 'store'])->name('schedule.store');
    Route::get('/schedule/{schedule}/edit', [DoctorScheduleController::class, 'edit'])->whereNumber('schedule')->name('schedule.edit');
    Route::patch('/schedule/{schedule}', [DoctorScheduleController::class, 'update'])->whereNumber('schedule')->name('schedule.update');
    Route::delete('/schedule/{schedule}', [DoctorScheduleController::class, 'destroy'])->whereNumber('schedule')->name('schedule.destroy');
    Route::get('/profile', [DoctorController::class, 'profile'])->name('profile');
    Route::patch('/profile', [DoctorController::class, 'updateProfile'])->name('profile.update');
});
