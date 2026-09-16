<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('articles', function (Blueprint $table) {
            $table->id(); $table->uuid('translation_key'); $table->string('title');
            $table->string('slug', 180); $table->string('language', 2)->default('en');
            $table->text('excerpt')->nullable(); $table->longText('content'); $table->string('featured_image', 2048)->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('meta_title')->nullable(); $table->text('meta_description')->nullable();
            $table->string('canonical_url', 2048)->nullable(); $table->json('seo_data')->nullable();
            $table->json('presentation')->nullable(); $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('draft'); $table->timestamp('published_at')->nullable(); $table->timestamps();
            $table->unique(['language', 'slug']); $table->unique(['translation_key', 'language']);
            $table->index(['language', 'status', 'published_at']); $table->index(['status', 'published_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('articles'); }
};
