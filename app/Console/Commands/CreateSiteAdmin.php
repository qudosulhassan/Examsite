<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateSiteAdmin extends Command
{
    protected $signature = 'site:create-admin {email : Admin email for the site this server runs} {--name=Administrator}';

    protected $description = 'Create (or reset) an administrator account for the site this server runs. The password is asked for interactively and never printed.';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('That is not a valid email address.');
            return self::FAILURE;
        }

        $password = (string) $this->secret('Password (min 12 characters, not shown)');
        if (strlen($password) < 12) {
            $this->error('Password must be at least 12 characters.');
            return self::FAILURE;
        }
        if ($password !== (string) $this->secret('Confirm password')) {
            $this->error('Passwords do not match.');
            return self::FAILURE;
        }

        // Accounts are per site: this creates/updates the admin for the current APP_SITE_ID only.
        $user = User::firstOrNew(['email' => $email]);
        $existed = $user->exists;

        $user->forceFill([
            'name' => $user->name ?: $this->option('name'),
            'password' => $password,
            'role' => 'super_admin',
            'status' => 'active',
            'email_verified_at' => $user->email_verified_at ?: now(),
        ])->save();

        $this->info(($existed ? 'Updated' : 'Created') . " administrator {$email} for site #" . User::currentSiteId() . '.');

        return self::SUCCESS;
    }
}
