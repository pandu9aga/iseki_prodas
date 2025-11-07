<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\RuleController;
use App\Http\Controllers\Admin\ReportController;

Route::get('/lineoffs-data', [MainController::class, 'getLineoffs'])->name('lineoffs.data');

Route::get('/api_dashboard', [AdminController::class, 'dashboard']);
Route::get('/api_dashboard2', [AdminController::class, 'dashboard2']);
Route::get('/api_dashboard3', [AdminController::class, 'dashboard3']);

Route::get('/plans-data', [PlanController::class, 'getPlans'])->name('plans.data');
Route::post('/record-process-by-sequence', [PlanController::class, 'recordProcessBySequence']);

Route::get('/rules-data', [RuleController::class, 'getRules'])->name('rules.data');

Route::get('/report/lineoffs-data', [ReportController::class, 'getLineoffs'])->name('report.lineoffs.data');
Route::get('/report/filters-data', [ReportController::class, 'getFilters'])->name('report.filters.data');
Route::get('/report/missings-data', [ReportController::class, 'getMissings'])->name('report.missings.data');
