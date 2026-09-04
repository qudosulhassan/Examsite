# Exam Topics Base - IT Certification Prep Platform

Exam Topics Base is a premium IT Certification Exam Preparation Platform built on Laravel 11, Tailwind CSS, Alpine.js, and MySQL. It integrates Cloudflare R2 object storage for secure PDF guide downloads, and features an interactive browser-based timed test simulator.

---

## Key Features
- **Timed Test Engine Simulator:** Supporting Practice, Exam, and Review modes with autosave controls.
- **Secure PDF Downloads:** Cloudflare R2 private storage connection with expiry links and download limiters (3 attempts max).
- **Payment Gateways:** Integrated Stripe Elements card fields and PayPal SDK sandbox buttons.
- **Sitemap & SEO:** Live dynamic XML sitemap generation at `/sitemap.xml` and standard `robots.txt` configuration.
- **Dashboard Logs:** Complete User dashboard with orders history, active subscription lifecycle metrics, and student logs.

---

## 1. Local Development Setup

### Prerequisites
- PHP 8.2+ (with curl, mbstring, openssl, pdo_mysql, pdo_sqlite, sqlite3, zip extensions enabled)
- Composer
- Node.js & npm

### Installation Steps
1. Clone the project or navigate to the directory:
   ```bash
   cd c:\ExamSite\examtopicsbase
   ```
2. Copy the environment configuration file:
   ```bash
   copy .env.example .env
   ```
3. Install PHP dependencies:
   ```bash
   composer install
   ```
4. Generate the application encryption key:
   ```bash
   php artisan key:generate
   ```
5. Install and build front-end assets:
   ```bash
   npm install
   npm run build
   ```
6. Run database migrations and seed mock vendors, exams, questions, and plans:
   ```bash
   php artisan migrate:fresh --seed
   ```
7. Start the local development servers:
   ```bash
   php artisan serve
   ```
   *The application will be accessible at [http://localhost:8000](http://localhost:8000).*

---

## 2. Sandbox Payment Simulator (Local Testing)
Since Stripe and PayPal credentials default to placeholder values locally, the platform includes a **Sandbox Payment Simulator** so developers and reviewers can test the entire checkout checkout flow seamlessly:
- **Card (Stripe Elements):** If `STRIPE_KEY` is a placeholder, a "Simulate Mock Stripe Payment" button is displayed on checkout. Clicking it simulates Stripe's PaymentIntent approval and redirects to the success page, creating order logs instantly.
- **PayPal Sandbox:** If `PAYPAL_CLIENT_ID` is a placeholder, a "Simulate PayPal Checkout" button is displayed. Clicking it completes mock PayPal captures, grants UserExam privileges, and initiates database subscriptions.
- **Free Purchases:** A 100% off coupon code allows immediate free checkout bypass.

---

## 3. Hostinger Production Deployment Guide
Follow these steps to deploy Exam Topics Base to a Hostinger Business Plus plan using hPanel and SSH.

### Step 3.1: Connect via SSH & Clone Repository
1. Log in to your **Hostinger hPanel**.
2. Go to **Advanced > SSH Access** and enable SSH. Copy the connection command.
3. Open terminal and connect via SSH:
   ```bash
   ssh -p [Port] [Username]@[IP]
   ```
4. Navigate to your domain's root folder (usually `domains/examtopicsbase.com` or `public_html`):
   ```bash
   cd domains/examtopicsbase.com
   ```
5. Clone your git repository or upload the files.

### Step 3.2: Configure Environment Variables
1. Rename the upload template `.env.production` to `.env` in the project root:
   ```bash
   mv .env.production .env
   ```
2. Open `.env` and fill in the production values:
   - **Database:** Hostinger MySQL Database, Database User, and password created in hPanel under **Databases > MySQL Databases**.
   - **Stripe & PayPal:** Live API credentials.
   - **Cloudflare R2:** Bucket credentials.
3. Run key generation:
   ```bash
   php artisan key:generate --force
   ```

### Step 3.3: Optimize Dependencies & Migrate
1. Install production PHP dependencies (skipping dev tools):
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
2. Build production assets:
   ```bash
   npm install
   npm run build
   ```
3. Run production database migrations:
   ```bash
   php artisan migrate --force
   ```
4. Run caching commands to speed up performance:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Step 3.4: Set Up hPanel Cron Scheduler & Queue Worker
Laravel queues are used for sending transactional emails (Welcome, Order Confirmation) and demo PDFs.
1. In hPanel, navigate to **Advanced > Cron Jobs**.
2. Create a new cron job with the following parameters:
   - **Interval:** Once per minute (`* * * * *`)
   - **Command:** `usr/local/bin/php /home/[username]/domains/examtopicsbase.com/artisan schedule:run >> /dev/null 2>&1`
3. Since Hostinger shared hosting does not support daemon processes like `queue:work` natively:
   - The scheduler is configured to run `php artisan queue:work --once` every minute automatically.
   - Ensure the scheduler command is defined in `routes/console.php` or `app/Console/Kernel.php` to handle queue processing on shared host limits.

### Step 3.5: Public Folder Configuration
By default, Hostinger expects the website files to be served directly from `public_html`.
1. Move the contents of `public` to `public_html` OR configure a symbolic link:
   ```bash
   ln -s /home/[username]/domains/examtopicsbase.com/public /home/[username]/domains/examtopicsbase.com/public_html
   ```
2. If symlinks are disabled, create/update `.htaccess` in your root `public_html` to rewrite all traffic to the subfolder public directory:
   ```apache
   RewriteEngine On
   RewriteRule ^(.*)$ public/$1 [L]
   ```

---

## 4. Running Tests
Exam Topics Base contains tests covering routing, Breeze authentication, and user profile management:
```bash
php artisan test
```
All test suites run in-memory using SQLite, requiring no active MySQL daemon to verify setup correctness.
