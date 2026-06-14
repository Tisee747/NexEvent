<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\EventController;
use App\Http\Controllers\Web\ParticipantController;
use App\Http\Controllers\Web\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('/login', function () { return view('auth.login'); })->name('login');
    Route::post('/login', [AuthController::class, 'loginPost'])->name('login.post');
    
    Route::get('/register', function () { return view('auth.register'); })->name('register');
    Route::post('/register', [AuthController::class, 'registerPost'])->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [App\Http\Controllers\Web\DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::get('/events/{id}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('events.destroy');

    Route::get('/participants', [ParticipantController::class, 'index'])->name('participants.index');
    Route::get('/attendance', [ParticipantController::class, 'attendance'])->name('attendance.index');
    Route::post('/attendance/{id}', [ParticipantController::class, 'markAttendance'])->name('attendance.mark');

    Route::get('/superadmin/dashboard', [App\Http\Controllers\Web\SuperadminController::class, 'dashboard'])->name('superadmin.dashboard');
    Route::get('/superadmin', [App\Http\Controllers\Web\SuperadminController::class, 'index'])->name('superadmin.index');
    Route::get('/superadmin/event/{id}', [App\Http\Controllers\Web\SuperadminController::class, 'showEvent'])->name('superadmin.showEvent');
    Route::get('/superadmin/all-events', [App\Http\Controllers\Web\SuperadminController::class, 'allEvents'])->name('superadmin.allEvents');

    Route::get('/superadmin/organizations', [App\Http\Controllers\Web\SuperadminController::class, 'organizations'])->name('superadmin.organizations');
    Route::put('/superadmin/organizations/{id}', [App\Http\Controllers\Web\SuperadminController::class, 'updateOrganization'])->name('superadmin.updateOrg');
    Route::delete('/superadmin/organizations/{id}', [App\Http\Controllers\Web\SuperadminController::class, 'deleteOrganization'])->name('superadmin.deleteOrg');

    Route::post('/superadmin/user/{id}', [App\Http\Controllers\Web\SuperadminController::class, 'approveUser'])->name('superadmin.approveUser');
    Route::post('/superadmin/event/{id}', [App\Http\Controllers\Web\SuperadminController::class, 'updateEventStatus'])->name('superadmin.updateEvent');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});