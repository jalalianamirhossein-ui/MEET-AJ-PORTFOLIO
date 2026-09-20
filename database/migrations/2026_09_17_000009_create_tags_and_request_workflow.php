<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug', 180)->unique();
            $table->timestamps();
        });

        Schema::create('article_tag', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->primary(['article_id', 'tag_id']);
            $table->index('tag_id');
            $table->timestamps();
        });

        Schema::table('requests', function (Blueprint $table) {
            $table->text('internal_notes')->nullable()->after('status');
        });

        DB::table('requests')->where('status', 'in_progress')->update(['status' => 'in_discussion']);
        DB::table('requests')->where('status', 'resolved')->update(['status' => 'completed']);
        DB::table('requests')->where('status', 'spam')->update(['status' => 'cancelled']);
    }

    public function down(): void
    {
        DB::table('requests')->where('status', 'in_discussion')->update(['status' => 'in_progress']);
        DB::table('requests')->where('status', 'completed')->update(['status' => 'resolved']);
        DB::table('requests')->where('status', 'cancelled')->update(['status' => 'spam']);

        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('internal_notes');
        });

        Schema::dropIfExists('article_tag');
        Schema::dropIfExists('tags');
    }
};
