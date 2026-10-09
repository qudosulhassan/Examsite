<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteScopedCustomersTest extends TestCase
{
    use RefreshDatabase;

    private function asSite(int $id): void
    {
        config(['site.id' => $id, 'site.mode' => 'standalone']);
    }

    public function test_new_users_get_the_current_site_id(): void
    {
        $this->asSite(2);
        $user = User::factory()->create();

        $this->assertSame(2, (int) $user->site_id);
    }

    public function test_same_email_can_exist_on_both_sites(): void
    {
        $this->asSite(1);
        User::factory()->create(['email' => 'same@example.com']);
        $this->asSite(2);
        User::factory()->create(['email' => 'same@example.com']);

        $this->assertSame(1, User::where('email', 'same@example.com')->count());
        $this->asSite(1);
        $this->assertSame(1, User::where('email', 'same@example.com')->count());
        $this->assertSame(2, User::withoutGlobalScope('site')->where('email', 'same@example.com')->count());
    }

    public function test_site_one_customer_cannot_log_in_on_site_two(): void
    {
        $this->asSite(1);
        User::factory()->create(['email' => 'only1@example.com', 'password' => 'secret-pass-123']);

        $this->asSite(2);
        $this->post('/login', ['email' => 'only1@example.com', 'password' => 'secret-pass-123']);
        $this->assertGuest();

        $this->asSite(1);
        $this->post('/login', ['email' => 'only1@example.com', 'password' => 'secret-pass-123']);
        $this->assertAuthenticated();
    }

    public function test_registration_on_site_two_creates_a_site_two_user_even_if_email_exists_on_site_one(): void
    {
        $this->asSite(1);
        User::factory()->create(['email' => 'dup@example.com']);

        $this->asSite(2);
        $this->post('/register', [
            'name' => 'Dup Person',
            'email' => 'dup@example.com',
            'password' => 'password-12345',
            'password_confirmation' => 'password-12345',
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, User::withoutGlobalScope('site')->where('email', 'dup@example.com')->count());
        $this->assertSame(2, (int) User::first()->site_id);
    }

    public function test_coupons_are_isolated_and_codes_can_repeat_across_sites(): void
    {
        $base = ['discount_type' => 'percentage', 'discount_value' => 10, 'min_order_amount' => 0, 'per_user_limit' => 1, 'is_active' => true];
        $this->asSite(1);
        Coupon::create($base + ['code' => 'SAVE10']);
        $this->asSite(2);
        $this->assertNull(Coupon::where('code', 'SAVE10')->first());
        Coupon::create($base + ['code' => 'SAVE10']);

        $this->assertSame(1, Coupon::count());
        $this->assertSame(2, Coupon::withoutGlobalScope('site')->count());
    }
}
