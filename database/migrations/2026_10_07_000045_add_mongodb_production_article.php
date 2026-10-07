<?php

use App\Services\LegacyArticleImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(LegacyArticleImporter::class)->import(false, false, [
            'mongodb-installation-configuration-production-deployment',
        ]);
    }

    public function down(): void
    {
        // Preserve authored content during schema rollback.
    }
};
