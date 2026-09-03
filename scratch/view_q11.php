<?php

$htmlContent = file_get_contents('C:\Users\LENOVO\Downloads\AZ-303-220-sequential-clean-final.html');

libxml_use_internal_errors(true);
$dom = new \DOMDocument();
$dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'));
libxml_clear_errors();

$xpath = new \DOMXPath($dom);
$q11 = $xpath->query('//article[@id="q11"]')->item(0);

if ($q11) {
    echo "--- Q11 HTML ---\n";
    $pNodes = $xpath->query('.//p', $q11);
    foreach ($pNodes as $p) {
        echo trim($p->nodeValue) . "\n";
    }
    
    $imgs = $xpath->query('.//img', $q11);
    echo "Total images in Q11: " . $imgs->length . "\n";
    
    $ansVal = $xpath->query('.//div[contains(@class, "answer-value")]', $q11);
    if ($ansVal->length > 0) {
        echo "Correct Answer Value: " . trim($ansVal->item(0)->nodeValue) . "\n";
    }
}
