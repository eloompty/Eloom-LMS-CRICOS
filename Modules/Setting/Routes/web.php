<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Modules\Setting\Http\Controllers\AssignmentController;
use Modules\Setting\Http\Controllers\AttendanceController;
use Modules\Setting\Http\Controllers\AvetmissBackpController;
use Modules\Setting\Http\Controllers\EmailController;
use Modules\Setting\Http\Controllers\FeeController;
use Modules\Setting\Http\Controllers\ImportController;
use Modules\Setting\Http\Controllers\NatController;
use Modules\Setting\Http\Controllers\OfferController;
use Modules\Setting\Http\Controllers\SettingController;
use Modules\Setting\Http\Controllers\StripeController;
use Modules\Setting\Http\Controllers\StudentDeleteController;

Route::group([
    'prefix' => 'admin'
], function () {
    Route::group([
        'prefix' => 'setting'
    ], function () {
        Route::get('/', [SettingController::class, 'index'])->name('admin.setting.index');
        Route::get('dashboard', [SettingController::class, 'dashboardSetting'])->name('admin.setting.dashboard.index');
        Route::get('onlineclass', [SettingController::class, 'onlineclassSetting'])->name('admin.setting.onlineclass.index');
        Route::get('notification', [SettingController::class, 'notificationSetting'])->name('admin.setting.notification.index');
        Route::get('zoho', [SettingController::class, 'zohoSetting'])->name('admin.setting.zoho.index');
        Route::post('update', [SettingController::class, 'update'])->name('admin.setting.update');
        Route::get('menu', [SettingController::class, 'menu'])->name('admin.setting.menu');
        Route::get('theme', [SettingController::class, 'theme'])->name('admin.setting.theme');
        Route::get('theme/{theme}', [SettingController::class, 'updateTheme'])->name('admin.setting.updateTheme');
    });
    Route::group([
        'prefix' => 'setting/offer'
    ], function () {
        Route::get('/', [OfferController::class, 'index'])->name('admin.setting.offer.index');
        Route::post('update', [OfferController::class, 'update'])->name('admin.setting.offer.update');
    });
    Route::group([
        'prefix' => 'export'
    ], function () {
        Route::get('/', [NatController::class, 'index'])->name('admin.setting.export.index');
        Route::post('export', [NatController::class, 'export'])->name('admin.setting.export.type');
    });
    Route::group([
        'prefix' => 'setting/assignment'
    ], function () {
        Route::get('/', [AssignmentController::class, 'index'])->name('admin.setting.assignment.index');
        Route::post('update', [AssignmentController::class, 'update'])->name('admin.setting.assignment.update');
    });
    Route::group([
        'prefix' => 'setting/fee'
    ], function () {
        Route::get('/', [FeeController::class, 'index'])->name('admin.setting.fee.index');
        Route::post('update', [FeeController::class, 'update'])->name('admin.setting.fee.update');
    });
    Route::group([
        'prefix' => 'setting/stripe'
    ], function () {
        Route::get('/', [StripeController::class, 'index'])->name('admin.setting.stripe.index');
        Route::post('update', [StripeController::class, 'update'])->name('admin.setting.stripe.update');
    });
    Route::group([
        'prefix' => 'setting/attendance'
    ], function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('admin.setting.attendance.index');
        Route::post('update', [AttendanceController::class, 'update'])->name('admin.setting.attendance.update');
    });
    Route::group([
        'prefix' => 'import'
    ], function () {
        Route::get('/', [ImportController::class, 'index'])->name('admin.setting.import.index');
        Route::group([
            'prefix' => 'rto'
        ], function () {
            Route::post('deliverysite', [ImportController::class, 'rtoImportDeliverySite'])->name('admin.setting.rto.deliverysite');
            Route::post('course', [ImportController::class, 'rtoImportCourse'])->name('admin.setting.rto.course');
            Route::post('intake', [ImportController::class, 'rtoImportIntake'])->name('admin.setting.rto.intake');
            Route::post('student', [ImportController::class, 'rtoImportStudent'])->name('admin.setting.rto.student');
        });
    });
    Route::group([
        'prefix' => 'setting/email'
    ], function () {
        Route::get('/', [EmailController::class, 'index'])->name('admin.setting.email.index');
        Route::post('update', [EmailController::class, 'update'])->name('admin.setting.email.update');
    });
    Route::group([
        'prefix' => 'avetmiss_backup'
    ], function () {
        Route::get('/', [AvetmissBackpController::class, 'index'])->name('admin.setting.avetmiss.index');
        Route::get('create', [AvetmissBackpController::class, 'create'])->name('admin.setting.avetmiss.create');
        Route::post('store', [AvetmissBackpController::class, 'store'])->name('admin.setting.avetmiss.store');
        Route::get('edit/{id}', [AvetmissBackpController::class, 'edit'])->name('admin.setting.avetmiss.edit');
        Route::post('update/{id}', [AvetmissBackpController::class, 'update'])->name('admin.setting.avetmiss.update');
        Route::get('delete/{id}', [AvetmissBackpController::class, 'destroy'])->name('admin.setting.avetmiss.delete');
    });

});

Route::get('admin/setting/student/delete', [StudentDeleteController::class, 'index']);
