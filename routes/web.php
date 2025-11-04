<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Controllers\MainController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\RuleController;
use App\Http\Controllers\Admin\ReportController;

Route::get('/', [MainController::class, 'index'])->name('/');
Route::get('/login', [MainController::class, 'index'])->name('login');
Route::post('/login/auth', [MainController::class, 'login'])->name('login.auth');
Route::get('/logout', [MainController::class, 'logout'])->name('logout');
Route::get('/scan', [MainController::class, 'scan'])->name('scan');
Route::post('/scan', [MainController::class, 'scanStore'])->name('scan.store');
Route::get('/lineoff', [MainController::class, 'lineoff'])->name('lineoff');

Route::middleware(AdminMiddleware::class)->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    Route::get('/user', [UserController::class, 'index'])->name('user');
    Route::get('/user/add', [UserController::class, 'add'])->name('user.add');
    Route::post('/user/create', [UserController::class, 'create'])->name('user.create');
    Route::get('/user/edit/{Id_User}', [UserController::class, 'edit'])->name('user.edit');
    Route::put('/user/update/{Id_User}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/user/delete/{Id_User}', [UserController::class, 'destroy'])->name('user.destroy');

    Route::get('/plan', [PlanController::class, 'index'])->name('plan');
    Route::get('/plan/add', [PlanController::class, 'add'])->name('plan.add');
    Route::post('/plan/create', [PlanController::class, 'create'])->name('plan.create');
    Route::get('/plan/edit/{Id_Plan}', [PlanController::class, 'edit'])->name('plan.edit');
    Route::put('/plan/update/{Id_Plan}', [PlanController::class, 'update'])->name('plan.update');
    Route::delete('/plan/delete/{Id_Plan}', [PlanController::class, 'destroy'])->name('plan.destroy');
    Route::post('/plan/import', [PlanController::class, 'import'])->name('plan.import');

    Route::get('/rule', [RuleController::class, 'index'])->name('rule');
    Route::get('/rule/add', [RuleController::class, 'add'])->name('rule.add');
    Route::post('/rule/create', [RuleController::class, 'create'])->name('rule.create');
    Route::get('/rule/edit/{Id_Rule}', [RuleController::class, 'edit'])->name('rule.edit');
    Route::put('/rule/update/{Id_Rule}', [RuleController::class, 'update'])->name('rule.update');
    Route::delete('/rule/delete/{Id_Rule}', [RuleController::class, 'destroy'])->name('rule.destroy');
    Route::post('/rule/import', [RuleController::class, 'import'])->name('rule.import');

    Route::get('/report/lineoff', [ReportController::class, 'lineoff'])->name('report.lineoff');
    Route::get('/report/filter', [ReportController::class, 'filter'])->name('report.filter');
});
