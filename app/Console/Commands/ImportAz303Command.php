<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vendor;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportAz303Command extends Command
{
    protected $signature = 'import:az303 {file? : Absolute path to the AZ-303 html file}';
    protected $description = 'Import AZ-303 exam and all 220 questions from HTML file into live database';

    public function handle()
    {
        $filePath = $this->argument('file') ?: 'C:\\Users\\LENOVO\\Downloads\\AZ-303-220-sequential-clean-final.html';

        if (!file_exists($filePath)) {
            $this->error("File not found at: {$filePath}");
            return 1;
        }

        $this->info("Reading HTML file from {$filePath}...");
        $htmlContent = file_get_encoding($filePath);

        // 1. Resolve or Create Microsoft Vendor
        $vendor = Vendor::firstOrCreate(
            ['slug' => 'microsoft'],
            [
                'name' => 'Microsoft',
                'description' => 'Microsoft Certification Practice Exams & Question Banks',
                'is_active' => true,
            ]
        );

        // 2. Resolve or Create AZ-303 Exam
        $exam = Exam::firstOrCreate(
            ['exam_code' => 'AZ-303'],
            [
                'vendor_id' => $vendor->id,
                'exam_name' => 'Microsoft Azure Architect Technologies',
                'slug' => 'az-303',
                'description' => 'Microsoft Azure Architect Technologies practice questions and exam engine simulator.',
                'question_count' => 0,
                'passing_score' => 70,
                'price_pdf' => 29.99,
                'price_engine' => 39.99,
                'is_active' => true,
                'last_updated_at' => now(),
            ]
        );

        $this->info("Target Exam: [ID {$exam->id}] {$exam->exam_code} - {$exam->exam_name}");

        // Use DOMDocument & DOMXPath to parse HTML cards
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $cards = $xpath->query('//article[contains(@class, "question-card")]');

        $this->info("Found {$cards->length} question cards in HTML.");

        $importedCount = 0;
        $updatedCount = 0;

        foreach ($cards as $index => $card) {
            $qNum = $card->getAttribute('data-number') ?: ($index + 1);

            // Determine type badge
            $badgeNodes = $xpath->query('.//span[contains(@class, "type-badge")]', $card);
            $badgeText = ($badgeNodes->length > 0) ? trim($badgeNodes->item(0)->nodeValue) : 'Multiple Choice';

            $questionType = 'single_choice';
            if (stripos($badgeText, 'Hotspot') !== false) {
                $questionType = 'hotspot';
            } elseif (stripos($badgeText, 'Drag') !== false) {
                $questionType = 'drag_drop';
            } elseif (stripos($badgeText, 'Multiple Answer') !== false) {
                $questionType = 'multiple_choice';
            }

            // Extract question content paragraphs
            $pNodes = $xpath->query('.//div[contains(@class, "question-content")]/p', $card);
            $pTexts = [];
            foreach ($pNodes as $p) {
                $html = $dom->saveHTML($p);
                $pTexts[] = trim(strip_tags($html, '<b><i><code><pre><span><br><p><table><tr><td><th><ul><li>'));
            }
            $questionText = implode("<br>", array_filter($pTexts));
            if (empty($questionText)) {
                $questionText = "Question {$qNum}";
            }

            // Extract options
            $options = [];
            $optionNodes = $xpath->query('.//div[contains(@class, "options")]//label[contains(@class, "option")]', $card);
            foreach ($optionNodes as $optIdx => $optNode) {
                $inpNodes = $xpath->query('.//input', $optNode);
                $optKey = ($inpNodes->length > 0 && $inpNodes->item(0)->getAttribute('value')) 
                    ? trim($inpNodes->item(0)->getAttribute('value')) 
                    : chr(65 + $optIdx);

                $textNodes = $xpath->query('.//span[contains(@class, "option-text")]', $optNode);
                $optText = ($textNodes->length > 0) ? trim($textNodes->item(0)->nodeValue) : trim($optNode->nodeValue);
                // Clean leading option letter e.g. "A. "
                $optText = preg_replace('/^[A-Z]\.\s*/i', '', $optText);

                $options[] = [
                    'key' => $optKey,
                    'text' => $optText,
                    'sort_order' => $optIdx + 1,
                ];
            }

            // Extract correct answer
            $ansNodes = $xpath->query('.//div[contains(@class, "answer-value")]', $card);
            $correctAnswerRaw = ($ansNodes->length > 0) ? trim($ansNodes->item(0)->nodeValue) : '';
            $correctAnswers = array_filter(array_map('trim', explode(',', $correctAnswerRaw)));

            if (count($correctAnswers) > 1 && $questionType === 'single_choice') {
                $questionType = 'multiple_choice';
            }

            // Extract explanation
            $expNodes = $xpath->query('.//div[contains(@class, "explanation")]', $card);
            $explanation = '';
            if ($expNodes->length > 0) {
                $expHtml = $dom->saveHTML($expNodes->item(0));
                $explanation = trim(preg_replace('/^<div[^>]*>|<\/div>$/i', '', $expHtml));
            }

            // Extract exhibits / images
            $media = [];
            $imgNodes = $xpath->query('.//div[contains(@class, "exhibits")]//img', $card);
            foreach ($imgNodes as $imgIdx => $imgNode) {
                $src = $imgNode->getAttribute('src');
                if ($src) {
                    // Check if base64 data URI
                    if (str_starts_with($src, 'data:image/')) {
                        preg_match('/data:image\/(\w+);base64,(.*)/', $src, $matches);
                        if (!empty($matches[2])) {
                            $ext = $matches[1] ?: 'png';
                            $filename = "az303_q{$qNum}_img" . ($imgIdx + 1) . ".{$ext}";
                            $storedPath = "questions/{$filename}";
                            Storage::disk('public')->put($storedPath, base64_decode($matches[2]));
                            $mediaUrl = "/storage/{$storedPath}";
                        } else {
                            $mediaUrl = $src;
                        }
                    } else {
                        $mediaUrl = $src;
                    }

                    $media[] = [
                        'type' => 'image',
                        'url' => $mediaUrl,
                        'caption' => "Exhibit for Question {$qNum}",
                        'alt' => "Exhibit for Question {$qNum}",
                        'sort_order' => $imgIdx + 1,
                    ];
                }
            }

            // Prepare universal model array
            $universalData = [
                'exam_id' => $exam->id,
                'topic' => 'Azure Architecture',
                'question_type' => $questionType,
                'question_text' => $questionText,
                'instructions' => 'Select the best response to fulfill the scenario requirements.',
                'explanation' => $explanation,
                'is_active' => true,
                'status' => 'published',
                'options' => $options,
                'correct_answers' => $correctAnswers,
                'media' => $media,
            ];

            // Save question using universal importer
            $question = Question::saveFromUniversalModel($universalData);
            $importedCount++;

            if ($importedCount % 20 === 0 || $importedCount === $cards->length) {
                $this->info("Imported {$importedCount} / {$cards->length} questions...");
            }
        }

        // Update Exam count
        $exam->update([
            'question_count' => $exam->questions()->count(),
            'last_updated_at' => now(),
        ]);

        $this->info("SUCCESS! Successfully imported all {$importedCount} questions for Exam {$exam->exam_code}!");
        return 0;
    }
}

function file_get_encoding($path) {
    return file_get_contents($path);
}
