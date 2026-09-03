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
    protected $description = 'Import AZ-303 exam and all 220 questions from HTML file with canonical structure, note filtering, and accurate selection limits';

    public function handle()
    {
        $filePath = $this->argument('file') ?: 'C:\\Users\\LENOVO\\Downloads\\AZ-303-220-sequential-clean-final.html';

        if (!file_exists($filePath)) {
            $this->error("File not found at: {$filePath}");
            return 1;
        }

        $this->info("Reading HTML file from {$filePath}...");
        $htmlContent = file_get_contents($filePath);

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

        // Clear existing questions for AZ-303 to avoid duplicates
        $deletedCount = Question::where('exam_id', $exam->id)->delete();
        $this->info("Cleared {$deletedCount} existing questions for AZ-303 before fresh import.");

        // Use DOMDocument & DOMXPath to parse HTML cards
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $cards = $xpath->query('//article[contains(@class, "question-card")]');

        $this->info("Found {$cards->length} question cards in HTML.");

        $importedCount = 0;

        foreach ($cards as $index => $card) {
            $qNum = $card->getAttribute('data-number') ?: ($index + 1);

            // Type badge
            $badgeNodes = $xpath->query('.//span[contains(@class, "type-badge")]', $card);
            $badgeText = ($badgeNodes->length > 0) ? trim($badgeNodes->item(0)->nodeValue) : 'Multiple Choice';

            // Checkbox vs Radio input nodes
            $checkboxNodes = $xpath->query('.//div[contains(@class, "options")]//input[@type="checkbox"]', $card);
            $selectNodes = $xpath->query('.//select', $card);

            // Extract question content paragraphs
            $pNodes = $xpath->query('.//div[contains(@class, "question-content")]/p', $card);
            $pTexts = [];
            $extractedInstructions = [];

            foreach ($pNodes as $p) {
                $rawP = trim($p->nodeValue);
                if (preg_match('/note:|instructions:|each correct selection is worth|you may select|OTE:/i', $rawP)) {
                    $extractedInstructions[] = $rawP;
                } else {
                    $html = $dom->saveHTML($p);
                    $pTexts[] = trim(strip_tags($html, '<b><i><code><pre><span><br><p><table><tr><td><th><ul><li>'));
                }
            }
            $questionText = implode("<br>", array_filter($pTexts));
            if (empty($questionText)) {
                $questionText = "Question {$qNum}";
            }

            // Extract options and filter out notes inside options
            $options = [];
            $optionNodes = $xpath->query('.//div[contains(@class, "options")]//label[contains(@class, "option")]', $card);
            foreach ($optionNodes as $optIdx => $optNode) {
                $textNodes = $xpath->query('.//span[contains(@class, "option-text")]', $optNode);
                $optText = ($textNodes->length > 0) ? trim($textNodes->item(0)->nodeValue) : trim($optNode->nodeValue);
                
                // Filter out instructional notes that were placed inside option labels in source HTML
                if (preg_match('/note:|instructions:|each correct selection is worth|you may select|OTE:/i', $optText)) {
                    $extractedInstructions[] = preg_replace('/^[A-Z](\.|\s*)/i', '', $optText);
                    continue; // Skip adding this as an option!
                }

                $cleanText = preg_replace('/^[A-Z](\.|\s*)/i', '', $optText);
                $cleanText = trim($cleanText);

                $inpNodes = $xpath->query('.//input', $optNode);
                $optKey = ($inpNodes->length > 0 && $inpNodes->item(0)->getAttribute('value')) 
                    ? trim($inpNodes->item(0)->getAttribute('value')) 
                    : chr(65 + count($options));

                $options[] = [
                    'key' => $optKey,
                    'text' => $cleanText,
                    'sort_order' => count($options) + 1,
                ];
            }

            // Extract correct answer
            $ansNodes = $xpath->query('.//div[contains(@class, "answer-value")]', $card);
            $correctAnswerRaw = ($ansNodes->length > 0) ? trim($ansNodes->item(0)->nodeValue) : '';
            $correctAnswers = array_filter(array_map('trim', explode(',', $correctAnswerRaw)));

            // Determine question type & selection limit
            $questionType = 'single_choice';
            $selectionLimit = 1;

            if (stripos($badgeText, 'Hotspot') !== false || $selectNodes->length > 0) {
                $questionType = 'hotspot';
            } elseif (stripos($badgeText, 'Drag') !== false) {
                $questionType = 'drag_drop';
            } else {
                $combinedText = $questionText . ' ' . implode(' ', $extractedInstructions);
                
                if (preg_match('/which two|select two|select 2/i', $combinedText)) {
                    $selectionLimit = 2;
                    $questionType = 'multiple_choice';
                } elseif (preg_match('/which three|select three|select 3/i', $combinedText)) {
                    $selectionLimit = 3;
                    $questionType = 'multiple_choice';
                } elseif (preg_match('/which four|select four|select 4/i', $combinedText)) {
                    $selectionLimit = 4;
                    $questionType = 'multiple_choice';
                } elseif ($checkboxNodes->length > 0 || count($correctAnswers) > 1 || stripos($badgeText, 'multiple answer') !== false || preg_match('/each correct selection is worth/i', $combinedText)) {
                    $selectionLimit = max(count($correctAnswers), 2);
                    $questionType = 'multiple_choice';
                }
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

            // Prepare question data JSON
            $questionData = [
                'instructions' => implode(' ', array_unique($extractedInstructions)),
                'selection_limit' => $selectionLimit,
                'drag_items' => [],
                'hotspot_answers' => [],
            ];

            // If Hotspot, extract select boxes
            if ($questionType === 'hotspot' && $selectNodes->length > 0) {
                $boxes = [];
                foreach ($selectNodes as $bIdx => $sel) {
                    $boxOptions = [];
                    $optElements = $xpath->query('.//option', $sel);
                    foreach ($optElements as $oEl) {
                        $oVal = trim($oEl->nodeValue);
                        if ($oVal !== '' && stripos($oVal, 'select') === false) {
                            $boxOptions[] = $oVal;
                        }
                    }
                    $boxes[] = [
                        'id' => 'box_' . ($bIdx + 1),
                        'label' => 'Answer Area Dropdown ' . ($bIdx + 1),
                        'options' => $boxOptions,
                        'correct_answer' => $correctAnswers[$bIdx] ?? ($boxOptions[0] ?? ''),
                    ];
                }
                $questionData['boxes'] = $boxes;
                $questionData['hotspot_answers'] = $boxes;
            }

            // Prepare universal model array
            $universalData = [
                'exam_id' => $exam->id,
                'topic' => 'Azure Architecture',
                'question_type' => $questionType,
                'question_text' => $questionText,
                'instructions' => implode(' ', array_unique($extractedInstructions)),
                'explanation' => $explanation,
                'is_active' => true,
                'status' => 'published',
                'options' => $options,
                'correct_answers' => $correctAnswers,
                'media' => $media,
                'question_data' => $questionData,
            ];

            // Save question using universal importer
            Question::saveFromUniversalModel($universalData);
            $importedCount++;

            if ($importedCount % 40 === 0 || $importedCount === $cards->length) {
                $this->info("Imported {$importedCount} / {$cards->length} questions...");
            }
        }

        // Update Exam count
        $exam->update([
            'question_count' => $exam->questions()->count(),
            'last_updated_at' => now(),
        ]);

        $this->info("SUCCESS! Successfully re-imported all {$importedCount} questions for Exam {$exam->exam_code} with canonical note filtering and selection limits!");
        return 0;
    }
}
