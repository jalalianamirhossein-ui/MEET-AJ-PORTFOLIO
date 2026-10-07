<?php

use App\Models\Article;
use App\Services\MikrotikFirewallArticleRepair;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (Article::where('slug', 'mikrotik-firewall-hardening-input-forward-chain')->get() as $article) {
            $localizations = data_get($article->presentation, 'localizations');
            if (isset($localizations['en']['faq'], $localizations['fa']['faq'])) {
                $article->update(['content' => app(MikrotikFirewallArticleRepair::class)->repair($article->content, $localizations)]);
            }
        }
    }

    public function down(): void
    {
        // Preserve corrected editorial content.
    }
};
