<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'google_id',
        'avatar',
        'email_verified_at',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if the user is an admin or has admin-level roles.
     */
    public function isAdmin(): bool
    {
        if (in_array(strtolower($this->role ?? ''), ['admin', 'super_admin', 'super admin', 'administrator', 'staff'])) {
            return true;
        }

        return $this->hasAnyRole(['admin', 'Super Admin', 'Admin', 'Staff', 'administrator']);
    }

    /**
     * Check if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return in_array(strtolower($this->role ?? ''), ['super_admin', 'super admin']) || $this->hasRole(['Super Admin', 'super_admin']);
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    /**
     * Check if user is suspended.
     */
    public function isSuspended(): bool
    {
        return ($this->status ?? 'active') === 'suspended';
    }

    /**
     * Check if user is pending.
     */
    public function isPending(): bool
    {
        return ($this->status ?? 'active') === 'pending';
    }

    /**
     * Check if user is deactivated.
     */
    public function isDeactivated(): bool
    {
        return ($this->status ?? 'active') === 'deactivated';
    }

    /**
     * Get initials for avatar fallback (e.g. "QH", "JS", "SA").
     */
    public function getInitialsAttribute(): string
    {
        $name = trim($this->name ?: '');
        if (empty($name)) {
            return 'U';
        }
        $words = preg_split('/\s+/', $name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
        }
        return strtoupper(substr($name, 0, min(2, strlen($name))));
    }

    /**
     * Get avatar image URL, properly formatted or fallback.
     */
    public function getAvatarUrlAttribute(): string
    {
        if (!empty($this->avatar)) {
            if (Str::startsWith($this->avatar, ['http://', 'https://'])) {
                return $this->avatar;
            }
            if (Str::startsWith($this->avatar, '/storage/')) {
                return asset($this->avatar);
            }
            return asset('storage/' . ltrim($this->avatar, '/'));
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=00D4AA&background=0A1628&bold=true';
    }

    /**
     * Check if the user is a student.
     */
    public function isStudent(): bool
    {
        if (in_array(strtolower($this->role ?? ''), ['student'])) {
            return true;
        }

        return $this->hasAnyRole(['Student', 'student']);
    }

    /**
     * Get the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the subscriptions for the user.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the purchased exams for the user.
     */
    public function userExams()
    {
        return $this->hasMany(UserExam::class);
    }

    /**
     * Get the purchased packages for the user.
     */
    public function userPackages()
    {
        return $this->hasMany(UserPackage::class);
    }

    /**
     * Get the test attempts for the user.
     */
    public function testAttempts()
    {
        return $this->hasMany(TestAttempt::class);
    }

    /**
     * Get the bookmarks for the user.
     */
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get the reviews by the user.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the blog posts by the user.
     */
    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class);
    }

    /**
     * Actions performed by this user (as admin).
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'admin_id');
    }

    /**
     * Audit logs targeting this user.
     */
    public function targetAuditLogs()
    {
        return $this->hasMany(AuditLog::class, 'target_user_id');
    }
}
