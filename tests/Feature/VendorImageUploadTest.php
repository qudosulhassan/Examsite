<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vendor;

class VendorImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@examtopicsbase.com',
            'role' => 'admin',
        ]);
    }

    public function test_can_upload_vendor_image_on_create()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('microsoft_logo.png', 200, 200);

        $response = $this->actingAs($this->adminUser)->post(route('admin.vendors.store'), [
            'name' => 'Microsoft Azure',
            'sort_order' => 1,
            'description' => 'Microsoft vendor description',
            'is_active' => '1',
            'logo' => $file,
        ]);

        $response->assertRedirect(route('admin.vendors.index'));

        $vendor = Vendor::where('name', 'Microsoft Azure')->first();
        $this->assertNotNull($vendor);
        $this->assertNotNull($vendor->logo_path);
        $this->assertStringStartsWith('/storage/vendors/', $vendor->logo_path);
        $this->assertStringContainsString('/storage/vendors/', $vendor->logo_url);

        $storagePath = str_replace('/storage/', '', $vendor->logo_path);
        Storage::disk('public')->assertExists($storagePath);
    }

    public function test_can_upload_vendor_image_on_update()
    {
        Storage::fake('public');

        $vendor = Vendor::create([
            'name' => 'Cisco Systems',
            'slug' => 'cisco-systems',
            'sort_order' => 2,
            'description' => 'Cisco networking',
            'is_active' => true,
        ]);

        $file = UploadedFile::fake()->image('cisco_logo.png', 300, 300);

        $response = $this->actingAs($this->adminUser)->put(route('admin.vendors.update', $vendor->id), [
            'name' => 'Cisco Systems Updated',
            'sort_order' => 2,
            'description' => 'Cisco networking updated',
            'is_active' => '1',
            'logo' => $file,
        ]);

        $response->assertRedirect(route('admin.vendors.index'));

        $vendor->refresh();
        $this->assertNotNull($vendor->logo_path);
        $this->assertStringStartsWith('/storage/vendors/', $vendor->logo_path);

        $storagePath = str_replace('/storage/', '', $vendor->logo_path);
        Storage::disk('public')->assertExists($storagePath);
    }

    public function test_can_remove_vendor_image_on_update()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('old_logo.png');
        $storedPath = $file->store('vendors', 'public');

        $vendor = Vendor::create([
            'name' => 'Oracle',
            'slug' => 'oracle',
            'logo_path' => '/storage/' . $storedPath,
            'sort_order' => 3,
            'is_active' => true,
        ]);

        Storage::disk('public')->assertExists($storedPath);

        $response = $this->actingAs($this->adminUser)->put(route('admin.vendors.update', $vendor->id), [
            'name' => 'Oracle',
            'sort_order' => 3,
            'is_active' => '1',
            'remove_logo' => '1',
        ]);

        $response->assertRedirect(route('admin.vendors.index'));

        $vendor->refresh();
        $this->assertNull($vendor->logo_path);
        Storage::disk('public')->assertMissing($storedPath);
    }

    public function test_vendor_edit_page_renders_logo_upload_field()
    {
        $vendor = Vendor::create([
            'name' => 'CompTIA',
            'slug' => 'comptia',
            'sort_order' => 4,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.vendors.edit', $vendor->id));
        $response->assertStatus(200);
        $response->assertSee('Vendor Image / Logo');
        $response->assertSee('name="logo"', false);
        $response->assertSee('enctype="multipart/form-data"', false);
    }
}
