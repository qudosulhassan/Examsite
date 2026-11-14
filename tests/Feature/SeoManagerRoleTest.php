<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SeoManagerRoleTest extends TestCase
{
    use RefreshDatabase;

    private function seoManager(): User
    {
        // same effect as `php artisan site:create-seo-manager`, without the interactive password prompt
        $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage-seo', 'guard_name' => 'web']);
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'SEO Manager', 'guard_name' => 'web']);
        $role->syncPermissions([$permission]);
        $user = User::factory()->create(['role' => 'seo_manager', 'status' => 'active', 'email' => 'seo@example.com']);
        $user->syncRoles([$role]);

        return $user;
    }

    public function test_seo_manager_can_use_the_technical_seo_center_and_sees_only_it(): void
    {
        $user = $this->seoManager();
        $this->assertFalse($user->isAdmin());

        $html = $this->actingAs($user)->get('/admin/settings/seo?tab=sitemap')->assertOk()->getContent();
        $this->assertStringContainsString('Technical SEO Center', $html);
        $this->assertStringNotContainsString('Subscriptions', $html);
        $this->assertStringNotContainsString('Vendor Bundles', $html);
        $this->assertStringNotContainsString('Coupons', $html);

        $this->actingAs($user)->post('/admin/settings/seo/sitemap-regenerate')->assertRedirect();
    }

    public function test_seo_manager_cannot_open_anything_else_in_the_admin(): void
    {
        $user = $this->seoManager();

        foreach (['/admin', '/admin/users', '/admin/orders', '/admin/settings', '/admin/exams', '/admin/coupons', '/admin/sites'] as $path) {
            $this->actingAs($user)->get($path)->assertRedirect('/');
        }
        $this->actingAs($user)->post('/admin/users/1/grant-access')->assertRedirect('/');
    }

    public function test_an_ordinary_customer_still_cannot_open_the_seo_center(): void
    {
        $customer = User::factory()->create(['role' => 'student']);
        $this->actingAs($customer)->get('/admin/settings/seo')->assertRedirect('/');
    }

    public function test_a_suspended_seo_manager_is_locked_out(): void
    {
        $user = $this->seoManager();
        $user->forceFill(['status' => 'suspended'])->save();
        $this->actingAs($user)->get('/admin/settings/seo')->assertRedirect();
        $this->assertNotSame(200, $this->actingAs($user)->get('/admin/settings/seo')->getStatusCode());
    }

    public function test_the_command_refuses_to_downgrade_an_existing_admin(): void
    {
        User::factory()->create(['role' => 'admin', 'email' => 'boss@example.com']);
        $this->artisan('site:create-seo-manager', ['email' => 'boss@example.com'])
            ->expectsQuestion('Password for the SEO manager (min 12 characters, not shown)', 'a-long-test-passphrase-1')
            ->expectsQuestion('Confirm password', 'a-long-test-passphrase-1')
            ->assertFailed();
        $this->assertSame('admin', User::where('email', 'boss@example.com')->value('role'));
    }

    public function test_the_command_creates_a_limited_account(): void
    {
        $this->artisan('site:create-seo-manager', ['email' => 'new.seo@example.com'])
            ->expectsQuestion('Password for the SEO manager (min 12 characters, not shown)', 'a-long-test-passphrase-1')
            ->expectsQuestion('Confirm password', 'a-long-test-passphrase-1')
            ->assertSuccessful();

        $user = User::where('email', 'new.seo@example.com')->firstOrFail();
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->can('manage-seo'));
        $this->assertSame(['manage-seo'], $user->getAllPermissions()->pluck('name')->all());
    }
}
