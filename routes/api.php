<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ParticipantController;
use App\Http\Controllers\Api\SuperadminController;
use App\Http\Controllers\Api\StudentController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/register-student', [AuthController::class, 'registerStudent']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/user/update', [AuthController::class, 'updateProfile']);

    Route::get('/dashboard/superadmin', [DashboardController::class, 'superadmin']);
    Route::get('/dashboard/organization', [DashboardController::class, 'organization']);

    Route::get('/events', [EventController::class, 'index']);
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events/{id}', [EventController::class, 'show']);
    Route::post('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);
    Route::patch('/events/{id}/internal-status', [EventController::class, 'updateInternalStatus']);

    Route::get('/participants', [ParticipantController::class, 'index']);
    Route::delete('/participants/{id}', [ParticipantController::class, 'cancel']);
    Route::post('/attendance/{id}', [ParticipantController::class, 'markAttendance']);

    Route::get('/superadmin/pending-events', [SuperadminController::class, 'pendingEvents']);
    Route::post('/superadmin/events/{id}/status', [SuperadminController::class, 'updateEventStatus']);
    Route::get('/superadmin/all-events', [SuperadminController::class, 'allEvents']);
    Route::get('/superadmin/organizations', [SuperadminController::class, 'organizations']);
    Route::put('/superadmin/organizations/{id}', [SuperadminController::class, 'updateOrganization']);
    Route::delete('/superadmin/organizations/{id}', [SuperadminController::class, 'deleteOrganization']); 
    Route::post('/superadmin/user/{id}/approve', [SuperadminController::class, 'approveUser']);

    Route::get('/student/feed', [StudentController::class, 'feed']);
    Route::post('/student/events/{id}/register', [StudentController::class, 'register']);
    Route::get('/student/tickets', [StudentController::class, 'myTickets']);
    Route::delete('/student/tickets/{regId}', [StudentController::class, 'cancel']);
    Route::get('/student/check-committee', [StudentController::class, 'checkCommitteeStatus']);
    Route::get('/student/organizations', [StudentController::class, 'myOrganizations']);
    Route::get('student/organizations', [\App\Http\Controllers\Api\StudentController::class, 'myOrganizations']);
});