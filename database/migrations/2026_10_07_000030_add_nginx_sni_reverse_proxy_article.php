<?php

use App\Services\LegacyArticleImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(LegacyArticleImporter::class)->import(false, false, [
            'nginx-reverse-proxy-multiple-domains-single-ip-443',
        ]);
    }

    public function down(): void
    {
        // Preserve published article content during schema rollback.
    }
};
