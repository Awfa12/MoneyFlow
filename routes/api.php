<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// API Routes
// All routes in this file are prefixed with /api
// Example: Route::post('/register') becomes /api/register

// Authentication Routes (Public - no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/transfers', [TransferController::class, 'store'])->middleware('auth:sanctum');

// Transaction History Routes (Protected - authentication required)
Route::get('/transactions', [TransactionController::class, 'index'])->middleware('auth:sanctum');
Route::get('/transactions/{uuid}', [TransactionController::class, 'show'])->middleware('auth:sanctum');

