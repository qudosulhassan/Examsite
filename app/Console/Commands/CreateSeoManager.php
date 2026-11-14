<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Creates the limited "SEO Manager" login for the site this server runs. That login can open the Technical SEO
 * Center (/admin/settings/seo) and nothing else in the admin. Run it on the server of the site that should have it
 * (for example the ProExamTopics server); accounts and roles live in that site's own database.
 */
class CreateSeoManager extends Command
{
    protected $signature = 'site:create-seo-manager {email : Email of the SEO manager} {--name=SEO Manager}';

    protected $description = 'Create (or reset) an SEO Manager login limited to the Technical SEO Center for the site this server runs. The password is asked for interactively and never printed.';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('That is not a valid email address.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Password for the SEO manager (min 12 characters, not shown)');
        if (strlen($password) < 12) {
            $this->error('Password must be at least 12 characters.');

            return self::FAILURE;
        }
        if ($password !== (string) $this->secret('Confirm password')) {
            $this->error('Passwords do not match.');

            return self::FAILURE;
        }

        $permission = Permission::firstOrCreate(['name' => 'manage-seo', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'SEO Manager', 'guard_name' => 'web']);
        $role->syncPermissions([$permission]);   // exactly one permission, nothing else

        $user = User::firstOrNew(['email' => $email]);
        $existed = $user->exists;
        if ($existed && $user->isAdmin()) {
            $this->error("{$email} is already an administrator of this site. Use another email so their admin rights are not reduced by accident.");

            return self::FAILURE;
        }

        $user->forceFill([
            'name' => $user->name ?: $this->option('name'),
            'password' => $password,
            'role' => 'seo_manager',   // not an admin role: the admin panel opens only through the manage-seo permission
            'status' => 'active',
            'email_verified_at' => $user->email_verified_at ?: now(),
        ])->save();
        $user->syncRoles([$role]);

        $this->info(($existed ? 'Updated' : 'Created') . " SEO Manager {$email} for site #" . User::currentSiteId() . '. They can open /admin/settings/seo only.');

        return self::SUCCESS;
    }
}
