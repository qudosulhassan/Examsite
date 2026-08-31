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
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogTagController;

// Dashboard
Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

// CRUD Resources
Route::resource('packages', PackageAdminController::class);
Route::resource('vendors', VendorAdminController::class);
Route::resource('certifications', App\Http\Controllers\Admin\CertificationController::class);
Route::get('exams/search-suggestions', [ExamAdminController::class, 'searchSuggestions'])->name('exams.search-suggestions');
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
// Route::resource('blog-categories', BlogCategoryController::class)->except(['show']);
// Route::resource('blog-tags', BlogTagController::class)->except(['show']);

// Blog Comments
Route::get('blog-comments', [App\Http\Controllers\Admin\BlogCommentController::class, 'index'])->name('blog-comments.index');
Route::patch('blog-comments/{comment}/approve', [App\Http\Controllers\Admin\BlogCommentController::class, 'approve'])->name('blog-comments.approve');
Route::patch('blog-comments/{comment}/spam', [App\Http\Controllers\Admin\BlogCommentController::class, 'spam'])->name('blog-comments.spam');
Route::delete('blog-comments/{comment}', [App\Http\Controllers\Admin\BlogCommentController::class, 'destroy'])->name('blog-comments.destroy');

// Blog Subscribers
Route::get('blog-subscribers', [App\Http\Controllers\Admin\BlogSubscriberController::class, 'index'])->name('blog-subscribers.index');

// Media Gallery
Route::resource('media', App\Http\Controllers\Admin\MediaController::class)->only(['index', 'store', 'destroy']);

// Settings
Route::get('settings', [SettingsAdminController::class, 'index'])->name('settings.index');
Route::post('settings', [SettingsAdminController::class, 'update'])->name('settings.update');
