<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\ArticleHtmlSanitizer;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SqlBackupArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_editorial_package_preserves_executable_snippets_and_toc_targets(): void
    {
        $directory = resource_path('content/articles/sql-server-automatic-backup-job');
        $package = require $directory.'/build.php';
        $html = app(ArticleHtmlSanitizer::class)->sanitize($package['content']);
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
        $xpath = new \DOMXPath($dom);
        $codeBlocks = [];
        foreach ($xpath->query('//pre/code') as $code) {
            $codeBlocks[] = $code->textContent;
        }
        foreach (glob($directory.'/*.{sql,ps1}', GLOB_BRACE) as $file) {
            $this->assertContains(file_get_contents($file), $codeBlocks, basename($file).' changed during HTML sanitization');
        }
        preg_match_all('/href="#([^"]+)"/', $package['presentation']['toc_html'], $targets);
        $this->assertGreaterThanOrEqual(14, count($targets[1]));
        foreach ($targets[1] as $target) {
            $this->assertSame(1, $xpath->query('//*[@id="'.$target.'"]')->length, $target);
        }
        foreach ($package['seo_data']['faq_schema']['mainEntity'] as $item) {
            $this->assertStringContainsString($item['name'], $html);
            $this->assertStringContainsString($item['acceptedAnswer']['text'], $html);
        }
        $this->assertStringNotContainsString('{{CODE:', $html);
        $this->assertStringNotContainsString('xp_cmdshell', file_get_contents($directory.'/03-agent-jobs.sql'));
    }

    public function test_persian_editorial_article_renders_metadata_faq_and_rtl_body(): void
    {
        $package = require resource_path('content/articles/sql-server-automatic-backup-job/build.php');
        $package['content'] = app(ArticleHtmlSanitizer::class)->sanitize($package['content']);
        Article::create(array_merge($package, [
            'slug' => 'sql-server-automatic-backup-job',
            'status' => 'published', 'published_at' => now()->subDay(),
        ]));
        $response = $this->get('/articles/sql-server-automatic-backup-job')->assertOk();
        $response->assertSee('<title>'.$package['meta_title'].'</title>', false)
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('"inLanguage":"fa"', false)
            ->assertSee($package['presentation']['hero_title_fa'])
            ->assertDontSee('control-plane configuration');
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NONET);
        $this->assertSame(1, (new \DOMXPath($dom))->query('//article[@class="article-body"][@lang="fa"][@dir="rtl"]')->length);
        $this->get('/articles/sql-server-automatic-backup-job.html')
            ->assertRedirect('/articles/sql-server-automatic-backup-job');
    }

    public function test_unchanged_legacy_import_preserves_intentional_persian_metadata(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        $article = Article::where('slug', 'sql-server-automatic-backup-job')->firstOrFail();
        $package = require resource_path('content/articles/sql-server-automatic-backup-job/build.php');
        $package['presentation'] = array_replace($article->presentation, $package['presentation']);
        $article->update($package);
        app(LegacyArticleImporter::class)->import(false);
        $article->refresh();
        $this->assertSame($package['meta_title'], $article->meta_title);
        $this->assertSame($package['content'], $article->content);
        $this->assertSame($package['seo_data']['schema']['headline'], $article->seo_data['schema']['headline']);
        $this->assertSame($package['seo_data']['og_title'], $article->seo_data['og_title']);
    }
}
