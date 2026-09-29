<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Exam;
use App\Models\Package;
use App\Models\User;
use App\Models\UserExam;
use App\Models\UserPackage;
use App\Models\Vendor;
use App\Services\AccessManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorPackageAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function makeVendorPackage(Vendor $vendor, array $overrides = []): Package
    {
        return Package::create(array_merge([
            'vendor_id' => $vendor->id,
            'type' => 'bundle',
            'name' => $vendor->name . ' Ultimate Package',
            'slug' => \Illuminate\Support\Str::slug($vendor->name) . '-ultimate-' . uniqid(),
            'description' => 'Test package',
            'price_lifetime' => 199.99,
            'update_price_3_months' => 0,
            'update_price_6_months' => 29.99,
            'update_price_12_months' => 49.99,
            'license_price_individual' => 0,
            'license_price_corporate' => 149.99,
            'license_price_trainer' => 299.99,
            'features' => ['All Exam PDFs', 'All Testing Engines'],
            'is_popular' => true,
            'sort_order' => 1,
            'is_active' => true,
            'includes_pdf' => true,
            'includes_te' => true,
            'access_days' => null,
        ], $overrides));
    }

    public function test_package_purchase_creates_order_with_correct_package_id_not_exam_id(): void
    {
        $vendor = Vendor::factory()->create();
        $package = $this->makeVendorPackage($vendor);
        $user = User::factory()->create(['role' => 'student']);
        $coupon = Coupon::create([
            'code' => 'FREETEST100',
            'discount_type' => 'percentage',
            'discount_value' => 100,
            'is_active' => true,
            'max_uses' => null,
            'used_count' => 0,
        ]);

        session(['cart' => [
            'pkg_' . $package->id => [
                'id' => $package->id, 'key' => 'pkg_' . $package->id, 'name' => $package->name,
                'type' => 'package', 'package_type' => 'bundle', 'price' => 199.99,
                'update_period' => '6', 'license_type' => 'trainer',
            ],
        ], 'cart_coupon' => $coupon->code]);

        $response = $this->actingAs($user)->post(route('checkout.free'));
        $response->assertRedirect(route('checkout.success'));

        $order = \App\Models\Order::where('user_id', $user->id)->latest()->first();
        $this->assertNotNull($order);

        $item = $order->items()->first();
        $this->assertEquals('package', $item->item_type);
        $this->assertNull($item->exam_id, 'exam_id must NOT hold the package id.');
        $this->assertEquals($package->id, $item->package_id, 'package_id must hold the actual package id.');
        $this->assertEquals($package->name, $item->plan_name, 'plan_name must show the package name for order history.');

        $userPackage = UserPackage::where('user_id', $user->id)->where('package_id', $package->id)->first();
        $this->assertNotNull($userPackage);
        $this->assertEquals('active', $userPackage->status);
        $this->assertNull($userPackage->expires_at, 'null access_days must mean lifetime (null expires_at).');
    }

    public function test_access_manager_grants_pdf_and_te_for_vendor_package_but_not_other_vendors(): void
    {
        $vendorA = Vendor::factory()->create();
        $vendorB = Vendor::factory()->create();
        $examA = Exam::factory()->create(['vendor_id' => $vendorA->id, 'is_active' => true]);
        $examB = Exam::factory()->create(['vendor_id' => $vendorB->id, 'is_active' => true]);

        $package = $this->makeVendorPackage($vendorA);
        $user = User::factory()->create(['role' => 'student']);
        UserPackage::create([
            'user_id' => $user->id, 'package_id' => $package->id, 'order_id' => null,
            'status' => 'active', 'purchased_at' => now(), 'expires_at' => null,
        ]);

        $access = app(AccessManager::class);

        $this->assertTrue($access->canAccessPdf($user, $examA));
        $this->assertTrue($access->canAccessTestEngine($user, $examA));
        $this->assertFalse($access->canAccessPdf($user, $examB));
        $this->assertFalse($access->canAccessTestEngine($user, $examB));
    }

    public function test_pdf_only_single_exam_purchase_does_not_grant_test_engine_access(): void
    {
        // Regression guard for the AccessManager looseness that was fixed:
        // a PDF-only purchase must never unlock the Test Engine for that exam.
        $vendor = Vendor::factory()->create();
        $exam = Exam::factory()->create(['vendor_id' => $vendor->id, 'is_active' => true]);
        $user = User::factory()->create(['role' => 'student']);

        UserExam::create([
            'user_id' => $user->id, 'exam_id' => $exam->id, 'access_type' => 'pdf',
            'download_count' => 0, 'max_downloads' => 3, 'purchased_at' => now(),
        ]);

        $access = app(AccessManager::class);
        $this->assertTrue($access->canAccessPdf($user, $exam));
        $this->assertFalse($access->canAccessTestEngine($user, $exam));
    }

    public function test_my_exams_dashboard_materializes_package_pdf_access(): void
    {
        $vendor = Vendor::factory()->create();
        $examA = Exam::factory()->create(['vendor_id' => $vendor->id, 'is_active' => true]);
        $examB = Exam::factory()->create(['vendor_id' => $vendor->id, 'is_active' => true]);

        $package = $this->makeVendorPackage($vendor);
        $user = User::factory()->create(['role' => 'student']);
        UserPackage::create([
            'user_id' => $user->id, 'package_id' => $package->id, 'order_id' => null,
            'status' => 'active', 'purchased_at' => now(), 'expires_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.my-exams'));
        $response->assertOk();
        $response->assertSee($examA->exam_code);
        $response->assertSee($examB->exam_code);

        $this->assertDatabaseHas('user_exams', [
            'user_id' => $user->id, 'exam_id' => $examA->id, 'access_type' => 'pdf',
        ]);
        $this->assertDatabaseHas('user_exams', [
            'user_id' => $user->id, 'exam_id' => $examB->id, 'access_type' => 'pdf',
        ]);
    }

    public function test_test_engine_dashboard_lists_package_covered_exams_and_lobby_access_works(): void
    {
        $vendor = Vendor::factory()->create();
        $exam = Exam::factory()->create(['vendor_id' => $vendor->id, 'is_active' => true]);

        $package = $this->makeVendorPackage($vendor);
        $user = User::factory()->create(['role' => 'student']);
        UserPackage::create([
            'user_id' => $user->id, 'package_id' => $package->id, 'order_id' => null,
            'status' => 'active', 'purchased_at' => now(), 'expires_at' => null,
        ]);

        $list = $this->actingAs($user)->get(route('dashboard.test-engine'));
        $list->assertOk();
        $list->assertSee($exam->exam_code);

        $lobby = $this->actingAs($user)->get(route('dashboard.test-engine.lobby', $exam->slug));
        $lobby->assertOk();
    }

    public function test_package_without_te_flag_does_not_grant_test_engine_access(): void
    {
        $vendor = Vendor::factory()->create();
        $exam = Exam::factory()->create(['vendor_id' => $vendor->id, 'is_active' => true]);

        $package = $this->makeVendorPackage($vendor, ['includes_te' => false]);
        $user = User::factory()->create(['role' => 'student']);
        UserPackage::create([
            'user_id' => $user->id, 'package_id' => $package->id, 'order_id' => null,
            'status' => 'active', 'purchased_at' => now(), 'expires_at' => null,
        ]);

        $lobby = $this->actingAs($user)->get(route('dashboard.test-engine.lobby', $exam->slug));
        $lobby->assertRedirect(route('dashboard.test-engine'));
    }
}
