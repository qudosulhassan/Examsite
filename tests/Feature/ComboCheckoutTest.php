<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Exam;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserExam;

class ComboCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_combo_checkout_creates_order_item_and_grants_dual_access()
    {
        $vendor = Vendor::create(['name' => 'Microsoft', 'slug' => 'microsoft', 'is_active' => true]);
        $exam = Exam::create([
            'vendor_id' => $vendor->id,
            'exam_code' => 'AZ-104',
            'exam_name' => 'Azure Administrator',
            'slug' => 'az-104',
            'price_pdf' => 29.95,
            'price_engine' => 29.95,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        // 1. Add Combo to cart
        $response = $this->actingAs($user)->post(route('cart.add'), [
            'type' => 'combo',
            'exam_id' => $exam->id,
        ]);

        $response->assertRedirect(route('cart'));
        $response->assertSessionHas('cart');

        // 2. Create 100% off coupon for free checkout testing
        $coupon = Coupon::create([
            'code' => 'FREECOMBO',
            'discount_type' => 'percentage',
            'discount_value' => 100,
            'is_active' => true,
        ]);

        session()->put('cart_coupon', 'FREECOMBO');

        // 3. Complete free checkout
        $checkoutResponse = $this->actingAs($user)->post(route('checkout.free'));

        $checkoutResponse->assertRedirect(route('checkout.success'));

        // 4. Assert Order and OrderItem were created properly
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_status' => 'paid',
        ]);

        $order = Order::where('user_id', $user->id)->first();

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'exam_id' => $exam->id,
            'item_type' => 'combo',
        ]);

        // 5. Assert dual UserExam access granted (PDF + Test Engine)
        $this->assertDatabaseHas('user_exams', [
            'user_id' => $user->id,
            'exam_id' => $exam->id,
            'access_type' => 'pdf',
        ]);

        $this->assertDatabaseHas('user_exams', [
            'user_id' => $user->id,
            'exam_id' => $exam->id,
            'access_type' => 'engine',
        ]);
    }
}
