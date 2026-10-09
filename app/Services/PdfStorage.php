<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Optional Cloudflare R2 copy of exam PDFs, so several sites on separate servers can serve the
 * same files. Every method is a safe no-op when R2 is not configured, so a site without R2
 * keeps working purely from its own disks.
 */
class PdfStorage
{
    public static function configured(): bool
    {
        return !empty(config('filesystems.disks.r2.key'))
            && !empty(config('filesystems.disks.r2.secret'))
            && !empty(config('filesystems.disks.r2.bucket'))
            && !empty(config('filesystems.disks.r2.endpoint'))
            && class_exists(\League\Flysystem\AwsS3V3\AwsS3V3Adapter::class);
    }

    public static function put(string $folder, $file, string $filename): bool
    {
        if (!static::configured()) {
            return false;
        }

        try {
            return (bool) Storage::disk('r2')->putFileAs($folder, $file, $filename);
        } catch (\Throwable $e) {
            Log::warning('R2 upload failed for ' . $folder . '/' . $filename . ': ' . $e->getMessage());
            return false;
        }
    }

    public static function exists(string $path): bool
    {
        if (!static::configured()) {
            return false;
        }

        try {
            return Storage::disk('r2')->exists($path);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function delete(string $path): void
    {
        if (!static::configured()) {
            return;
        }

        try {
            Storage::disk('r2')->delete($path);
        } catch (\Throwable $e) {
            Log::warning('R2 delete failed for ' . $path . ': ' . $e->getMessage());
        }
    }

    /**
     * Short-lived signed download URL, or null if unavailable.
     */
    public static function temporaryUrl(string $path, \DateTimeInterface $expires, ?string $downloadName = null): ?string
    {
        if (!static::configured()) {
            return null;
        }

        try {
            $options = $downloadName
                ? ['ResponseContentDisposition' => 'attachment; filename="' . addslashes($downloadName) . '"']
                : [];

            return Storage::disk('r2')->temporaryUrl($path, $expires, $options);
        } catch (\Throwable $e) {
            Log::warning('R2 signed URL failed for ' . $path . ': ' . $e->getMessage());
            return null;
        }
    }
}
