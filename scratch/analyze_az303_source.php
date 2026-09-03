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

echo "Found total question cards: " . $cards->length . "\n\n";

$singleChoice = 0;
$multiChoice = 0;
$hotspotCount = 0;
$dragDropCount = 0;
$matchingCount = 0;
$questionsWithNotesInOptions = 0;

for ($i = 0; $i < $cards->length; $i++) {
    $card = $cards->item($i);
    $qNum = $card->getAttribute('data-number') ?: ($i + 1);

    // Type badge
    $badgeNodes = $xpath->query('.//span[contains(@class, "type-badge")]', $card);
    $badgeText = ($badgeNodes->length > 0) ? trim($badgeNodes->item(0)->nodeValue) : '';

    // Checkbox vs Radio input nodes
    $checkboxes = $xpath->query('.//div[contains(@class, "options")]//input[@type="checkbox"]', $card);
    $radios = $xpath->query('.//div[contains(@class, "options")]//input[@type="radio"]', $card);

    // Option labels
    $optionLabels = $xpath->query('.//div[contains(@class, "options")]//label[contains(@class, "option")]', $card);

    // Correct Answer Value
    $ansValNodes = $xpath->query('.//div[contains(@class, "answer-value")]', $card);
    $ansVal = ($ansValNodes->length > 0) ? trim($ansValNodes->item(0)->nodeValue) : '';

    // Determine type
    $isHotspot = (stripos($badgeText, 'hotspot') !== false || $xpath->query('.//select', $card)->length > 0);
    $isDragDrop = (stripos($badgeText, 'drag') !== false || stripos($badgeText, 'sequential') !== false);
    $isMulti = ($checkboxes->length > 0 || count(array_filter(explode(',', $ansVal))) > 1 || stripos($badgeText, 'multiple answer') !== false);

    if ($isHotspot) {
        $hotspotCount++;
    } elseif ($isDragDrop) {
        $dragDropCount++;
    } elseif ($isMulti) {
        $multiChoice++;
    } else {
        $singleChoice++;
    }

    // Inspect options to see if any contain NOTE:
    foreach ($optionLabels as $lbl) {
        $txt = trim($lbl->nodeValue);
        if (stripos($txt, 'NOTE:') !== false || stripos($txt, 'Each correct selection is worth') !== false) {
            $questionsWithNotesInOptions++;
            echo "Question {$qNum}: NOTE found inside option label: '{$txt}'\n";
        }
    }
}

echo "\n--- QUESTION BANK BREAKDOWN ---\n";
echo "Single Choice Questions: {$singleChoice}\n";
echo "Multiple Response Questions: {$multiChoice}\n";
echo "Hotspot Questions: {$hotspotCount}\n";
echo "Drag & Drop Questions: {$dragDropCount}\n";
echo "Matching Questions: {$matchingCount}\n";
echo "Questions with NOTE in option label: {$questionsWithNotesInOptions}\n";
