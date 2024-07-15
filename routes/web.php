<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('user', [UserController::class, 'user_create']);
Route::get('user/create', [UserController::class, 'create']);
Route::post('user/store', [UserController::class, 'store'])->name('user.store');
Route::post('users/store', [UserController::class, 'UserStore'])->name('users.store');
Route::post('users/update', [UserController::class, 'update'])->name('users.update');
Route::get('users/data', [UserController::class, 'getData'])->name('user.data');

Route::get('role/data', [RoleController::class, 'getRole'])->name('get.role');



Route::get('dash-home', [DashboardController::class, 'index']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');



Route::middleware(['guest'])->group(function () {

    Route::get('/', [AuthController::class, 'index']);
    Route::post('login', [AuthController::class, 'login'])->name('login');
    
    Route::get('forgot-password', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'reset'])->name('password.update');
    
});










