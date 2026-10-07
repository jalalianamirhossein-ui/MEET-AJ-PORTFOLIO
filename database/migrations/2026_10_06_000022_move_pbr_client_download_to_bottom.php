<?php

use App\Services\LegacyArticleImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(LegacyArticleImporter::class)->import(false, true, ['mikrotik-pbr-client']);
    }

    public function down(): void
    {
        // Preserve the requested article layout and editorial content.
    }
};
