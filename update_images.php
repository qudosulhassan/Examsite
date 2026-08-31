<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (\App\Models\BlogPost::all() as $post) {
    $post->update(['featured_image' => 'https://picsum.photos/seed/'.$post->id.'/800/500']);
}
echo "Done!\n";
