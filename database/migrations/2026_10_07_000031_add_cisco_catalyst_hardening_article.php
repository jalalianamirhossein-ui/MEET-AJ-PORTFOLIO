<?php

use App\Services\LegacyArticleImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(LegacyArticleImporter::class)->import(false, false, [
            'cisco-catalyst-layer-2-layer-3-switch-hardening',
        ]);
    }

    public function down(): void
    {
        // Preserve editorial content on schema rollback.
    }
};
