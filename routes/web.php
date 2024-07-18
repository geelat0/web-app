<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('dash-home', [DashboardController::class, 'index']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');


Route::get('user', [UserController::class, 'user_create']);
Route::get('user/create', [UserController::class, 'create']);
Route::post('user/store', [UserController::class, 'store'])->name('user.store');
Route::post('users/store', [UserController::class, 'UserStore'])->name('users.store');
Route::post('users/update', [UserController::class, 'update'])->name('users.update');
Route::get('users/list', [UserController::class, 'list'])->name('user.list');
Route::post('/users/destroy', [UserController::class, 'destroy'])->name('users.destroy');
Route::post('/temp-password', [UserController::class, 'temp_password'])->name('users.temp-password');

Route::get('roles', [RoleController::class, 'roles']);
Route::get('role/list', [RoleController::class, 'list'])->name('role.list');
Route::get('role/data', [RoleController::class, 'getRole'])->name('get.role');
Route::post('role/store', [RoleController::class, 'store'])->name('role.store');
Route::post('role/update', [RoleController::class, 'update'])->name('role.update');
Route::post('/role/destroy', [RoleController::class, 'destroy'])->name('role.destroy');



Route::middleware(['guest'])->group(function () {

    Route::get('/', [AuthController::class, 'index']);
    Route::post('login', [AuthController::class, 'login'])->name('login');
    
    Route::get('forgot-password', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'reset'])->name('password.update');
    
});










