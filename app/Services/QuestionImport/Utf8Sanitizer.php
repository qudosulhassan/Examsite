<?php

namespace App\Services\QuestionImport;

class Utf8Sanitizer
{
    /**
     * Recursively sanitize any string, array, or nested structure into guaranteed valid UTF-8.
     *
     * @param mixed $data
     * @return mixed
     */
    public static function clean(mixed $data): mixed
    {
        if (is_string($data)) {
            return self::cleanString($data);
        }

        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $k => $v) {
                $cleanedKey = is_string($k) ? self::cleanString($k) : $k;
                $cleaned[$cleanedKey] = self::clean($v);
            }
            return $cleaned;
        }

        return $data;
    }

    /**
     * Clean a single string into guaranteed valid UTF-8, stripping control chars and corrupt multi-byte sequences.
     *
     * @param string|null $str
     * @return string
     */
    public static function cleanString(?string $str): string
    {
        if ($str === null || $str === '') {
            return '';
        }

        // 1. If valid UTF-8 already, strip unprintable control characters
        if (mb_check_encoding($str, 'UTF-8')) {
            return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $str);
        }

        // 2. Try iconv UTF-8 ignore
        $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $str);
        if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
            return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $converted);
        }

        // 3. Try UTF-16BE / UTF-16LE conversion
        if (mb_check_encoding($str, 'UTF-16BE')) {
            $converted = @mb_convert_encoding($str, 'UTF-8', 'UTF-16BE');
            if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
                return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $converted);
            }
        }

        // 4. Try Windows-1252 / ISO-8859-1 conversion
        $converted = @mb_convert_encoding($str, 'UTF-8', 'Windows-1252');
        if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
            return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $converted);
        }

        // 5. Ultimate fallback: convert from Latin-1
        $clean = @mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', (string)$clean);
    }
}
