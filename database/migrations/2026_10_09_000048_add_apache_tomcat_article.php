<?php

use App\Services\LegacyArticleImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(LegacyArticleImporter::class)->import(false, false, [
            'apache-tomcat-linux-installation-security-hardening',
        ]);
    }

    public function down(): void
    {
        // Preserve editorial content during schema rollback.
    }
};
