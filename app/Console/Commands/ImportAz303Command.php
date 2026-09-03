<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vendor;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionMedia;
use App\Models\QuestionAnswer;
use App\Models\QuestionReference;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ImportAz303Command extends Command
{
    protected $signature = 'import:az303 {file? : Absolute path to the AZ-303 html file}';
    protected $description = 'Import AZ-303 exam and all 220 questions from HTML source file with deterministic image extraction';

    public function handle()
    {
        // Try project root first, then default Downloads path
        $filePath = $this->argument('file')
            ?: base_path('AZ-303-220-sequential-clean-final.html');

        if (!file_exists($filePath)) {
            $fallback = 'C:\\Users\\LENOVO\\Downloads\\AZ-303-220-sequential-clean-final.html';
            if (file_exists($fallback)) {
                $filePath = $fallback;
            } else {
                $this->error("HTML file not found at: {$filePath}");
                $this->error("Also tried: {$fallback}");
                return 1;
            }
        }

        $this->info("Reading HTML file: {$filePath} (" . round(filesize($filePath)/1024/1024, 1) . " MB)");
        $htmlContent = file_get_contents($filePath);

        // 1. Resolve or Create Microsoft Vendor
        $vendor = Vendor::firstOrCreate(
            ['slug' => 'microsoft'],
            [
                'name'        => 'Microsoft',
                'description' => 'Microsoft Certification Practice Exams & Question Banks',
                'is_active'   => true,
            ]
        );

        // 2. Purge existing AZ-303 data before fresh import
        $exam = Exam::where('exam_code', 'AZ-303')->orWhere('slug', 'az-303')->first();
        if ($exam) {
            $qIds = Question::where('exam_id', $exam->id)->pluck('id')->toArray();
            if (!empty($qIds)) {
                QuestionOption::whereIn('question_id', $qIds)->delete();
                QuestionAnswer::whereIn('question_id', $qIds)->delete();
                QuestionMedia::whereIn('question_id', $qIds)->delete();
                QuestionReference::whereIn('question_id', $qIds)->delete();
                Question::whereIn('id', $qIds)->delete();
            }
            $this->info("Purged existing questions for AZ-303 before fresh import.");
        }

        // 3. Resolve or Create AZ-303 Exam
        $exam = Exam::updateOrCreate(
            ['exam_code' => 'AZ-303'],
            [
                'vendor_id'       => $vendor->id,
                'exam_name'       => 'Microsoft Azure Architect Technologies',
                'slug'            => 'az-303',
                'description'     => 'Microsoft Azure Architect Technologies practice questions and exam engine simulator.',
                'question_count'  => 0,
                'passing_score'   => 70,
                'price_pdf'       => 29.99,
                'price_engine'    => 39.99,
                'is_active'       => true,
                'last_updated_at' => now(),
            ]
        );

        $this->info("Target Exam: [ID {$exam->id}] {$exam->exam_code} — {$exam->exam_name}");

        // 4. Parse HTML with DOMDocument
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $cards = $xpath->query('//article[contains(@class, "question-card")]');

        $this->info("Found {$cards->length} question cards in HTML.");

        $importedCount = 0;

        foreach ($cards as $index => $card) {
            $qNum = (int)($card->getAttribute('data-number') ?: ($index + 1));

            // --- Type Badge ---
            $badgeNodes = $xpath->query('.//span[contains(@class, "type-badge")]', $card);
            $badgeText  = ($badgeNodes->length > 0) ? trim($badgeNodes->item(0)->nodeValue) : 'Multiple Choice';

            // --- Detect input types ---
            $checkboxNodes = $xpath->query('.//div[contains(@class,"options")]//input[@type="checkbox"]', $card);
            $selectNodes   = $xpath->query('.//select', $card);

            // --- Extract Question Text (paragraphs only, strip img tags) ---
            $pNodes = $xpath->query('.//div[contains(@class,"question-content")]/p', $card);
            $pTexts              = [];
            $extractedInstructions = [];

            foreach ($pNodes as $p) {
                $rawP = trim($p->nodeValue);

                // Move NOTE/instruction text to instructions field, not question body
                if (preg_match('/note:|instructions:|each correct selection is worth|you may select/i', $rawP)) {
                    $extractedInstructions[] = $rawP;
                    continue;
                }

                // Get HTML of paragraph, strip <img> and dangerous tags
                $pHtml = $dom->saveHTML($p);
                // Remove any embedded <img> tags (they go to QuestionMedia instead)
                $pHtml = preg_replace('/<img[^>]*>/i', '', $pHtml);
                $cleaned = trim(strip_tags($pHtml, '<b><i><code><pre><span><br><p><strong><em><table><tr><td><th><ul><ol><li>'));
                if ($cleaned !== '') {
                    $pTexts[] = $cleaned;
                }
            }

            $questionText = implode("\n", array_filter($pTexts));
            if (empty(trim($questionText))) {
                $questionText = "Question {$qNum}";
            }

            // --- Extract Options ---
            $options     = [];
            $optionNodes = $xpath->query('.//div[contains(@class,"options")]//label[contains(@class,"option")]', $card);

            foreach ($optionNodes as $optIdx => $optNode) {
                // Get text from option-text span
                $textNodes = $xpath->query('.//span[contains(@class,"option-text")]', $optNode);
                $optText   = ($textNodes->length > 0)
                    ? trim($textNodes->item(0)->nodeValue)
                    : trim($optNode->nodeValue);

                // Skip if this is a note/instruction that leaked into options
                if (preg_match('/note:|instructions:|each correct selection is worth|you may select/i', $optText)) {
                    $extractedInstructions[] = $optText;
                    continue;
                }

                // Get option key from input value attribute
                $inpNodes = $xpath->query('.//input', $optNode);
                $optKey   = ($inpNodes->length > 0 && $inpNodes->item(0)->getAttribute('value'))
                    ? trim($inpNodes->item(0)->getAttribute('value'))
                    : chr(65 + count($options));

                // Clean option text
                $optTextClean = trim(strip_tags($optText, '<b><i><code><span><strong><em>'));

                $options[] = [
                    'key'        => $optKey,
                    'text'       => $optTextClean,
                    'sort_order' => count($options) + 1,
                ];
            }

            // --- Extract Correct Answer ---
            $ansNodes        = $xpath->query('.//div[contains(@class,"answer-value")]', $card);
            $correctAnswerRaw = ($ansNodes->length > 0) ? trim($ansNodes->item(0)->nodeValue) : '';

            // Parse answer: could be "A", "B,C", "AB", etc.
            $correctAnswers = [];
            if (!empty($correctAnswerRaw)) {
                if (str_contains($correctAnswerRaw, ',')) {
                    $correctAnswers = array_filter(array_map('trim', explode(',', $correctAnswerRaw)));
                } elseif (strlen($correctAnswerRaw) > 1 && ctype_upper($correctAnswerRaw)) {
                    // "AB" style — split into ['A','B']
                    $correctAnswers = str_split($correctAnswerRaw);
                } else {
                    $correctAnswers = [trim($correctAnswerRaw)];
                }
            }

            // --- Determine Question Type ---
            $questionType  = 'single_choice';
            $selectionLimit = 1;

            if (stripos($badgeText, 'Hotspot') !== false || $selectNodes->length > 0) {
                $questionType = 'hotspot';
            } elseif (stripos($badgeText, 'Drag') !== false) {
                $questionType = 'drag_drop';
            } else {
                // Check for multi-select indicators in text
                $combinedText = $questionText . ' ' . implode(' ', $extractedInstructions);
                if (preg_match('/which\s+(?:two|2)\b|select\s+(?:two|2)\b/i', $combinedText)) {
                    $selectionLimit = 2;
                    $questionType   = 'multiple_choice';
                } elseif (preg_match('/which\s+(?:three|3)\b|select\s+(?:three|3)\b/i', $combinedText)) {
                    $selectionLimit = 3;
                    $questionType   = 'multiple_choice';
                } elseif (preg_match('/which\s+(?:four|4)\b|select\s+(?:four|4)\b/i', $combinedText)) {
                    $selectionLimit = 4;
                    $questionType   = 'multiple_choice';
                } elseif ($checkboxNodes->length > 0 || count($correctAnswers) > 1) {
                    $selectionLimit = max(count($correctAnswers), 2);
                    $questionType   = 'multiple_choice';
                }
            }

            // --- Extract Explanation HTML ---
            $expNodes   = $xpath->query('.//div[contains(@class,"explanation") and not(contains(@class,"explanation-heading"))]', $card);
            $explanation = '';
            if ($expNodes->length > 0) {
                $expHtml = $dom->saveHTML($expNodes->item(0));
                // Strip the wrapping div tag, keep inner HTML
                $explanation = trim(preg_replace('/^<div[^>]*>|<\/div>$/i', '', $expHtml));
            }

            // --- Extract ALL Images (base64 or URL) from this question card ---
            $media        = [];
            $seenHashes   = [];
            $imgNodes     = $xpath->query('.//img', $card);

            foreach ($imgNodes as $imgIdx => $imgNode) {
                $src = $imgNode->getAttribute('src');
                if (!$src) continue;

                // De-duplicate by src hash
                $srcHash = md5(substr($src, 0, 200)); // hash prefix for performance
                if (in_array($srcHash, $seenHashes)) continue;
                $seenHashes[] = $srcHash;

                $mediaUrl = $src;

                // Extract and save base64 images to disk
                if (str_starts_with($src, 'data:image/')) {
                    if (preg_match('/data:image\/(\w+);base64,(.+)/s', $src, $matches)) {
                        $ext      = ($matches[1] === 'jpeg') ? 'jpg' : ($matches[1] ?: 'jpg');
                        $filename = "az303_q{$qNum}_img" . (count($media) + 1) . ".{$ext}";
                        $path     = "questions/{$filename}";
                        Storage::disk('public')->put($path, base64_decode($matches[2]));
                        $mediaUrl = "/storage/{$path}";
                    }
                }

                $media[] = [
                    'type'       => 'image',
                    'url'        => $mediaUrl,
                    'caption'    => "Exhibit for Question {$qNum}",
                    'alt'        => "Exhibit for Question {$qNum} — image " . (count($media) + 1),
                    'sort_order' => count($media) + 1,
                ];
            }

            // --- Extract Hotspot Boxes ---
            $questionData = [
                'selection_limit'  => $selectionLimit,
                'drag_items'       => [],
                'correct_order'    => [],
                'matching_pairs'   => [],
                'hotspot_answers'  => [],
                'boxes'            => [],
                'search_text'      => $card->getAttribute('data-search') ?: strip_tags($questionText),
            ];

            if ($questionType === 'hotspot') {
                // Strategy 1: HTML has <select> elements (older format)
                if ($selectNodes->length > 0) {
                    $boxes = [];
                    foreach ($selectNodes as $bIdx => $sel) {
                        $labelText = 'Answer Dropdown ' . ($bIdx + 1);
                        $parent = $sel->parentNode;
                        if ($parent) {
                            $labelNodes = $xpath->query('.//label|.//span[contains(@class,"label")]|.//strong|.//b', $parent);
                            if ($labelNodes->length > 0) {
                                $lText = trim($labelNodes->item(0)->nodeValue);
                                if ($lText && strlen($lText) < 200) $labelText = $lText;
                            }
                        }
                        $boxOptions = [];
                        $optElements = $xpath->query('.//option', $sel);
                        foreach ($optElements as $oEl) {
                            $oVal = trim($oEl->nodeValue);
                            if ($oVal !== '' && !preg_match('/^\[?\s*select\s*\]?$/i', $oVal)) {
                                $boxOptions[] = $oVal;
                            }
                        }
                        $boxes[] = [
                            'id'             => 'box_' . ($bIdx + 1),
                            'label'          => $labelText,
                            'options'        => $boxOptions,
                            'correct_answer' => $correctAnswers[$bIdx] ?? ($boxOptions[0] ?? ''),
                        ];
                    }
                    $questionData['boxes']           = $boxes;
                    $questionData['hotspot_answers'] = $boxes;
                }
                // Strategy 2: Answer stored as "Box 1: X | Box 2: Y" text (image-based hotspot)
                elseif (!empty($correctAnswerRaw) && preg_match('/Box\s*\d+\s*:/i', $correctAnswerRaw)) {
                    $parts = preg_split('/\s*\|\s*/', $correctAnswerRaw);
                    $boxes = [];
                    foreach ($parts as $bIdx => $part) {
                        // Parse "Box 1: Yes" or "Box 2: 3"
                        if (preg_match('/Box\s*(\d+)\s*:\s*(.+)/i', trim($part), $pm)) {
                            $boxNum    = (int)$pm[1];
                            $boxAnswer = trim($pm[2]);
                            // Build a short option list from the explanation text if possible
                            // Default: offer Yes/No for boolean, or common numeric choices
                            $boxOptions = $this->inferHotspotOptions($boxAnswer);
                            $boxes[] = [
                                'id'             => 'box_' . $boxNum,
                                'label'          => 'Box ' . $boxNum,
                                'options'        => $boxOptions,
                                'correct_answer' => $boxAnswer,
                            ];
                        }
                    }
                    if (!empty($boxes)) {
                        $questionData['boxes']           = $boxes;
                        $questionData['hotspot_answers'] = $boxes;
                    }
                }
                // Strategy 3: Single correct answer for hotspot (e.g. "Yes")
                elseif (!empty($correctAnswerRaw)) {
                    $boxAnswer  = trim($correctAnswerRaw);
                    $boxOptions = $this->inferHotspotOptions($boxAnswer);
                    $boxes = [[
                        'id'             => 'box_1',
                        'label'          => 'Answer Area',
                        'options'        => $boxOptions,
                        'correct_answer' => $boxAnswer,
                    ]];
                    $questionData['boxes']           = $boxes;
                    $questionData['hotspot_answers'] = $boxes;
                }
            }

            // --- Save via Universal Model ---
            $universalData = [
                'exam_id'         => $exam->id,
                'topic'           => 'Azure Architecture',
                'question_type'   => $questionType,
                'question_text'   => $questionText,
                'instructions'    => implode(' ', array_unique(array_filter($extractedInstructions))),
                'explanation'     => $explanation,
                'is_active'       => true,
                'status'          => 'published',
                'source_type'     => 'html_import',
                'options'         => $options,
                'correct_answers' => array_values(array_filter($correctAnswers)),
                'media'           => $media,
                // Pass boxes at top-level AND inside question_data (saveFromUniversalModel reads top-level)
                'boxes'           => $questionData['boxes'] ?? [],
                'hotspot_answers' => $questionData['hotspot_answers'] ?? [],
                'drag_items'      => $questionData['drag_items'] ?? [],
                'correct_order'   => $questionData['correct_order'] ?? [],
                'matching_pairs'  => $questionData['matching_pairs'] ?? [],
                'question_data'   => $questionData,
            ];

            Question::saveFromUniversalModel($universalData);
            $importedCount++;

            if ($importedCount % 40 === 0 || $importedCount === $cards->length) {
                $this->info("  Imported {$importedCount} / {$cards->length} questions...");
            }
        }

        // 5. Update Exam question count
        $exam->update([
            'question_count'  => $exam->questions()->count(),
            'last_updated_at' => now(),
        ]);

        $this->info("✅ SUCCESS! Imported all {$importedCount} questions for Exam {$exam->exam_code} [ID: {$exam->id}]");
        $this->info("   Exam URL: /demo-test-engine/az-303");
        return 0;
    }

    /**
     * Infer dropdown options for image-based hotspot questions.
     * The correct answer is known; we generate a plausible set of options.
     */
    protected function inferHotspotOptions(string $correctAnswer): array
    {
        $val = trim($correctAnswer);

        // Boolean-style answers
        if (in_array(strtolower($val), ['yes', 'no'])) {
            return ['Yes', 'No'];
        }
        if (in_array(strtolower($val), ['true', 'false'])) {
            return ['True', 'False'];
        }

        // Numeric answer — provide surrounding range
        if (is_numeric($val)) {
            $n = (int)$val;
            $opts = [];
            for ($i = max(1, $n - 2); $i <= $n + 2; $i++) {
                $opts[] = (string)$i;
            }
            return $opts;
        }

        // Common Azure CLI/PowerShell keyword answers
        $azureKeywords = [
            'Set-AzureRmVirtualNetwork', 'New-AzureRmVirtualNetwork', 'Add-AzureRmVirtualNetworkSubnetConfig',
            'New-AzureRmPublicIpAddress', 'New-AzureRmLoadBalancer', 'New-AzureRmVmss',
            'Update-AzureRmVmss', 'Set-AzureRmVmss',
        ];
        foreach ($azureKeywords as $kw) {
            if (stripos($val, $kw) !== false) {
                return [$kw, str_replace(['Set-','Add-','Update-'], 'New-', $kw), str_replace('New-','Get-',$kw)];
            }
        }

        // Default: return the correct answer as the only known option + a few placeholders
        return [$val, 'Option B', 'Option C'];
    }
}
