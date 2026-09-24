<?php

namespace App\Services\QuestionImport\Pdf;

class PdfOptionParser
{
    /**
     * Parse options list and clean question prompt.
     *
     * @param string $rawBlock
     * @return array ['question_text' => '...', 'options' => [['key' => 'A', 'text' => '...'], ...]]
     */
    public static function parseOptions(string $rawBlock): array
    {
        // Normalize unicode spaces & non-breaking spaces
        $rawBlock = preg_replace('/[\x{00A0}\x{200B}\x{FEFF}\x{202F}\x{3000}]/u', ' ', $rawBlock);
        $lines = explode("\n", $rawBlock);
        $questionPromptLines = [];
        $options = [];
        $currentOptionKey = null;
        $currentOptionText = '';

        // Pattern for option start: A. | A) | A: | [A] | (A) | Option A: | Choice A: | A -
        $optionPattern = '/^(?:\[([A-H])\]|\(([A-H])\)|([A-H])[\.\)\:\-]\s+|Option\s+([A-H])[:\.\-]?\s+|Choice\s+([A-H])[:\.\-]?\s*)(.+)$/i';

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            // Strip leading bullet glyphs (e.g. ɣ, ●, •, ✑, -, *) before testing option pattern
            $cleanLine = preg_replace('/^[^\w\s\(\[\n]+\s*/u', '', $trimmed);

            if (preg_match($optionPattern, $cleanLine, $matches)) {
                // Save previous option if any
                if ($currentOptionKey !== null) {
                    $options[] = [
                        'key' => strtoupper($currentOptionKey),
                        'text' => trim($currentOptionText),
                    ];
                }

                $key = $matches[1] ?: ($matches[2] ?: ($matches[3] ?: ($matches[4] ?: $matches[5])));
                $currentOptionKey = strtoupper($key);
                $currentOptionText = trim(end($matches));
            } elseif ($currentOptionKey !== null) {
                // Multi-line option text continuation
                $currentOptionText .= ' ' . $trimmed;
            } else {
                // Still in question prompt
                $questionPromptLines[] = $trimmed;
            }
        }

        // Push last option
        if ($currentOptionKey !== null) {
            $options[] = [
                'key' => strtoupper($currentOptionKey),
                'text' => trim($currentOptionText),
            ];
        }

        return [
            'question_text' => implode("\n", $questionPromptLines),
            'options' => $options,
        ];
    }
}
