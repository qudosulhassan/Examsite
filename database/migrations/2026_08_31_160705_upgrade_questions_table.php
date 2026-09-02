<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create Options Table
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->string('option_key');
            $table->text('option_text');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Create Answers Table
        Schema::create('question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->string('answer_value');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Create Media/Exhibits Table
        Schema::create('question_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->string('media_type')->default('image'); // image, pdf_exhibit
            $table->string('media_url');
            $table->string('caption')->nullable();
            $table->string('alt_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 4. Create References Table
        Schema::create('question_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 5. Create Import History Table
        Schema::create('import_histories', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('source_type'); // json_import, pdf_import, manual
            $table->integer('total_questions')->default(0);
            $table->integer('imported_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status')->default('completed'); // completed, failed
            $table->timestamps();
        });

        // 6. Update Questions Table structure
        Schema::table('questions', function (Blueprint $table) {
            $table->string('question_type')->default('single_choice')->after('exam_id');
            $table->text('instructions')->nullable()->after('question_text');
            $table->string('status')->default('draft')->after('is_active');
            $table->string('source_type')->default('manual')->after('status');
            $table->json('source_reference')->nullable()->after('source_type');
            $table->string('question_hash')->nullable()->after('source_reference');
            $table->json('question_data')->nullable()->after('question_hash');
        });

        // 7. Migrate Existing Question Data
        $questions = DB::table('questions')->get();
        foreach ($questions as $q) {
            // Options A-D
            $optionsMap = [
                'A' => $q->option_a ?? null,
                'B' => $q->option_b ?? null,
                'C' => $q->option_c ?? null,
                'D' => $q->option_d ?? null,
            ];

            $sortOrder = 1;
            foreach ($optionsMap as $key => $text) {
                if ($text !== null && $text !== '') {
                    DB::table('question_options')->insert([
                        'question_id' => $q->id,
                        'option_key' => $key,
                        'option_text' => $text,
                        'sort_order' => $sortOrder++,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Answers (parse CSV list, e.g., "A,B")
            if (!empty($q->correct_option)) {
                $correctOptions = array_map('trim', explode(',', $q->correct_option));
                $ansSortOrder = 1;
                foreach ($correctOptions as $optionVal) {
                    if ($optionVal !== '') {
                        DB::table('question_answers')->insert([
                            'question_id' => $q->id,
                            'answer_value' => $optionVal,
                            'sort_order' => $ansSortOrder++,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Media
            if (!empty($q->image_filename)) {
                DB::table('question_media')->insert([
                    'question_id' => $q->id,
                    'media_type' => 'image',
                    'media_url' => '/storage/questions/' . $q->image_filename,
                    'caption' => 'Exhibit',
                    'alt_text' => 'Exhibit',
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Question Hash & Status
            $normalized = preg_replace('/\s+/', ' ', strtolower(trim($q->question_text)));
            $hash = md5($normalized);
            
            // Check if multiple correct answers were set to dynamically set correct question_type
            $questionType = 'single_choice';
            if (!empty($q->correct_option) && strpos($q->correct_option, ',') !== false) {
                $questionType = 'multiple_choice';
            }

            DB::table('questions')->where('id', $q->id)->update([
                'question_type' => $questionType,
                'question_hash' => $hash,
                'status' => $q->is_active ? 'published' : 'draft',
            ]);
        }

        // 8. Clean up old columns from Questions table
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'option_a',
                'option_b',
                'option_c',
                'option_d',
                'correct_option',
                'image_filename'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore old columns to questions table
        Schema::table('questions', function (Blueprint $table) {
            $table->text('option_a')->nullable();
            $table->text('option_b')->nullable();
            $table->text('option_c')->nullable();
            $table->text('option_d')->nullable();
            $table->string('correct_option')->nullable();
            $table->string('image_filename')->nullable();
        });

        // Restore migrated data back into questions columns
        $questions = DB::table('questions')->get();
        foreach ($questions as $q) {
            $optionA = DB::table('question_options')->where('question_id', $q->id)->where('option_key', 'A')->value('option_text');
            $optionB = DB::table('question_options')->where('question_id', $q->id)->where('option_key', 'B')->value('option_text');
            $optionC = DB::table('question_options')->where('question_id', $q->id)->where('option_key', 'C')->value('option_text');
            $optionD = DB::table('question_options')->where('question_id', $q->id)->where('option_key', 'D')->value('option_text');
            $correctOption = DB::table('question_answers')->where('question_id', $q->id)->orderBy('sort_order')->pluck('answer_value')->implode(',');
            
            $imageFilename = null;
            $mediaUrl = DB::table('question_media')->where('question_id', $q->id)->where('media_type', 'image')->value('media_url');
            if ($mediaUrl) {
                $imageFilename = basename($mediaUrl);
            }

            DB::table('questions')->where('id', $q->id)->update([
                'option_a' => $optionA,
                'option_b' => $optionB,
                'option_c' => $optionC,
                'option_d' => $optionD,
                'correct_option' => $correctOption,
                'image_filename' => $imageFilename,
            ]);
        }

        // Drop new tables and columns
        Schema::dropIfExists('import_histories');
        Schema::dropIfExists('question_references');
        Schema::dropIfExists('question_media');
        Schema::dropIfExists('question_answers');
        Schema::dropIfExists('question_options');

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'question_type',
                'instructions',
                'status',
                'source_type',
                'source_reference',
                'question_hash',
                'question_data'
            ]);
        });
    }
};
