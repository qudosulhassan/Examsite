# Walkthrough - Exam Topics Base.com Platform Development

This document details the work accomplished for the setup, database architecture, routing layer, test engine, admin dashboard, cart, payment gateways, webhook processors, queue mailers, and deployment prep for the Exam Topics Base IT Certification Prep Platform.

---

## 1. Scaffold & Server Setup
- **PHP 8.2 & Composer:** Configured a portable PHP 8.2 runtime at `C:\Users\LENOVO\php82` with critical extensions (`curl`, `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo_mysql`, `pdo_sqlite`, `sqlite3`, `zip`).
- **Laravel 11 Scaffolding:** Scaffolded the new Laravel 11 project in the `c:\ExamSite\examtopicsbase` workspace.
- **Dependency Installation:** Installed packages: `laravel/breeze`, `laravel/socialite`, `stripe/stripe-php`, `srmklive/paypal`, `aws/aws-sdk-php`, `barryvdh/laravel-dompdf`, `spatie/laravel-permission`.
- **Storage Disk Configuration:** Configured the `r2` disk driver in `config/filesystems.php` for Cloudflare R2 bucket connection.
- **Local Settings:** Set up `.env` with configurations for database connection, local mail traps, Google OAuth, Stripe sandbox, and PayPal.

---

## 2. Database Schema, Models & Seeders
- **Migrations:** Created migration files for 17 tables (users, vendors, exams, questions, orders, order_items, subscriptions, user_exams, demo_requests, test_attempts, test_answers, bookmarks, coupons, reviews, blog_posts, settings, activity_log).
- **Models:** Defined all 17 Eloquent models with model relationship parameters, casts, logging hooks, and JSON array decoders.
- **Seeders:** Configured realistic mock data seeders: `VendorSeeder`, `ExamSeeder`, `QuestionSeeder`, `AdminSeeder`, `PlanSeeder`, and `CouponSeeder`.
- **Database Verification:** Verified database setups and seeder tasks compile successfully on SQLite.

---

## 3. Layouts, Routing & Middleware
- **Layout Blade Views:** Created public layout (`layouts/public.blade.php`), student portal layout (`layouts/app.blade.php`), and administrator panel layout (`layouts/admin.blade.php`) using Google Sora and Inter typography, navy backdrops, and cyan/orange highlights.
- **Middleware:** Implemented `AdminMiddleware` protecting administrative routes under the `/admin` prefix.
- **Exemptions:** Excluded Stripe and PayPal webhooks from CSRF checks in `bootstrap/app.php` for out-of-band payment confirmations.
- **Route Compilation:** Registered public paths, Google OAuth, shopping cart endpoints, student dashboard routes, simulator controllers, and administrative CRUD paths, mapping 103 routes.

---

## 4. Controllers & Pages (Marketing & Portal)
- **Public Marketing Handlers:** Developed views and controllers for Home page (with dynamic search), Vendors listing/details, Exam details (collapsible topics, purchase boxes, sample questions), Pricing page, Free Demo request capture, Blog grid/details, FAQ, About, and Contact forms.
- **Dashboard Portal Handlers:** Developed Student dashboard overview stats, My Exams download manager (generating signed R2 temporary URLs with max 3 download attempts limiters), Orders log, Invoice downloads (DomPDF generation), and Account settings.
- **Test Engine Simulator:** Built timed practice engine supporting setup lobby config, timed sessions with Alpine.js answer autosaving AJAX controllers, grid navigation, flagged question state, and detailed score results cards.
- **Email Notifications:** Configured a queued `SendDemoPdfEmail` job sending signed sample download links to guest users.
- **Admin Panel CRUD Controllers:** Scaffolded administrative CRUD controllers for Vendors, Exams, Questions, Users, Orders, Subscriptions, Coupons, Blog, and Settings.

---

## 5. Cart, Checkout & Payment Gateways (Phase 7)
- **Cart Management:** Created `CartController` supporting session-based additions, removals, coupon application/validation, and type checks (Printable PDF study guides, single simulator engine access, and monthly/annual subscription plans).
- **Checkout Processing:** Developed `CheckoutController` to process checkouts:
  - Generates Stripe client secrets dynamically for Elements credit card input fields.
  - Initializes PayPal sandbox orders and vault subscriptions via AJAX calls.
  - Automatically handles free checkouts (100% off coupon code bypass) and redirects to success endpoints.
- **Payment Wrappers:** Developed `StripeService` and `PayPalService` client wrappers with robust mock fallbacks (allowing simulated checkout processing if API keys are left as placeholders).
- **Webhook Processors:** Developed `StripeWebhookController` and `PayPalWebhookController` handling recurring subscription payments, successful captures, failed charges, and automated membership activations or cancellations in the database.

---

## 6. Email Mailers & Deployment Readiness (Phase 8)
- **Mailable Notification Classes:** Implemented 7 queued mail classes and responsive HTML Blade layouts inside `app/Mail/` and `resources/views/emails/`:
  - `WelcomeMail`: Sent on user signup and Google OAuth callback registration.
  - `OrderConfirmationMail`: Sent with purchase item summaries and access links.
  - `SubscriptionStartedMail`: Dispatched on successful Stripe/PayPal subscription setup.
  - `SubscriptionRenewalReminderMail`: Dispatched on renewal success or warning.
  - `PaymentFailedMail`: Sent to alert users when a recurring billing charge declines.
  - `SubscriptionCancelledMail`: Dispatched when a subscription is set to terminate.
  - `ExamUpdatedMail`: Sent to alert guide purchasers when new questions are uploaded.
- **SEO & Indexation:** Configured dynamic XML sitemap generation at `/sitemap.xml` listing all pages/vendors/exams, and set up public `robots.txt` crawler constraints.
- **Production Settings:** Created a template `.env.production` database configuration customized for Hostinger hosting setups.
- **Deployment Documentation:** Overwrote `README.md` with complete hPanel SSH configurations, composer optimizations, directory symlinks, and cron scheduler setups.
- **Verification:** Fixed Breeze baseline tests (refreshing database and mapping profile URL prefixes). Ran `php artisan test` successfully passing all 25 tests.

---

## 7. Admin Panel View Resolution & CRUD View Completion
- **Admin Dashboard Error Fixed:** Resolved the stale process issue causing `View [admin.dashboard] not found` error by performing cache clearing and restarting the background `php artisan serve` process correctly within the workspace.
- **Admin Blog Views:** Created the blog management views under `resources/views/admin/blog/` (`index.blade.php`, `create.blade.php`, `edit.blade.php`) mapped to the `BlogAdminController`, incorporating the Quill Rich Text Editor.
- **Admin Settings View:** Created the global settings management view at `resources/views/admin/settings.blade.php` mapped to `SettingsAdminController`, enabling easy UI configuration of global variables like site name, site tagline, support email, maintenance mode, home promotional banner text/link/status, and the raw subscription plans JSON array.
- **Route Naming Namespace Fix:** Fixed the `Symfony\Component\Routing\Exception\RouteNotFoundException` (`Route [admin.vendors.create] not defined`) by defining `->name('admin.')` on the admin route group in `bootstrap/app.php`. Added a `/dashboard` fallback redirect route named `dashboard` in `routes/web.php` to ensure standard user logins redirect correctly to the student dashboard.
- **Vendor Category Option Removal:** Removed the `category` attribute from the Vendor create/edit views, the administrator vendor table, validation rules, and the public facing filter grid for simplified administration.
- **Exam PDF File Uploads:** Integrated multipart form data and PDF file inputs into the create and edit views for Exams. Handled file uploads in `ExamAdminController` by mapping them to `demos/` and `full/` folders on Cloudflare R2 bucket with local storage mirror fallbacks.
- **Practice Engine Portal Visibility:** Exposed the Timed Simulator/Practice Test Engine to the public facing site by placing a navigation link named "Practice Engine" with a vibrant "NEW" badge in the desktop header menu, mobile drawer menu, and footer layout.

---

## 8. Public Test Engine Portal & Checkout Constraint Fixes
- **Payment Status Constraint Violation Fix:** Fixed the SQLite CHECK constraint violation exception during purchase flow by updating the assigned order status from `'completed'` to `'paid'` in `CheckoutController.php` and `StripeWebhookController.php`, satisfying the enum values defined in the database migrations.
- **Public Test Engine Page:** Created a dedicated public informational page at `/test-engine` highlighting practice modes, adaptive analytics, and certification question counts.
- **Interactive Alpine.js Simulator:** Designed and embedded a fully functional inline mock exam console directly on the public Test Engine page. Guests can select answers, flag questions, view timers, submit for instant grading, and read detailed concept explanations without logging in.
- **Global Navigation Routing:** Updated the public layouts header, mobile menu, and footer links to point directly to the public `/test-engine` page rather than the restricted user dashboard directory, ensuring an optimal guest experience.
- **Invisible Text CSS Resolution:** Resolved a bug causing question text, options, customer reviews, and exam topics to be rendered as white-on-white (invisible) inside `bg-white` cards. Added the missing `.text-navy { color: #0A1628; }` class rule in `public.blade.php`, which properly overrides parent `text-white` configurations.
- **Interactive Exam Page Sample Questions:** Upgraded the 3 sample questions displayed on each exam details page (e.g. CCNA 200-301 details page) to be fully interactive Alpine.js components. Guests can now select options, click a "Check Answer" button, view instant grading (correct/incorrect icons and border overlays), and read explanations directly on the marketing pages without needing to purchase or log in.
- **Test Automation:** Added feature tests verifying that guest users can load `/test-engine` successfully. Verified that all 26 feature and unit tests compile and pass.

```bash
# Output from final test verification:
#   Tests:    26 passed (63 assertions)
#   Duration: 3.87s
```






