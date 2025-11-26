<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// API Routes
// All routes in this file are prefixed with /api
// Example: Route::post('/register') becomes /api/register

// Authentication Routes (Public - no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

