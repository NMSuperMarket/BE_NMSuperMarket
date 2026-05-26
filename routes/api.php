<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CategoryController;

// Auth routes (REQ_01 + REQ_02)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/google', [AuthController::class, 'googleLogin']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// System Stats (REQ_06 + REQ_08)
Route::get('/system-stats', [EventController::class, 'getSystemStats']);

// Event List — search, filter, paginate (REQ_06 + REQ_08)
Route::get('/events', [EventController::class, 'index']);

// Event Detail (REQ_09)
Route::get('/events/{id}', [EventController::class, 'show']);
Route::get('/events/{id}/reviews/stream', [ReviewController::class, 'stream']);

// Categories (REQ_13)
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}/events', [CategoryController::class, 'getEvents']);

use App\Http\Controllers\OrganizerEventController;
use App\Http\Controllers\AttendeeController;

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::post('/events/{id}/register', [RegistrationController::class, 'store']);
    Route::post('/events/{id}/reviews', [ReviewController::class, 'store']);

    // Organizer Dashboard routes
    Route::prefix('organizer')->group(function () {
        Route::get('/dashboard/stats', [OrganizerEventController::class, 'getDashboardStats']);
        Route::get('/dashboard/events', [OrganizerEventController::class, 'getRecentEvents']);
        Route::get('/events', [OrganizerEventController::class, 'index']);
        Route::post('/events', [OrganizerEventController::class, 'store']);
        Route::get('/events/{id}', [OrganizerEventController::class, 'show']);
        Route::put('/events/{id}', [OrganizerEventController::class, 'update']);
        Route::delete('/events/{id}', [OrganizerEventController::class, 'destroy']);
        Route::post('/events/{id}/publish', [OrganizerEventController::class, 'publish']);
        Route::post('/events/{id}/cancel', [OrganizerEventController::class, 'cancel']);
    });

    // Attendee Dashboard routes (REQ_11)
    Route::prefix('attendee')->group(function () {
        Route::get('/dashboard/stats', [AttendeeController::class, 'getDashboardStats']);
        Route::get('/dashboard/registrations', [AttendeeController::class, 'getRegistrations']);
        Route::get('/dashboard/waitlist', [AttendeeController::class, 'getWaitlist']);
        Route::get('/dashboard/cancelled', [AttendeeController::class, 'getCancelledRegistrations']);
    });
});
