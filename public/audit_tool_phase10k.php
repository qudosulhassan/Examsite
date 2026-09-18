<?php

$secret = 'etb_phase10k_audit_839210';
if (($_GET['token'] ?? '') !== $secret && ($_SERVER['HTTP_X_AUDIT_TOKEN'] ?? '') !== $secret) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'status';

if ($action === 'clear_cache') {
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    echo json_encode([
        'success' => true,
        'output' => \Illuminate\Support\Facades\Artisan::output(),
    ]);
    exit;
}

if ($action === 'status') {
    $counts = [
        'users' => \App\Models\User::count(),
        'orders' => \App\Models\Order::count(),
        'exams' => \App\Models\Exam::count(),
        'questions' => \App\Models\Question::count(),
        'question_options' => \App\Models\QuestionOption::count(),
        'question_answers' => \App\Models\QuestionAnswer::count(),
        'site_exam_overlays' => \App\Models\SiteExamOverlay::count(),
    ];

    $config = [
        'app_name' => config('app.name'),
        'app_url' => config('app.url'),
        'app_env' => config('app.env'),
        'queue_default' => config('queue.default'),
        'db_queue' => config('queue.connections.database.queue'),
        'mail_default' => config('mail.default'),
        'mail_host' => config('mail.mailers.smtp.host'),
        'mail_port' => config('mail.mailers.smtp.port'),
        'mail_scheme' => config('mail.mailers.smtp.scheme'),
        'mail_encryption' => env('MAIL_ENCRYPTION'),
        'mail_from_address' => config('mail.from.address'),
        'mail_from_name' => config('mail.from.name'),
    ];

    $jobs = \Illuminate\Support\Facades\DB::table('jobs')->get();
    $failedJobs = \Illuminate\Support\Facades\DB::table('failed_jobs')->get();

    $envPath = base_path('.env');
    $envLines = file_exists($envPath) ? file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $sanitizedEnv = [];
    foreach ($envLines as $line) {
        $line = trim($line);
        if (str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $key = trim($key);
        if (str_contains(strtolower($key), 'password') || str_contains(strtolower($key), 'secret') || str_contains(strtolower($key), 'key')) {
            $sanitizedEnv[$key] = empty($val) ? '[EMPTY]' : '[SET: length ' . strlen(trim($val, "\"'")) . ']';
        } else {
            $sanitizedEnv[$key] = trim($val, "\"'");
        }
    }

    $cachedConfigExists = file_exists(base_path('bootstrap/cache/config.php'));
    $cachedRoutesExists = file_exists(base_path('bootstrap/cache/routes-v7.php'));

    $logPath = storage_path('logs/laravel.log');
    $logTail = file_exists($logPath) ? substr(file_get_contents($logPath), -2500) : '';

    echo json_encode([
        'success' => true,
        'cached_config_exists' => $cachedConfigExists,
        'cached_routes_exists' => $cachedRoutesExists,
        'counts' => $counts,
        'config' => $config,
        'sanitized_env' => $sanitizedEnv,
        'jobs' => $jobs,
        'failed_jobs' => $failedJobs,
        'log_tail' => $logTail,
    ], JSON_PRETTY_PRINT);
    exit;
}

if ($action === 'work_queue') {
    $exitCode = \Illuminate\Support\Facades\Artisan::call('queue:work', [
        '--queue' => $_GET['queue'] ?? 'examsninja',
        '--stop-when-empty' => true,
        '--max-time' => 15,
    ]);
    echo json_encode([
        'success' => true,
        'exitCode' => $exitCode,
        'output' => \Illuminate\Support\Facades\Artisan::output(),
        'remaining_jobs' => \Illuminate\Support\Facades\DB::table('jobs')->get(),
    ], JSON_PRETTY_PRINT);
    exit;
}
