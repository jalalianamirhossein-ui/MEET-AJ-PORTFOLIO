<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Reuse the repeatable path-only migration with the finalized mappings.
        // This covers installations that already ran the first image migration.
        $migration = require __DIR__.'/2026_10_06_000023_organize_image_paths.php';
        $migration->up();
    }

    public function down(): void
    {
        // Retain valid image paths; removed duplicate assets are not restored.
    }
};
