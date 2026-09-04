<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log an administrative or user management action.
     */
    public static function log(string $action, string $description, ?int $targetUserId = null, ?array $payload = null): ?AuditLog
    {
        try {
            $adminId = Auth::id();

            // Sanitize sensitive fields from payload
            if ($payload) {
                $payload = self::sanitizePayload($payload);
            }

            return AuditLog::create([
                'admin_id' => $adminId,
                'target_user_id' => $targetUserId,
                'action' => $action,
                'description' => $description,
                'ip_address' => Request::ip(),
                'user_agent' => substr(Request::userAgent() ?? '', 0, 500),
                'payload' => $payload,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AuditLogService failure: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Strip plaintext passwords and secrets from payload array.
     */
    protected static function sanitizePayload(array $data): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'token', 'remember_token', 'credit_card', 'cvv', 'secret'];

        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $data[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $data[$key] = self::sanitizePayload($value);
            }
        }

        return $data;
    }
}
