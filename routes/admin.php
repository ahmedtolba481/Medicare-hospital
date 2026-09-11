<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDepartmentController;
use App\Http\Controllers\AdminDoctorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('doctors', AdminDoctorController::class)->except('show');
    Route::resource('departments', AdminDepartmentController::class)->except('show');
    Route::get('/patients', [AdminController::class, 'patients'])->name('patients.index');
    Route::get('/patients/{patient}', [AdminController::class, 'showPatient'])->whereNumber('patient')->name('patients.show');
    Route::get('/appointments', [AdminController::class, 'appointments'])->name('appointments.index');
    Route::get('/appointments/{appointment}', [AdminController::class, 'showAppointment'])->whereNumber('appointment')->name('appointments.show');
    Route::get('/messages', [AdminController::class, 'messages'])->name('messages.index');
    Route::get('/messages/{message}', [AdminController::class, 'showMessage'])->whereNumber('message')->name('messages.show');
    Route::patch('/messages/{message}', [AdminController::class, 'updateMessage'])->whereNumber('message')->name('messages.update');
    Route::delete('/messages/{message}', [AdminController::class, 'destroyMessage'])->whereNumber('message')->name('messages.destroy');
});
