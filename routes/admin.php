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

use App\Http\Controllers\Admin\QuestionImportController;

// Questions Submenu & Smart Import Routes
Route::get('questions/import/sample', [QuestionAdminController::class, 'downloadSample'])->name('questions.import-sample');
Route::get('questions/import/history', [QuestionImportController::class, 'history'])->name('questions.import-history');
Route::get('questions/import/pdf', [QuestionImportController::class, 'showPdfImportForm'])->name('questions.import-pdf-form');
Route::post('questions/import/pdf', [QuestionImportController::class, 'processPdfUpload'])->name('questions.import-pdf-upload');
Route::get('questions/import', [QuestionImportController::class, 'showImportForm'])->name('questions.import-form');
Route::post('questions/import/upload', [QuestionImportController::class, 'processUpload'])->name('questions.import-upload');
Route::get('questions/import/batch/{uuid}', [QuestionImportController::class, 'showReview'])->name('questions.import-review');
Route::get('questions/import/batch/{uuid}/candidate/{id}/preview', [QuestionImportController::class, 'previewCandidate'])->name('questions.import-candidate-preview');
Route::get('questions/import/batch/{uuid}/candidate/{id}/review', [QuestionImportController::class, 'reviewCandidate'])->name('questions.import-candidate-review');
Route::get('questions/import/batch/{uuid}/candidate/{id}/edit', [QuestionImportController::class, 'editCandidate'])->name('questions.import-candidate-edit');

Route::get('questions/import/item/{id}', [QuestionImportController::class, 'getItem'])->name('questions.import-get-item');
Route::get('questions/import/item/{id}/preview', [QuestionImportController::class, 'previewItem'])->name('questions.import-item-preview');
Route::get('questions/import/item/{id}/review', [QuestionImportController::class, 'reviewItem'])->name('questions.import-item-review');
Route::get('questions/import/item/{id}/edit', [QuestionImportController::class, 'editItem'])->name('questions.import-item-edit');
Route::put('questions/import/item/{id}', [QuestionImportController::class, 'updateItem'])->name('questions.import-update-item');
Route::post('questions/import/item/{id}/status', [QuestionImportController::class, 'updateItemReviewStatus'])->name('questions.import-item-status');
Route::post('questions/import/batch/{uuid}/confirm', [QuestionImportController::class, 'importSelected'])->name('questions.import-confirm-selected');
Route::get('questions/import/batch/{uuid}/error-report', [QuestionImportController::class, 'downloadErrorReport'])->name('questions.import-error-report');
Route::delete('questions/import/batch/{uuid}', [QuestionImportController::class, 'cancelBatch'])->name('questions.import-cancel-batch');

Route::get('questions/{question}/preview', [QuestionAdminController::class, 'preview'])->name('questions.preview');
Route::post('questions/bulk-action', [QuestionAdminController::class, 'bulkAction'])->name('questions.bulk-action');

// Legacy import AJAX handlers preserved for backward compatibility
Route::post('questions/import/validate', [QuestionAdminController::class, 'validateImport'])->name('questions.import-validate');
Route::post('questions/import/confirm', [QuestionAdminController::class, 'confirmImport'])->name('questions.import-confirm');
Route::post('questions/import', [QuestionAdminController::class, 'importJson'])->name('questions.import');

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
