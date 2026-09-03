<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Question;
use App\Models\Exam;
use App\Models\QuestionMedia;

$exam = Exam::where('exam_code', 'AZ-303')->first();
$questions = Question::where('exam_id', $exam->id)->with('media')->get();

echo "Verifying Image Mapping for Exam AZ-303 (Total Questions: " . $questions->count() . ")...\n\n";

$inlineImgCount = 0;
$totalMediaCount = 0;
$missingDiskFiles = 0;

foreach ($questions as $q) {
    // 1. Check for inline <img> in question_text
    if (stripos($q->question_text, '<img') !== false) {
        $inlineImgCount++;
        echo "WARNING: Question ID {$q->id} still contains inline <img tag!\n";
    }

    // 2. Check media records
    foreach ($q->media as $m) {
        $totalMediaCount++;
        $relativePath = str_replace('/storage/', '', $m->media_url);
        $fullPath = storage_path('app/public/' . $relativePath);

        if (!file_exists($fullPath)) {
            $missingDiskFiles++;
            echo "ERROR: Media ID {$m->id} for Question ID {$q->id} missing on disk: {$fullPath}\n";
        }
    }
}

echo "\n--- VERIFICATION AUDIT RESULTS ---\n";
echo "Total Questions Scanned: " . $questions->count() . "\n";
echo "Questions with inline <img tags: {$inlineImgCount} (Expect 0)\n";
echo "Total Database Media Records: {$totalMediaCount}\n";
echo "Media Files Missing on Disk: {$missingDiskFiles} (Expect 0)\n";
