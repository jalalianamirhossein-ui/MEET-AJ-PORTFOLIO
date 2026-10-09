<?php

use App\Services\LegacyArticleImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(LegacyArticleImporter::class)->import(false, false, [
            'zabbix-server-linux-windows-agents-backup',
        ]);
    }

    public function down(): void
    {
        // Preserve editorial content during schema rollback.
    }
};
