<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Order;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionAnswer;
use App\Models\SiteExamOverlay;

class InternalAuditController extends Controller
{
    private string $secretToken = 'etb_audit_phase10k_884920';

    public function handle(Request $request)
    {
        if ($request->header('X-Audit-Token') !== $this->secretToken && $request->query('token') !== $this->secretToken) {
            abort(404);
        }

        $action = $request->query('action', 'status');

        if ($action === 'status') {
            $dataSafety = [
                'users' => User::count(),
                'orders' => Order::count(),
                'exams' => Exam::count(),
                'questions' => Question::count(),
                'question_options' => QuestionOption::count(),
                'question_answers' => QuestionAnswer::count(),
                'site_exam_overlays' => SiteExamOverlay::count(),
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
                'mail_from_address' => config('mail.from.address'),
                'mail_from_name' => config('mail.from.name'),
            ];

            $jobs = DB::table('jobs')->select('id', 'queue', 'attempts', 'reserved_at', 'available_at', 'created_at')->get();
            $failedJobs = DB::table('failed_jobs')->select('id', 'connection', 'queue', 'failed_at', 'exception')->limit(10)->get();

            $logPath = storage_path('logs/laravel.log');
            $recentLog = file_exists($logPath) ? substr(file_get_contents($logPath), -3000) : '';

            return response()->json([
                'success' => true,
                'data_safety' => $dataSafety,
                'config' => $config,
                'jobs' => $jobs,
                'failed_jobs' => $failedJobs,
                'recent_log' => $recentLog,
            ]);
        }

        if ($action === 'process_queue') {
            $exitCode = Artisan::call('queue:work', [
                '--queue' => 'examsninja',
                '--stop-when-empty' => true,
                '--max-time' => 15,
            ]);

            return response()->json([
                'success' => true,
                'exit_code' => $exitCode,
                'output' => Artisan::output(),
                'remaining_jobs' => DB::table('jobs')->select('id', 'queue', 'attempts', 'reserved_at', 'available_at', 'created_at')->get(),
            ]);
        }

        if ($action === 'clear_cache') {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');

            return response()->json([
                'success' => true,
                'output' => 'Caches cleared successfully',
            ]);
        }

        return response()->json(['error' => 'Unknown action'], 400);
    }
}
