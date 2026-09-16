<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->uuid('translation_key');
            $table->string('title');
            $table->string('slug', 180);
            $table->string('language', 2)->default('en');
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->json('features')->nullable();
            $table->json('process')->nullable();
            $table->json('faq')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('price_currency', 8)->default('AED');
            $table->string('price_label')->nullable();
            $table->string('price_type', 32)->default('fixed');
            $table->string('featured_image', 2048)->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->json('presentation')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['language', 'slug']);
            $table->unique(['translation_key', 'language']);
            $table->index(['language', 'status', 'published_at']);
            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
