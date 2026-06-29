<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$vendor = \App\Models\Vendor::first();
if ($vendor) {
    \App\Models\Package::create([
        'vendor_id' => $vendor->id,
        'type' => 'bundle',
        'name' => $vendor->name . ' Ultimate Package',
        'slug' => 'test-vendor-ultimate',
        'price_lifetime' => 199.99,
        'includes_pdf' => true,
        'includes_te' => true,
        'is_active' => true,
        'sort_order' => 1,
        'features' => ['All PDFs', 'All Test Engines']
    ]);
    echo "Created package for vendor: " . $vendor->name;
}
