<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'user_audit_logs';

    protected $fillable = [
        'admin_id',
        'target_user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id')->withTrashed();
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id')->withTrashed();
    }
}
