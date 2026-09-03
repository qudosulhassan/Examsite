<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Question;
use App\Models\Exam;

$exam = Exam::where('exam_code', 'AZ-303')->first();
$questions = Question::where('exam_id', $exam->id)->with('options')->get();

echo "Total Database Questions for AZ-303: " . $questions->count() . "\n";

$singleCount = 0;
$multiCount = 0;
$hotspotCount = 0;
$dragDropCount = 0;
$notesInOptionsCount = 0;

foreach ($questions as $q) {
    if ($q->question_type === 'single_choice') {
        $singleCount++;
    } elseif ($q->question_type === 'multiple_choice') {
        $multiCount++;
    } elseif ($q->question_type === 'hotspot') {
        $hotspotCount++;
    } elseif ($q->question_type === 'drag_drop') {
        $dragDropCount++;
    }

    foreach ($q->options as $opt) {
        if (stripos($opt->option_text, 'NOTE:') !== false || stripos($opt->option_text, 'Each correct selection is worth') !== false) {
            $notesInOptionsCount++;
            echo "Question ID {$q->id} (Type: {$q->question_type}) HAS NOTE IN OPTION {$opt->option_key}: {$opt->option_text}\n";
        }
    }
}

echo "\n--- DATABASE QUESTION TYPES --- \n";
echo "Single Choice: {$singleCount}\n";
echo "Multiple Choice (Checkboxes): {$multiCount}\n";
echo "Hotspot: {$hotspotCount}\n";
echo "Drag & Drop: {$dragDropCount}\n";
echo "Notes inside option choices count: {$notesInOptionsCount}\n";
