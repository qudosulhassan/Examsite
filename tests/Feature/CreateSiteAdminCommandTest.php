<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateSiteAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_an_admin_for_the_current_site_only(): void
    {
        config(['site.id' => 2, 'site.mode' => 'standalone']);

        $this->artisan('site:create-admin', ['email' => 'Boss@Example.com'])
            ->expectsQuestion('Password (min 12 characters, not shown)', 'a-long-secret-123')
            ->expectsQuestion('Confirm password', 'a-long-secret-123')
            ->assertSuccessful();

        $user = User::withoutGlobalScope('site')->where('email', 'boss@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(2, (int) $user->site_id);
        $this->assertTrue($user->isAdmin());
        $this->assertTrue(Hash::check('a-long-secret-123', $user->password));

        config(['site.id' => 1]);
        $this->assertNull(User::where('email', 'boss@example.com')->first());
    }

    public function test_rejects_short_or_mismatched_passwords(): void
    {
        $this->artisan('site:create-admin', ['email' => 'a@example.com'])
            ->expectsQuestion('Password (min 12 characters, not shown)', 'short')
            ->assertFailed();

        $this->artisan('site:create-admin', ['email' => 'a@example.com'])
            ->expectsQuestion('Password (min 12 characters, not shown)', 'a-long-secret-123')
            ->expectsQuestion('Confirm password', 'different-secret-123')
            ->assertFailed();

        $this->assertSame(0, User::withoutGlobalScope('site')->count());
    }
}
