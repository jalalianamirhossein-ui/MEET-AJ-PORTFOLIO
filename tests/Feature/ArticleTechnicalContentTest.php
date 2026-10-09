<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\ArticleTechnicalContent;
use App\Services\ArticleTechnicalQuality;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTechnicalContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_reviewed_artifacts_survive_source_import_and_both_final_blade_editions(): void
    {
        $quality = app(ArticleTechnicalQuality::class);
        $importer = app(LegacyArticleImporter::class);
        $contracts = json_decode(file_get_contents(resource_path('content/article-technical-contracts.json')), true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(count($importer->articleFiles()), $contracts['articles']);
        foreach ($importer->articleFiles() as $file) {
            $slug = pathinfo($file, PATHINFO_FILENAME);
            $this->assertSame([], $quality->issues(file_get_contents($file), $slug), $slug.' authoritative source');
        }
        $importer->import(false);
        foreach (Article::query()->get() as $article) {
            foreach (['en', 'fa'] as $locale) {
                $page = $this->withUnencryptedCookie('lang', $locale)->get($article->path())->assertOk()->getContent();
                $this->assertSame([], $quality->issues($page, $article->slug), $article->slug.' '.$locale.' final Blade artifacts');
            }
        }
        $count = Article::count();
        $importer->import(false, true);
        $this->assertSame($count, Article::count(), 'Reimport must not create another article or locale row');
    }

    public function test_missing_command_blocks_incomplete_translation_and_rate_limited_denial_are_reported(): void
    {
        $quality = app(ArticleTechnicalQuality::class);
        $slug = 'windows-cmd-common-network-commands';
        $source = file_get_contents(resource_path('legacy/articles/'.$slug.'.html'));
        $missingCommand = preg_replace('~<pre\b[^>]*><code class="language-batch">ipconfig</code></pre>~', '', $source, 1);
        $this->assertNotSame($source, $missingCommand);
        $this->assertArrayHasKey('missing_or_changed_artifact', $quality->issues($missingCommand, $slug));
        $missingTranslation = str_replace('<div id="technical-enterprise-intro">', '<div id="technical-enterprise-intro"><p data-en="An incomplete explanation">An incomplete explanation</p>', $source);
        $this->assertArrayHasKey('incomplete_technical_translation', $quality->issues($missingTranslation, $slug));
        $firewall = file_get_contents(resource_path('legacy/articles/mikrotik-firewall-hardening-input-forward-chain.html'));
        $this->assertSame([], $quality->issues($firewall.'<pre><code># Never combine action=drop with limit= on a terminal deny</code></pre>', 'mikrotik-firewall-hardening-input-forward-chain'));
        $firewall .= '<pre><code>add chain=forward action=drop limit=10,5:packet</code></pre>';
        $this->assertArrayHasKey('rate_limited_deny', $quality->issues($firewall, 'mikrotik-firewall-hardening-input-forward-chain'));
    }

    public function test_content_migration_preserves_state_metadata_timestamps_and_custom_cms_notes(): void
    {
        $old = file_get_contents(base_path('tests/Fixtures/windows-network-before-technical-audit.html'));
        $old .= '<p data-en="Retain the incident owner note." data-fa="یادداشت مسئول رخداد حفظ شود.">Retain the incident owner note.</p>';
        $draft = Article::create(['title'=>'CMS draft', 'slug'=>'windows-cmd-common-network-commands', 'status'=>'draft', 'content'=>$old, 'published_at'=>null, 'presentation'=>['custom'=>'keep'], 'seo_data'=>['schema'=>['dateModified'=>'2024-01-01']]]);
        $before = $draft->getAttributes();
        $migration = require database_path('migrations/2026_10_09_000052_enrich_reviewed_article_technical_content.php');
        $migration->up();
        $draft->refresh();
        $this->assertStringContainsString('id="cmd-pathping"', $draft->content);
        $this->assertStringContainsString('Retain the incident owner note.', $draft->content);
        foreach ($before as $key => $value) {
            if ($key !== 'content') { $this->assertSame($value, $draft->getRawOriginal($key), $key); }
        }
        $normalized = $draft->content;
        $migration->up();
        $this->assertSame($normalized, $draft->fresh()->content);
    }

    public function test_customized_cms_installation_is_not_overwritten_and_is_reported_for_review(): void
    {
        $old = file_get_contents(base_path('tests/Fixtures/windows-network-before-technical-audit.html'));
        $custom = str_replace('<section id="enterprise-installation">', '<section id="enterprise-installation"><p>Custom operator procedure</p>', $old);
        // Source formatting can put additional attributes/newlines on the section.
        if ($custom === $old) {
            $custom = preg_replace('~(<section\b[^>]*id="enterprise-installation"[^>]*>)~', '$1<p>Custom operator procedure</p>', $old, 1);
        }
        $this->assertNotSame($old, $custom);
        $this->assertSame($custom, app(ArticleTechnicalContent::class)->enrich($custom, 'windows-cmd-common-network-commands'));
        $this->assertArrayHasKey('missing_or_changed_artifact', app(ArticleTechnicalQuality::class)->issues($custom, 'windows-cmd-common-network-commands'));
        $this->assertSame($custom, app(ArticleTechnicalContent::class)->enrich($custom, 'unreviewed-cms-only-draft'));
        $this->assertArrayHasKey('unreviewed_article', app(ArticleTechnicalQuality::class)->issues($custom, 'unreviewed-cms-only-draft'));
    }
}
