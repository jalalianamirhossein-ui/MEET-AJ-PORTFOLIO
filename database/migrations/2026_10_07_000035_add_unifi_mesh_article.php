<?php

use App\Services\LegacyArticleImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(LegacyArticleImporter::class)->import(false, false, [
            'ubiquiti-unifi-wireless-mesh-network',
        ]);
        $article = \App\Models\Article::where('slug', 'ubiquiti-unifi-wireless-mesh-network')->where('language', 'en')->first();
        if ($article) {
            app(\App\Services\ArticleTagAssigner::class)->syncArticle($article);
        }
    }

    public function down(): void
    {
        // Preserve editorial content on schema rollback.
    }
};
