<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionMedia;
use App\Models\QuestionReference;
use App\Models\QuestionAnswer;
use App\Models\TestAttempt;
use App\Models\TestAnswer;
use App\Models\Bookmark;
use App\Models\UserExam;
use App\Models\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

$exam = Exam::where('exam_code', 'AZ-303')
    ->orWhere('slug', 'az-303')
    ->first();

if (!$exam) {
    echo "Exam AZ-303 not found in database.\n";
    exit(0);
}

echo "Found Exam: [ID: {$exam->id}] {$exam->exam_code} - {$exam->exam_name}\n";

$questionIds = Question::where('exam_id', $exam->id)->pluck('id')->toArray();
echo "Total Questions to delete: " . count($questionIds) . "\n";

if (!empty($questionIds)) {
    // 1. Delete TestAnswers / QuestionAnswers / Bookmarks
    if (class_exists(TestAnswer::class)) {
        $deletedAnswers = TestAnswer::whereIn('question_id', $questionIds)->delete();
        echo "Deleted TestAnswer: {$deletedAnswers}\n";
    }
    if (class_exists(QuestionAnswer::class)) {
        $deletedQAnswers = QuestionAnswer::whereIn('question_id', $questionIds)->delete();
        echo "Deleted QuestionAnswer: {$deletedQAnswers}\n";
    }
    if (class_exists(Bookmark::class)) {
        $deletedBookmarks = Bookmark::whereIn('question_id', $questionIds)->delete();
        echo "Deleted Bookmarks: {$deletedBookmarks}\n";
    }

    // 2. Delete Question Options
    $deletedOptions = QuestionOption::whereIn('question_id', $questionIds)->delete();
    echo "Deleted QuestionOptions: {$deletedOptions}\n";

    // 3. Delete Question Media records
    $deletedMedia = QuestionMedia::whereIn('question_id', $questionIds)->delete();
    echo "Deleted QuestionMedia records: {$deletedMedia}\n";

    // 4. Delete Question References
    $deletedRefs = QuestionReference::whereIn('question_id', $questionIds)->delete();
    echo "Deleted QuestionReferences: {$deletedRefs}\n";

    // 5. Delete Questions
    $deletedQ = Question::whereIn('id', $questionIds)->delete();
    echo "Deleted Questions: {$deletedQ}\n";
}

// 6. Delete Test Attempts
if (class_exists(TestAttempt::class)) {
    $deletedAttempts = TestAttempt::where('exam_id', $exam->id)->delete();
    echo "Deleted TestAttempts: {$deletedAttempts}\n";
}

// 7. Delete UserExam enrollments
if (class_exists(UserExam::class)) {
    $deletedUserExams = UserExam::where('exam_id', $exam->id)->delete();
    echo "Deleted UserExams: {$deletedUserExams}\n";
}

// 8. Delete Reviews if any
$deletedReviews = $exam->reviews()->delete();
echo "Deleted Reviews: {$deletedReviews}\n";

// 9. Detach Certifications
$exam->certifications()->detach();
echo "Detached Certifications.\n";

// 10. Delete Redirects if any
$deletedRedirects = Redirect::where('old_url', 'like', '%az-303%')->orWhere('new_url', 'like', '%az-303%')->delete();
echo "Deleted Redirects: {$deletedRedirects}\n";

// 11. Delete Physical Storage Images for az303
$files = Storage::disk('public')->files('questions');
$deletedFileCount = 0;
foreach ($files as $file) {
    if (stripos(basename($file), 'az303_') !== false) {
        Storage::disk('public')->delete($file);
        $deletedFileCount++;
    }
}
echo "Deleted Physical Image Files from storage: {$deletedFileCount}\n";

// 12. Delete the Exam record
$exam->delete();
echo "Exam AZ-303 record deleted successfully.\n";

// Final verification
$remainingQuestions = Question::where('exam_id', $exam->id)->count();
$remainingExam = Exam::where('exam_code', 'AZ-303')->count();
echo "Remaining AZ-303 Exams in DB: {$remainingExam}\n";
echo "Remaining AZ-303 Questions in DB: {$remainingQuestions}\n";
