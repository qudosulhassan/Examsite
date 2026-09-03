<?php

$htmlPath = 'C:\Users\LENOVO\Downloads\AZ-303-220-sequential-clean-final.html';

if (!file_exists($htmlPath)) {
    echo "File not found: {$htmlPath}\n";
    exit(1);
}

$html = file_get_contents($htmlPath);

libxml_use_internal_errors(true);
$dom = new \DOMDocument();
$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
libxml_clear_errors();

$xpath = new \DOMXPath($dom);
$cards = $xpath->query('//article[contains(@class, "question-card")]');

echo "Auditing Images across all " . $cards->length . " Question Cards...\n\n";

$questionsWithQuestionImages = 0;
$questionsWithOptionImages = 0;
$questionsWithExhibitImages = 0;
$questionsWithAnswerAreaImages = 0;
$totalImagesCount = 0;

$imageReport = [];

for ($i = 0; $i < $cards->length; $i++) {
    $card = $cards->item($i);
    $qNum = $card->getAttribute('data-number') ?: ($i + 1);

    // 1. Question Content Images
    $qImgs = $xpath->query('.//div[contains(@class, "question-content")]/p//img | .//div[contains(@class, "question-content")]/div//img', $card);
    
    // 2. Exhibit Images
    $exImgs = $xpath->query('.//div[contains(@class, "exhibits")]//img', $card);

    // 3. Option Images
    $optImgs = $xpath->query('.//div[contains(@class, "options")]//img', $card);

    // 4. Special Answer / Hotspot / DragDrop Images
    $ansImgs = $xpath->query('.//div[contains(@class, "special-answer")]//img', $card);

    $cardImageCount = $qImgs->length + $exImgs->length + $optImgs->length + $ansImgs->length;
    $totalImagesCount += $cardImageCount;

    if ($qImgs->length > 0) $questionsWithQuestionImages++;
    if ($exImgs->length > 0) $questionsWithExhibitImages++;
    if ($optImgs->length > 0) $questionsWithOptionImages++;
    if ($ansImgs->length > 0) $questionsWithAnswerAreaImages++;

    if ($cardImageCount > 0) {
        $imageReport[$qNum] = [
            'q_imgs' => $qImgs->length,
            'ex_imgs' => $exImgs->length,
            'opt_imgs' => $optImgs->length,
            'ans_imgs' => $ansImgs->length,
            'total' => $cardImageCount,
        ];
        echo "Question {$qNum}: Total {$cardImageCount} images (Content: {$qImgs->length}, Exhibit: {$exImgs->length}, Option: {$optImgs->length}, AnswerArea: {$ansImgs->length})\n";
    }
}

echo "\n--- SUMMARY OF IMAGE AUDIT ---\n";
echo "Total Question Cards with Images: " . count($imageReport) . " / " . $cards->length . "\n";
echo "Total Images extracted across entire file: {$totalImagesCount}\n";
echo "Questions with Content Images: {$questionsWithQuestionImages}\n";
echo "Questions with Exhibit Images: {$questionsWithExhibitImages}\n";
echo "Questions with Option Images: {$questionsWithOptionImages}\n";
echo "Questions with Answer Area Images: {$questionsWithAnswerAreaImages}\n";
