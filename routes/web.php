<?php

use Illuminate\Support\Facades\Route;

// Tampilan Autentikasi
Route::get('/login', function () { return view('auth.login'); })->name('login');
Route::get('/register', function () { return view('auth.register'); })->name('register');

// Tampilan Panitia Organisasi
Route::get('/', function () { return view('dashboard'); })->name('dashboard');
Route::get('/events', function () { return view('events.index'); })->name('events.index');
Route::get('/events/create', function () { return view('events.create'); })->name('events.create');
Route::get('/events/{id}/edit', function () { return view('events.edit'); })->name('events.edit');

Route::get('/participants', function () { return view('participants.index'); })->name('participants.index');
Route::get('/attendance', function () { return view('participants.attendance'); })->name('attendance.index');

// Tampilan Superadmin
Route::get('/superadmin/dashboard', function () { return view('superadmin.dashboard'); })->name('superadmin.dashboard');
Route::get('/superadmin', function () { return view('superadmin.index'); })->name('superadmin.index');
Route::get('/superadmin/event/{id}', function () { return view('superadmin.event_detail'); })->name('superadmin.showEvent');
Route::get('/superadmin/all-events', function () { return view('superadmin.events'); })->name('superadmin.allEvents');
Route::get('/superadmin/organizations', function () { return view('superadmin.organizations'); })->name('superadmin.organizations');