<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntriesController;
use App\Http\Controllers\IndicatorController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OutcomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SuccesIndicatorController;
use App\Http\Controllers\TestController;
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
            Route::get('getDivision', [UserController::class, 'getDivision'])->name('getDivision');


            Route::get('roles', [RoleController::class, 'roles']);
            Route::get('role/list', [RoleController::class, 'list'])->name('role.list');
            Route::get('role/data', [RoleController::class, 'getRole'])->name('get.role');
            Route::post('role/store', [RoleController::class, 'store'])->name('role.store');
            Route::post('role/update', [RoleController::class, 'update'])->name('role.update');
            Route::post('/role/destroy', [RoleController::class, 'destroy'])->name('role.destroy');

            Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
            Route::post('/logs_clear', [LogController::class, 'clear'])->name('logs.clear');


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

        Route::get('outcome', [OutcomeController::class, 'index'])->name('organization_outcome');
        Route::get('organiztional/outcome/list', [OutcomeController::class, 'list'])->name('org.list');
        Route::post('organizational/outcome/store', [OutcomeController::class, 'store'])->name('org.store');
        Route::post('organizational/outcome/update', [OutcomeController::class, 'update'])->name('org.update');
        Route::post('organizational/outcome/destroy', [OutcomeController::class, 'destroy'])->name('org.destroy');
        Route::get('organizational/outcome/getOrg', [OutcomeController::class, 'getOrg'])->name('org.getOrg');

        Route::get('indicator', [IndicatorController::class, 'index'])->name('indicator');
        Route::get('indicator/list', [IndicatorController::class, 'list'])->name('indicator.list');
        Route::get('indicator_create', [IndicatorController::class, 'create'])->name('indicator.create');
        Route::get('indicator_edit', [IndicatorController::class, 'edit'])->name('indicator.edit');
        Route::get('indicator/getDivision', [IndicatorController::class, 'getDivision'])->name('indicator.getDivision');
        Route::post('indicator/store', [IndicatorController::class, 'store'])->name('indicator.store');
        Route::match(['post', 'put'], 'indicator/update', [IndicatorController::class, 'update'])->name('indicator.update');
        Route::match(['post', 'put'], 'indicator/update_2', [IndicatorController::class, 'update_nonSuperAdmin'])->name('indicator.update_nonSuperAdmin');
        Route::match(['post', 'put'], 'indicator/update_v2', [IndicatorController::class, 'update_nonSuperAdminV2'])->name('indicator.update_nonSuperAdminV2');
        Route::get('indicator_view', [IndicatorController::class, 'view'])->name('indicator.view');
        Route::post('indicator/destroy', [IndicatorController::class, 'destroy'])->name('indicator.destroy');
        Route::get('getIndicator', [IndicatorController::class, 'getIndicator'])->name('getIndicator');

        Route::get('getMeasureDetails', [IndicatorController::class, 'getMeasureDetails'])->name('indicator.getMeasureDetails');


        Route::get('entries', [EntriesController::class, 'index'])->name('entries');
        Route::get('entries_create', [EntriesController::class, 'create'])->name('create');
        Route::get('entries_view', [EntriesController::class, 'view'])->name('view');
        Route::get('entries_edit', [EntriesController::class, 'edit'])->name('edit');
        Route::post('entries/store', [EntriesController::class, 'store'])->name('entries.store');
        Route::post('entries/update', [EntriesController::class, 'update'])->name('entries.update');
        Route::post('entries/destroy', [EntriesController::class, 'destroy'])->name('entries.destroy');
        Route::get('entries/list', [EntriesController::class, 'list'])->name('entries.list');
        Route::get('entries/completed_list', [EntriesController::class, 'completed_list'])->name('entries.completed_list');
        Route::get('entries/getIndicator', [EntriesController::class, 'getIndicator'])->name('entries.getIndicator');


        Route::get('generate', [ReportController::class, 'index'])->name('generate');
        Route::post('/generate-pdf', [ReportController::class, 'generatePDF'])->name('generate.pdf');
        Route::get('pdf', [ReportController::class, 'pdf'])->name('show.pdf');


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


Route::get('/test', [TestController::class, 'index'])->name('test');
Route::get('/test_outcome', [TestController::class, 'test_outcome'])->name('test_outcome');















