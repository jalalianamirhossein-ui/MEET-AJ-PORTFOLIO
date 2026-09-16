<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('article_redirects', function (Blueprint $table) {
            $table->id(); $table->string('old_path', 255)->unique();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('article_redirects'); }
};
