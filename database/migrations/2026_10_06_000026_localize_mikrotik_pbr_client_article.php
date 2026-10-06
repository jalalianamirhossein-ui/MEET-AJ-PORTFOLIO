<?php

use App\Models\Article;
use App\Services\LegacyArticleImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $article = Article::where('language', 'en')->where('slug', 'mikrotik-pbr-client')->first();
        if (! $article) {
            app(LegacyArticleImporter::class)->import(false, false, ['mikrotik-pbr-client']);

            return;
        }

        // Upgrade only the original imported body. Preserve any CMS edits.
        $originalBodyHashes = [
            '8946c361a41c026d162f31deee51f731942e8661cb2fcc9eccc500b34102350e',
            // Earlier published source used the banner as its inline image.
            'd25d23957bd649f96219523061c47bcf0206ecf8be9200496251441bdc299ac9',
        ];
        if (in_array(hash('sha256', trim((string) $article->content)), $originalBodyHashes, true)) {
            app(LegacyArticleImporter::class)->import(false, true, ['mikrotik-pbr-client']);
        }
    }

    public function down(): void
    {
        // Keep published translations and editorial changes on rollback.
    }
};
