<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'slug')) {
                $table->string('slug', 100)->nullable()->unique()->after('name');
            }
        });

        // Backfill slugs for all existing users
        $users = User::withTrashed()->whereNull('slug')->orWhere('slug', '')->get();
        foreach ($users as $user) {
            $name = trim($user->name ?: '');
            if (empty($name)) {
                $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            }
            if (empty($name) && !empty($user->email)) {
                $name = explode('@', $user->email)[0];
            }
            $baseSlug = Str::slug($name ?: ('user-' . $user->id));
            if (empty($baseSlug)) {
                $baseSlug = 'user-' . $user->id;
            }

            $slug = $baseSlug;
            $counter = 1;
            while (User::withTrashed()->where('slug', $slug)->where('id', '!=', $user->id)->exists()) {
                $counter++;
                $slug = "{$baseSlug}-{$counter}";
            }

            $user->updateQuietly(['slug' => $slug]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
