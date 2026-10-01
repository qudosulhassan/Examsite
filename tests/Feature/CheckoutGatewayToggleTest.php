<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutGatewayToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function seedCart(User $user): void
    {
        $vendor = Vendor::factory()->create();
        $exam = Exam::factory()->create(['vendor_id' => $vendor->id, 'price_pdf' => 29.99]);

        session(['cart' => [
            'exam_' . $exam->id => [
                'id' => $exam->id, 'name' => $exam->exam_code, 'type' => 'pdf', 'price' => 29.99,
            ],
        ]]);
    }

    public function test_disabling_paypal_hides_it_from_checkout(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->seedCart($user);

        Setting::set('payment_gateway_paypal_enabled', '0');

        $response = $this->actingAs($user)->get(route('checkout'));
        $response->assertOk();
        $response->assertDontSee('PayPal Account');
        $response->assertDontSee('paypal.com/sdk/js', false);
        $response->assertSee('Credit Card / Debit Card');
    }

    public function test_disabling_stripe_hides_it_from_checkout(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->seedCart($user);

        Setting::set('payment_gateway_stripe_enabled', '0');

        $response = $this->actingAs($user)->get(route('checkout'));
        $response->assertOk();
        $response->assertDontSee('Credit Card / Debit Card');
        $response->assertSee('PayPal Account');
    }

    public function test_both_enabled_by_default_shows_both(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->seedCart($user);

        $response = $this->actingAs($user)->get(route('checkout'));
        $response->assertOk();
        $response->assertSee('Credit Card / Debit Card');
        $response->assertSee('PayPal Account');
    }

    public function test_both_disabled_shows_unavailable_message_not_blank(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->seedCart($user);

        Setting::set('payment_gateway_stripe_enabled', '0');
        Setting::set('payment_gateway_paypal_enabled', '0');

        $response = $this->actingAs($user)->get(route('checkout'));
        $response->assertOk();
        $response->assertSee('No Payment Methods Available');
        $response->assertDontSee('Credit Card / Debit Card');
        $response->assertDontSee('PayPal Account');
    }
}
