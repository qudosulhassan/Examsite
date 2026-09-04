<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Exam;
use App\Models\Certification;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserExam;

class ExamPricingAndCertificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@examsninja.com',
            'role' => 'admin',
        ]);

        $this->regularUser = User::factory()->create([
            'email' => 'student@examsninja.com',
            'role' => 'customer',
        ]);

        $this->vendor = Vendor::create([
            'name' => 'Cisco Systems',
            'slug' => 'cisco',
            'is_active' => true,
        ]);
    }

    /**
     * 1. Certification Modal Creation via AJAX
     */
    public function test_admin_can_create_certification_via_ajax_modal(): void
    {
        $payload = [
            'vendor_id' => $this->vendor->id,
            'name' => 'Cisco Certified DevNet Associate',
            'code' => '200-901',
            'description' => 'Software development and automation certification.',
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.certifications.store'), $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'certification' => [
                    'name' => 'Cisco Certified DevNet Associate',
                    'code' => '200-901',
                    'vendor_id' => $this->vendor->id,
                ],
            ]);

        $this->assertDatabaseHas('certifications', [
            'vendor_id' => $this->vendor->id,
            'name' => 'Cisco Certified DevNet Associate',
            'code' => '200-901',
            'slug' => 'cisco-certified-devnet-associate',
        ]);
    }

    /**
     * 2. Certification Duplicate Detection returns 422 with existing certification details
     */
    public function test_certification_duplicate_detection_returns_422_with_existing_cert_details(): void
    {
        $existing = Certification::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'CCNA Routing and Switching',
            'code' => '200-301',
            'slug' => 'ccna-routing-and-switching',
            'is_active' => true,
        ]);

        // Attempt to create duplicate with same vendor and name
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.certifications.store'), [
                'vendor_id' => $this->vendor->id,
                'name' => 'CCNA Routing and Switching',
                'code' => '200-301',
                'is_active' => 1,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'is_duplicate' => true,
                'existing_certification' => [
                    'id' => $existing->id,
                    'name' => 'CCNA Routing and Switching',
                    'code' => '200-301',
                    'vendor_id' => $this->vendor->id,
                ],
            ]);
    }

    /**
     * 3. Exam Creation with 3 Product Offerings and Bundle Price
     */
    public function test_admin_can_create_exam_with_3_product_offerings_and_independent_bundle_price(): void
    {
        $cert = Certification::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Cisco CyberOps Associate',
            'code' => '200-201',
            'slug' => 'cisco-cyberops-associate',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.exams.store'), [
            'vendor_id' => $this->vendor->id,
            'exam_code' => '200-201',
            'exam_name' => 'Understanding Cisco Cybersecurity Operations Fundamentals',
            'header_title' => 'Cisco 200-201 CBROPS Prep',
            'slug' => '200-201',
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'passing_score' => 75,
            'availability_configured' => '1',
            'is_pdf_available' => '1',
            'is_engine_available' => '1',
            'is_bundle_available' => '1',
            'price_pdf' => '29.99',
            'price_engine' => '39.99',
            'price_bundle' => '54.99',
            'certifications' => [$cert->id],
            'is_active' => '1',
            'action' => 'publish',
        ]);

        $response->assertSessionHasNoErrors();

        $exam = Exam::where('exam_code', '200-201')->first();
        $this->assertNotNull($exam);
        $this->assertTrue((bool)$exam->is_pdf_available);
        $this->assertTrue((bool)$exam->is_engine_available);
        $this->assertTrue((bool)$exam->is_bundle_available);
        $this->assertEquals(29.99, (float)$exam->price_pdf);
        $this->assertEquals(39.99, (float)$exam->price_engine);
        $this->assertEquals(54.99, (float)$exam->price_bundle);
        $this->assertEquals(54.99, (float)$exam->effective_bundle_price);
        $this->assertTrue($exam->certifications->contains($cert->id));
    }

    /**
     * 4. Exam Validation: At least one purchase option must be enabled
     */
    public function test_exam_creation_fails_if_all_three_product_options_are_disabled(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.exams.store'), [
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'DISABLE-ALL',
            'exam_name' => 'Disabled Options Exam',
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'availability_configured' => '1',
            'is_pdf_available' => '0',
            'is_engine_available' => '0',
            'is_bundle_available' => '0',
            'price_pdf' => '0.00',
            'price_engine' => '0.00',
            'price_bundle' => '0.00',
        ]);

        $response->assertSessionHasErrors('product_availability');
        $this->assertNull(Exam::where('exam_code', 'DISABLE-ALL')->first());
    }

    /**
     * 5. Exam Validation: Enabled products require valid price >= 0
     */
    public function test_exam_creation_fails_if_enabled_product_price_is_invalid(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.exams.store'), [
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'INVALID-PRICE',
            'exam_name' => 'Invalid Price Exam',
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'availability_configured' => '1',
            'is_pdf_available' => '1',
            'is_engine_available' => '0',
            'is_bundle_available' => '0',
            'price_pdf' => '-10.00', // Negative price
        ]);

        $response->assertSessionHasErrors('price_pdf');
    }

    /**
     * 6. Exam Edit: Updating product availability and prices
     */
    public function test_admin_can_update_exam_to_pdf_only_offering(): void
    {
        $exam = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => '300-410',
            'exam_name' => 'Implementing Cisco Enterprise Advanced Routing and Services',
            'slug' => '300-410',
            'price_pdf' => 35.00,
            'price_engine' => 45.00,
            'price_bundle' => 65.00,
            'is_pdf_available' => true,
            'is_engine_available' => true,
            'is_bundle_available' => true,
            'passing_score' => 70,
            'difficulty' => 'Professional',
            'exam_type' => 'MultipleChoice',
            'is_active' => true,
        ]);

        // Disable Simulator and Bundle, leaving PDF only
        $response = $this->actingAs($this->adminUser)->put(route('admin.exams.update', $exam->id), [
            'vendor_id' => $this->vendor->id,
            'exam_code' => '300-410',
            'exam_name' => 'Implementing Cisco Enterprise Advanced Routing and Services',
            'slug' => '300-410',
            'difficulty' => 'Professional',
            'exam_type' => 'MultipleChoice',
            'passing_score' => 70,
            'availability_configured' => '1',
            'is_pdf_available' => '1',
            'price_pdf' => '39.00',
            'is_engine_available' => '0',
            'is_bundle_available' => '0',
            'is_active' => '1',
            'action' => 'publish',
        ]);

        $response->assertSessionHasNoErrors();

        $exam->refresh();
        $this->assertTrue((bool)$exam->is_pdf_available);
        $this->assertFalse((bool)$exam->is_engine_available);
        $this->assertFalse((bool)$exam->is_bundle_available);
        $this->assertEquals(39.00, (float)$exam->price_pdf);
    }

    /**
     * 7. Public Exam Page: displays active options and respects availability
     */
    public function test_public_exam_page_displays_only_active_product_offerings(): void
    {
        $exam = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'PDF-ONLY-EXAM',
            'exam_name' => 'PDF Only Study Guide Exam',
            'slug' => 'pdf-only-exam',
            'price_pdf' => 29.00,
            'price_engine' => 39.00,
            'price_bundle' => null,
            'is_pdf_available' => true,
            'is_engine_available' => false,
            'is_bundle_available' => false,
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'is_active' => true,
        ]);

        $response = $this->get(route('exams.show', ['vendor' => $this->vendor->slug, 'slug' => $exam->slug]));

        $response->assertStatus(200);
        $response->assertSee('PDF Guide', false);
        $response->assertSee('$29.00', false);
        // Simulator only and Bundle purchase forms should NOT be offered
        $response->assertDontSee('Select Engine', false);
        $response->assertDontSee('PDF + Test Engine Bundle', false);
    }

    /**
     * 8. Cart Protection: Adding a disabled product is blocked
     */
    public function test_cart_blocks_adding_disabled_product(): void
    {
        $exam = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'NO-ENGINE-EXAM',
            'exam_name' => 'No Engine Available Exam',
            'slug' => 'no-engine-exam',
            'price_pdf' => 25.00,
            'price_engine' => 35.00,
            'price_bundle' => 45.00,
            'is_pdf_available' => true,
            'is_engine_available' => false, // Engine disabled
            'is_bundle_available' => true,
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'is_active' => true,
        ]);

        // Attempt to add disabled engine to cart
        $response = $this->actingAs($this->regularUser)->post(route('cart.add'), [
            'type' => 'engine',
            'exam_id' => $exam->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('currently not available', session('error'));
    }

    /**
     * 9. Cart: Adding Bundle applies the custom bundle price
     */
    public function test_cart_applies_configured_bundle_price(): void
    {
        $exam = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'BUNDLE-PRICE-EXAM',
            'exam_name' => 'Bundle Price Test Exam',
            'slug' => 'bundle-price-exam',
            'price_pdf' => 30.00,
            'price_engine' => 40.00,
            'price_bundle' => 49.00, // Explicit custom bundle price
            'is_pdf_available' => true,
            'is_engine_available' => true,
            'is_bundle_available' => true,
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->regularUser)->post(route('cart.add'), [
            'type' => 'combo',
            'exam_id' => $exam->id,
        ]);

        $response->assertRedirect(route('cart'));
        $cart = session('cart');
        $this->assertNotEmpty($cart);
        
        $item = array_values($cart)[0];
        $this->assertEquals(49.00, (float)$item['price']);
    }

    /**
     * 10. Checkout: Combo item grants dual access (PDF + Engine) in database
     */
    public function test_combo_checkout_grants_dual_customer_access(): void
    {
        $exam = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'DUAL-ACCESS-EXAM',
            'exam_name' => 'Dual Access Test Exam',
            'slug' => 'dual-access-exam',
            'price_pdf' => 30.00,
            'price_engine' => 40.00,
            'price_bundle' => 55.00,
            'is_pdf_available' => true,
            'is_engine_available' => true,
            'is_bundle_available' => true,
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'is_active' => true,
        ]);

        // Add combo to cart
        $this->actingAs($this->regularUser)->post(route('cart.add'), [
            'type' => 'combo',
            'exam_id' => $exam->id,
        ]);

        // Apply 100% coupon for automated testing
        Coupon::create([
            'code' => 'DUALFREE',
            'discount_type' => 'percentage',
            'discount_value' => 100,
            'is_active' => true,
        ]);
        session()->put('cart_coupon', 'DUALFREE');

        // Complete free checkout
        $checkoutResponse = $this->actingAs($this->regularUser)->post(route('checkout.free'));
        $checkoutResponse->assertRedirect(route('checkout.success'));

        // Assert Order and OrderItem
        $order = Order::where('user_id', $this->regularUser->id)->first();
        $this->assertNotNull($order);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'exam_id' => $exam->id,
            'item_type' => 'combo',
        ]);

        // Assert dual UserExam records
        $this->assertDatabaseHas('user_exams', [
            'user_id' => $this->regularUser->id,
            'exam_id' => $exam->id,
            'access_type' => 'pdf',
        ]);

        $this->assertDatabaseHas('user_exams', [
            'user_id' => $this->regularUser->id,
            'exam_id' => $exam->id,
            'access_type' => 'engine',
        ]);
    }
}