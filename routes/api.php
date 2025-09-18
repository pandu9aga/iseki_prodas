<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PlanController;

Route::get('/api_dashboard', [AdminController::class, 'dashboard']);
Route::get('/api_dashboard2', [AdminController::class, 'dashboard2']);

Route::get('/plans-data', [PlanController::class, 'getPlans'])->name('plans.data');

