<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); $table->uuid('translation_key'); $table->string('name');
            $table->string('slug', 180); $table->string('language', 2)->default('en'); $table->timestamps();
            $table->unique(['language', 'slug']); $table->unique(['translation_key', 'language']);
        });
    }
    public function down(): void { Schema::dropIfExists('categories'); }
};
