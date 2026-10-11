<?php

namespace Tests\Feature;

use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorDescriptionRebrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_description_is_rebranded_on_other_sites_but_raw_on_the_core_site(): void
    {
        $vendor = Vendor::factory()->create(['description' => 'ExamTopicsBase provides verified questions.']);

        config(['site.id' => 1]);
        $this->assertSame('ExamTopicsBase provides verified questions.', $vendor->fresh()->description);

        config(['site.id' => 2, 'app.name' => 'ProExamTopics']);
        $this->assertSame('ProExamTopics provides verified questions.', $vendor->fresh()->description);
    }

    public function test_stored_value_is_never_changed_by_reading_on_another_site(): void
    {
        $vendor = Vendor::factory()->create(['description' => 'ExamTopicsBase rocks.']);

        config(['site.id' => 2, 'app.name' => 'ProExamTopics']);
        $vendor->fresh()->description;

        $this->assertSame('ExamTopicsBase rocks.', \DB::table('vendors')->where('id', $vendor->id)->value('description'));
    }

    public function test_admin_pages_see_the_raw_description(): void
    {
        $vendor = Vendor::factory()->create(['description' => 'ExamTopicsBase rocks.']);
        config(['site.id' => 2, 'app.name' => 'ProExamTopics']);

        $this->get('/admin/vendors');   // sets a request whose path is under /admin
        $this->app['request'] = \Illuminate\Http\Request::create('/admin/vendors/' . $vendor->id . '/edit');

        $this->assertSame('ExamTopicsBase rocks.', $vendor->fresh()->description);
    }
}
