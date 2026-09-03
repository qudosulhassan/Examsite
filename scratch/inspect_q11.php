<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Question;
use App\Models\Exam;

$exam = Exam::where('exam_code', 'AZ-303')->first();
if (!$exam) {
    echo "AZ-303 not found!\n";
    exit(1);
}

$questions = Question::where('exam_id', $exam->id)->orderBy('id', 'asc')->get();

echo "Total questions for AZ-303: " . $questions->count() . "\n";

if ($questions->count() >= 11) {
    $q11 = $questions[10]; // 11th question
    echo "--- QUESTION 11 DETAILS ---\n";
    echo "ID: {$q11->id}\n";
    echo "Type: {$q11->question_type}\n";
    echo "Text snippet: " . substr(strip_tags($q11->question_text), 0, 150) . "...\n";
    echo "Question Data: " . json_encode($q11->question_data, JSON_PRETTY_PRINT) . "\n";
    echo "Options count: " . $q11->options()->count() . "\n";
    echo "Correct Answer: " . $q11->correct_option . "\n";
}
