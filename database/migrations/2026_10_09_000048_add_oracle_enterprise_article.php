<?php

use App\Services\LegacyArticleImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(LegacyArticleImporter::class)->import(false, false, [
            'oracle-database-26ai-installation-oracle-linux',
        ]);
    }

    public function down(): void
    {
        // Preserve editorial content when rolling back schema migrations.
    }
};
