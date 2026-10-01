<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Exam;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionBannerCouponSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function postBannerSettings(User $admin, array $overrides = [])
    {
        return $this->actingAs($admin)->post(route('admin.settings.update'), array_merge([
            'active_tab' => 'promotion',
            'home_banner_active' => '1',
            'home_banner_text' => 'FLASH SALE! Use coupon BASE20 for 20% off!',
            'home_banner_coupon' => 'BASE20',
            'home_banner_discount_percent' => '20',
            'home_banner_button_text' => 'Shop Now',
            'home_banner_link' => '/vendors',
        ], $overrides));
    }

    public function test_saving_banner_with_code_and_percent_creates_a_real_working_coupon(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->postBannerSettings($admin);
        $response->assertRedirect();

        $coupon = Coupon::where('code', 'BASE20')->first();
        $this->assertNotNull($coupon, 'Saving the banner must create a real Coupon row.');
        $this->assertEquals('percentage', $coupon->discount_type);
        $this->assertEquals(20.00, (float) $coupon->discount_value);
        $this->assertTrue($coupon->is_active);
    }

    public function test_banner_synced_coupon_actually_applies_a_discount_at_checkout(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->postBannerSettings($admin, [
            'home_banner_coupon' => 'BASE30',
            'home_banner_discount_percent' => '30',
        ]);

        $vendor = Vendor::factory()->create();
        $exam = Exam::factory()->create(['vendor_id' => $vendor->id, 'price_pdf' => 100]);

        $customer = User::factory()->create(['role' => 'student']);
        session(['cart' => [
            'exam_' . $exam->id => ['id' => $exam->id, 'name' => $exam->exam_code, 'type' => 'pdf', 'price' => 100],
        ]]);

        $response = $this->actingAs($customer)->post(route('cart.coupon'), ['code' => 'BASE30']);
        $response->assertRedirect(route('cart'));
        $response->assertSessionHas('success');
        $this->assertEquals('BASE30', session('cart_coupon'));

        $coupon = Coupon::where('code', 'BASE30')->first();
        $this->assertEquals(30.00, $coupon->calculateDiscount(100.00));
    }

    public function test_arbitrary_percentages_all_work(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach ([10, 25, 40, 50, 75] as $percent) {
            $code = 'BASE' . $percent;
            $this->postBannerSettings($admin, [
                'home_banner_coupon' => $code,
                'home_banner_discount_percent' => (string) $percent,
            ]);

            $coupon = Coupon::where('code', $code)->first();
            $this->assertNotNull($coupon, "Coupon {$code} should have been created.");
            $this->assertEquals((float) $percent, (float) $coupon->discount_value);
        }
    }

    public function test_coupon_code_without_percent_is_rejected_with_validation_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->postBannerSettings($admin, ['home_banner_discount_percent' => '']);
        $response->assertSessionHasErrors(['home_banner_discount_percent']);
        $this->assertNull(Coupon::where('code', 'BASE20')->first());
    }

    public function test_disabling_banner_deactivates_the_synced_coupon(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->postBannerSettings($admin, ['home_banner_active' => '1']);
        $this->assertTrue(Coupon::where('code', 'BASE20')->first()->is_active);

        $this->postBannerSettings($admin, ['home_banner_active' => '0']);
        $this->assertFalse(Coupon::where('code', 'BASE20')->first()->is_active);
    }
}
