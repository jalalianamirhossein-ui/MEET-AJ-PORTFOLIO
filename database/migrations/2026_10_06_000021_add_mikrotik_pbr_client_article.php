<?php

use App\Services\LegacyArticleImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Import only this new article; preserve any existing CMS edits.
        app(LegacyArticleImporter::class)->import(false, false, ['mikrotik-pbr-client']);
    }

    public function down(): void
    {
        // Keep published content and editorial changes on schema rollback.
    }
};
