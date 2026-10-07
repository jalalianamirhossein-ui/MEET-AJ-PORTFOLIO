<?php

use App\Services\LegacyArticleImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Use the existing bilingual importer; preserve subsequent CMS edits.
        app(LegacyArticleImporter::class)->import(false, false, ['deploy-msi-active-directory-group-policy']);
    }

    public function down(): void
    {
        // Published content remains available on schema rollback.
    }
};
