<?php

use App\Services\LegacyArticleImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Scoped import; preserve existing CMS edits on subsequent deployments.
        app(LegacyArticleImporter::class)->import(false, false, [
            '10-essential-group-policies-windows-domain',
        ]);
    }

    public function down(): void
    {
        // Preserve editorial content during schema rollback.
    }
};
