<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\VendorAdminController;
use App\Http\Controllers\Admin\ExamAdminController;
use App\Http\Controllers\Admin\QuestionAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\SubscriptionAdminController;
use App\Http\Controllers\Admin\CouponAdminController;
use App\Http\Controllers\Admin\BlogAdminController;
use App\Http\Controllers\Admin\SettingsAdminController;
use App\Http\Controllers\Admin\PackageAdminController;

// Dashboard
Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

// CRUD Resources
Route::resource('packages', PackageAdminController::class);
Route::resource('vendors', VendorAdminController::class);
Route::resource('exams', ExamAdminController::class);
Route::resource('questions', QuestionAdminController::class);
Route::resource('users', UserAdminController::class)->only(['index', 'show', 'edit', 'update']);

// Custom User actions
Route::post('users/{user}/grant-access', [UserAdminController::class, 'grantAccess'])->name('users.grant-access');
Route::post('users/{user}/revoke-access', [UserAdminController::class, 'revokeAccess'])->name('users.revoke-access');

// Orders
Route::resource('orders', OrderAdminController::class)->only(['index', 'show']);
Route::post('orders/{order}/refund', [OrderAdminController::class, 'refund'])->name('orders.refund');

// Subscriptions
Route::resource('subscriptions', SubscriptionAdminController::class)->only(['index', 'show', 'destroy']);

// Coupons
Route::resource('coupons', CouponAdminController::class);

// Blog
Route::resource('blog', BlogAdminController::class);

// Settings
Route::get('settings', [SettingsAdminController::class, 'index'])->name('settings.index');
Route::post('settings', [SettingsAdminController::class, 'update'])->name('settings.update');
