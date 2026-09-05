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
Route::get('exams/{exam}/download-pdf/{type}', [ExamAdminController::class, 'downloadPdf'])->name('exams.download-pdf');
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
// User Management & RBAC Routes
Route::get('users/export', [UserAdminController::class, 'export'])->name('users.export');
Route::post('users/bulk-action', [UserAdminController::class, 'bulkAction'])->name('users.bulk-action');
Route::resource('users', UserAdminController::class);

// Custom User Exam Access actions
Route::post('users/{user}/grant-access', [UserAdminController::class, 'grantAccess'])->name('users.grant-access');
Route::post('users/{user}/revoke-access', [UserAdminController::class, 'revokeAccess'])->name('users.revoke-access');

// Roles & Permissions Management
Route::resource('roles', App\Http\Controllers\Admin\RolePermissionController::class);

// Audit Logs
Route::get('audit-logs', [App\Http\Controllers\Admin\AuditLogAdminController::class, 'index'])->name('audit-logs.index');

// Orders
Route::get('orders/export', [OrderAdminController::class, 'export'])->name('orders.export');
Route::post('orders/bulk-action', [OrderAdminController::class, 'bulkAction'])->name('orders.bulk-action');
Route::get('orders/{order}/invoice', [OrderAdminController::class, 'downloadInvoice'])->name('orders.invoice');
Route::get('orders/{order}/print', [OrderAdminController::class, 'printInvoice'])->name('orders.print');
Route::post('orders/{order}/status', [OrderAdminController::class, 'updateStatus'])->name('orders.status');
Route::post('orders/{order}/notes', [OrderAdminController::class, 'updateNotes'])->name('orders.notes');
Route::post('orders/{order}/refund', [OrderAdminController::class, 'refund'])->name('orders.refund');
Route::post('orders/{order}/resend-confirmation', [OrderAdminController::class, 'resendConfirmation'])->name('orders.resend-confirmation');
Route::resource('orders', OrderAdminController::class)->only(['index', 'show']);

// Subscriptions
Route::resource('subscriptions', SubscriptionAdminController::class)->only(['index', 'show', 'destroy']);

// Coupons
Route::resource('coupons', CouponAdminController::class);

// Blog System
Route::get('blog/dashboard', [App\Http\Controllers\Admin\BlogDashboardController::class, 'index'])->name('blog.dashboard');
Route::post('blog/bulk-action', [BlogAdminController::class, 'bulkAction'])->name('blog.bulk-action');
Route::post('blog/{id}/duplicate', [BlogAdminController::class, 'duplicate'])->name('blog.duplicate');
Route::post('blog/{id}/restore', [BlogAdminController::class, 'restore'])->name('blog.restore');
Route::delete('blog/{id}/force-delete', [BlogAdminController::class, 'forceDelete'])->name('blog.force-delete');
Route::post('blog/quick-category', [BlogAdminController::class, 'quickCategory'])->name('blog.quick-category');
Route::post('blog/quick-tag', [BlogAdminController::class, 'quickTag'])->name('blog.quick-tag');
Route::resource('blog', BlogAdminController::class);

// Blog Categories & Tags
Route::resource('blog-categories', App\Http\Controllers\Admin\BlogCategoryController::class)->except(['show', 'create', 'edit']);
Route::resource('blog-tags', App\Http\Controllers\Admin\BlogTagController::class)->except(['show', 'create', 'edit']);

// Blog Comments Moderation
Route::get('blog-comments', [App\Http\Controllers\Admin\BlogCommentController::class, 'index'])->name('blog-comments.index');
Route::patch('blog-comments/{id}/approve', [App\Http\Controllers\Admin\BlogCommentController::class, 'approve'])->name('blog-comments.approve');
Route::patch('blog-comments/{id}/spam', [App\Http\Controllers\Admin\BlogCommentController::class, 'spam'])->name('blog-comments.spam');
Route::patch('blog-comments/{id}/pending', [App\Http\Controllers\Admin\BlogCommentController::class, 'pending'])->name('blog-comments.pending');
Route::post('blog-comments/{id}/reply', [App\Http\Controllers\Admin\BlogCommentController::class, 'reply'])->name('blog-comments.reply');
Route::post('blog-comments/{id}/restore', [App\Http\Controllers\Admin\BlogCommentController::class, 'restore'])->name('blog-comments.restore');
Route::delete('blog-comments/{id}/force-delete', [App\Http\Controllers\Admin\BlogCommentController::class, 'forceDelete'])->name('blog-comments.force-delete');
Route::post('blog-comments/bulk-action', [App\Http\Controllers\Admin\BlogCommentController::class, 'bulkAction'])->name('blog-comments.bulk-action');
Route::delete('blog-comments/{id}', [App\Http\Controllers\Admin\BlogCommentController::class, 'destroy'])->name('blog-comments.destroy');

// Blog Subscribers
Route::get('blog-subscribers', [App\Http\Controllers\Admin\BlogSubscriberController::class, 'index'])->name('blog-subscribers.index');
Route::post('blog-subscribers/{id}/toggle', [App\Http\Controllers\Admin\BlogSubscriberController::class, 'toggleStatus'])->name('blog-subscribers.toggle');
Route::delete('blog-subscribers/{id}', [App\Http\Controllers\Admin\BlogSubscriberController::class, 'destroy'])->name('blog-subscribers.destroy');
Route::get('blog-subscribers/export/csv', [App\Http\Controllers\Admin\BlogSubscriberController::class, 'exportCsv'])->name('blog-subscribers.export');

// Media Gallery
Route::resource('media', App\Http\Controllers\Admin\MediaController::class)->only(['index', 'store', 'destroy']);

// Settings
Route::get('settings', [SettingsAdminController::class, 'index'])->name('settings.index');
Route::post('settings', [SettingsAdminController::class, 'update'])->name('settings.update');
Route::post('settings/upload-branding', [SettingsAdminController::class, 'uploadBranding'])->name('settings.upload-branding');
Route::post('settings/remove-branding', [SettingsAdminController::class, 'removeBranding'])->name('settings.remove-branding');
Route::post('settings/clear-cache', [SettingsAdminController::class, 'clearCache'])->name('settings.clear-cache');

// Payment Gateway Action Endpoints
Route::post('settings/test-stripe', [SettingsAdminController::class, 'testStripe'])->name('settings.test-stripe');
Route::post('settings/test-paypal', [SettingsAdminController::class, 'testPayPal'])->name('settings.test-paypal');
Route::post('settings/update-stripe-credentials', [SettingsAdminController::class, 'updateStripeCredentials'])->name('settings.update-stripe-credentials');
Route::post('settings/update-paypal-credentials', [SettingsAdminController::class, 'updatePayPalCredentials'])->name('settings.update-paypal-credentials');
Route::get('settings/transactions/{id}', [SettingsAdminController::class, 'getTransactionDetails'])->name('settings.transaction-details');
Route::post('settings/transactions/{id}/refund', [SettingsAdminController::class, 'refundTransaction'])->name('settings.refund-transaction');
Route::post('settings/webhooks/{id}/retry', [SettingsAdminController::class, 'retryWebhook'])->name('settings.retry-webhook');
Route::post('settings/toggle-gateway', [SettingsAdminController::class, 'toggleGateway'])->name('settings.toggle-gateway');

