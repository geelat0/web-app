<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SuccesIndicatorController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::middleware(['auth_check'])->group(function () {
    Route::middleware(['2fa'])->group(function () {

        Route::middleware(['superadmin'])->group(function () { 

            Route::get('/login_in', [LogController::class, 'login_in']);
            Route::get('/list', [LogController::class, 'list'])->name('list');
    
            Route::get('user', [UserController::class, 'user_create']);
            
            Route::post('user/store', [UserController::class, 'store'])->name('user.store');
            Route::post('users/store', [UserController::class, 'UserStore'])->name('users.store');
            Route::post('users/update', [UserController::class, 'update'])->name('users.update');
            Route::get('users/list', [UserController::class, 'list'])->name('user.list');
            Route::post('users/destroy', [UserController::class, 'destroy'])->name('users.destroy');
            Route::post('temp-password', [UserController::class, 'temp_password'])->name('users.temp-password');
            Route::post('proxy', [UserController::class, 'proxy'])->name('users.gen-proxy');
            Route::post('users/change-status', [UserController::class, 'changeStatus'])->name('users.change-status');
    
            Route::get('roles', [RoleController::class, 'roles']);
            Route::get('role/list', [RoleController::class, 'list'])->name('role.list');
            Route::get('role/data', [RoleController::class, 'getRole'])->name('get.role');
            Route::post('role/store', [RoleController::class, 'store'])->name('role.store');
            Route::post('role/update', [RoleController::class, 'update'])->name('role.update');
            Route::post('/role/destroy', [RoleController::class, 'destroy'])->name('role.destroy');

            Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
            Route::post('/logs/clear', [LogController::class, 'clear'])->name('logs.clear');

            
        });

        Route::get('dash-home', [DashboardController::class, 'index'])->name('dash-home');
        Route::get('dashboard/filter', [DashboardController::class, 'filter'])->name('dashboard.filter');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('profile', [ProfileController::class, 'index']);
        Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('password.change');
        Route::get('/two-factor', [ProfileController::class, 'two_factor'])->name('two_factor');
        Route::post('/twofaEnable', [ProfileController::class, 'twofaEnable'])->name('twofaEnable');
        Route::post('/twofaDisabled', [ProfileController::class, 'twofaDisabled'])->name('twofaDisabled');

        Route::get('change-password', [AuthController::class, 'ChangePassForm'])->name('change-password');


        Route::get('organizational/outcome', [OrganizationController::class, 'index']);
        Route::get('organizational/outcome/list', [OrganizationController::class, 'list'])->name('org.list');
        Route::post('organizational/outcome/store', [OrganizationController::class, 'store'])->name('org.store');
        Route::post('organizational/outcome/update', [OrganizationController::class, 'update'])->name('org.update');
        Route::post('organizational/outcome/destroy', [OrganizationController::class, 'destroy'])->name('org.destroy');

        Route::get('success/indicator', [SuccesIndicatorController::class, 'index']);

    });

    Route::get('auth/otp', [AuthController::class, 'OTP'])->name('auth.otp');
    Route::post('auth/otp/check', [AuthController::class, 'check'])->name('auth.otp.check');
       
});



Route::middleware(['guest'])->group(function () {

    Route::get('/', [AuthController::class, 'index']);
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::get('register', [UserController::class, 'create']);
    
    Route::get('forgot-password', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'reset'])->name('password.update');

});















