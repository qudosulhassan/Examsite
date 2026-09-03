<?php

$htmlContent = file_get_contents('C:\Users\LENOVO\Downloads\AZ-303-220-sequential-clean-final.html');

libxml_use_internal_errors(true);
$dom = new \DOMDocument();
$dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'));
libxml_clear_errors();

$xpath = new \DOMXPath($dom);
$q11Nodes = $xpath->query('//article[@id="q11"]');

if ($q11Nodes->length > 0) {
    echo $dom->saveHTML($q11Nodes->item(0));
} else {
    echo "q11 not found";
}
