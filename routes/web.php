<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\VendorController;
use App\Http\Controllers\Public\ExamController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\BlogCommentController;
use App\Http\Controllers\Public\BlogSubscriberController;
use App\Http\Controllers\Public\DemoController;
use App\Http\Controllers\Public\DemoTestEngineController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\MyExamsController;
use App\Http\Controllers\Dashboard\TestEngineController;
use App\Http\Controllers\Dashboard\OrdersController;
use App\Http\Controllers\Payment\CartController;
use App\Http\Controllers\Payment\CheckoutController;
use App\Http\Controllers\Webhook\StripeWebhookController;

use App\Http\Controllers\Webhook\PayPalWebhookController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
Route::get('/vendors/{slug}', [App\Http\Controllers\Public\VendorController::class, 'show'])->name('vendors.show');
Route::get('/certifications', [App\Http\Controllers\Public\CertificationController::class, 'index'])->name('certifications.index');
Route::get('/certifications/{slug}', [App\Http\Controllers\Public\CertificationController::class, 'show'])->name('certifications.show');
Route::get('/exams/{slug}', [App\Http\Controllers\Public\ExamController::class, 'show'])->name('exams.show');

Route::get('/free-demo', [DemoController::class, 'index'])->name('free-demo.index');
Route::post('/free-demo', [DemoController::class, 'request'])->name('free-demo.request');

Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/api/search', [SearchController::class, 'liveSearch']);
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// --- Blog ---
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/rss.xml', [BlogController::class, 'rss'])->name('rss');
    Route::get('/search', [BlogController::class, 'search'])->name('search');
    Route::get('/category/{slug}', [BlogController::class, 'category'])->name('category');
    Route::get('/tag/{slug}', [BlogController::class, 'tag'])->name('tag');
    Route::get('/author/{slug}', [BlogController::class, 'author'])->name('author');
    
    // Comments & Subscribers
    Route::post('/comments', [BlogCommentController::class, 'store'])->name('comments.store');
    Route::post('/subscribe', [BlogSubscriberController::class, 'subscribe'])->name('subscribe');
    Route::get('/unsubscribe', [BlogSubscriberController::class, 'unsubscribe'])->name('unsubscribe');

    Route::get('/{slug}', [BlogController::class, 'show'])->name('show');
});

Route::get('/faq', function () {
    return view('pages.faq');
})->name('faq');

Route::get('/pricing', function () {
    return redirect()->route('vendors.index');
})->name('pricing');

Route::get('/test-engine', [HomeController::class, 'testEngine'])->name('public.test-engine');

// Public Demo Test Engine Flow
Route::prefix('demo-test-engine')->name('public.demo-test-engine.')->group(function () {
    Route::get('/{exam}', [DemoTestEngineController::class, 'lobby'])->name('lobby');
    Route::post('/{exam}/start', [DemoTestEngineController::class, 'startAttempt'])->name('start');
    Route::get('/session/{attempt}', [DemoTestEngineController::class, 'session'])->name('session');
    Route::post('/session/{attempt}/submit', [DemoTestEngineController::class, 'submitAttempt'])->name('submit');
    Route::get('/results/{attempt}', [DemoTestEngineController::class, 'results'])->name('results');
    Route::post('/session/answer', [DemoTestEngineController::class, 'saveAnswerAjax'])->name('answer');
    Route::post('/session/flag', [DemoTestEngineController::class, 'toggleFlagAjax'])->name('flag');
});

Route::get('/about', function () {
    return view('pages.about');
})->name('about');

Route::get('/contact', function () {
    return view('pages.contact');
})->name('contact');

Route::post('/contact', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'subject' => 'required|string|max:255',
        'message' => 'required|string',
    ]);
    return back()->with('success', 'Thank you! Your message has been sent to our support queue. We will contact you shortly.');
});

/*
|--------------------------------------------------------------------------
| Google OAuth Routes
|--------------------------------------------------------------------------
*/
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');

/*
|--------------------------------------------------------------------------
| Cart & Checkout Routes (Session-based, checkout requires auth)
|--------------------------------------------------------------------------
*/
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/add-package', [CartController::class, 'addPackage'])->name('cart.add-package');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');
Route::post('/cart/coupon/remove', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/free', [CheckoutController::class, 'checkoutFree'])->name('checkout.free');
    Route::post('/checkout/paypal/create-order', [CheckoutController::class, 'paypalCreateOrder'])->name('checkout.paypal.create-order');
    Route::post('/checkout/paypal/capture-order', [CheckoutController::class, 'paypalCaptureOrder'])->name('checkout.paypal.capture-order');
    Route::post('/checkout/paypal/create-subscription', [CheckoutController::class, 'paypalCreateSubscription'])->name('checkout.paypal.create-subscription');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
});

// Webhook Routes (CSRF excluded inside bootstrap/app.php)
Route::post('/webhook/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');
Route::post('/webhook/paypal', [PayPalWebhookController::class, 'handle'])->name('webhooks.paypal');


/*
|--------------------------------------------------------------------------
| User Portal Dashboard Routes (Auth Required)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard.')->group(function () {
    // Overview
    Route::get('/', [DashboardController::class, 'index'])->name('index');

    // My purchased PDF guides
    Route::get('/my-exams', [MyExamsController::class, 'index'])->name('my-exams');
    Route::get('/my-exams/download/{id}', [MyExamsController::class, 'download'])->name('my-exams.download');

    // Test Engine
    Route::get('/test-engine', [TestEngineController::class, 'index'])->name('test-engine');
    Route::get('/test-engine/{exam}', [TestEngineController::class, 'lobby'])->name('test-engine.lobby');
    Route::post('/test-engine/{exam}/start', [TestEngineController::class, 'startAttempt'])->name('test-engine.start');
    Route::get('/test-engine/session/{attempt}', [TestEngineController::class, 'session'])->name('test-engine.session');
    Route::post('/test-engine/session/{attempt}/submit', [TestEngineController::class, 'submitAttempt'])->name('test-engine.submit');
    Route::get('/test-engine/results/{attempt}', [TestEngineController::class, 'results'])->name('test-engine.results');
    Route::post('/test-engine/session/answer', [TestEngineController::class, 'saveAnswerAjax'])->name('test-engine.answer');
    Route::post('/test-engine/session/flag', [TestEngineController::class, 'toggleFlagAjax'])->name('test-engine.flag');

    // Billing & Invoice download
    Route::get('/orders', [OrdersController::class, 'index'])->name('orders');
    Route::get('/orders/invoice/{id}', [OrdersController::class, 'invoice'])->name('orders.invoice');
});

Route::get('/dashboard/home', function () {
    return redirect()->route('dashboard.index');
})->name('dashboard')->middleware(['auth', 'verified']);

// Profile Settings (outside name group to match Breeze defaults but prefixed under dashboard)
Route::middleware(['auth', 'verified'])->prefix('dashboard/profile')->group(function () {
    Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
