<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\ArticleContentStandardizer;
use App\Services\ArticleLocalization;
use App\Services\ArticleStructure;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_article_is_ordered_in_both_languages_with_identical_code_and_valid_navigation(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        $count = Article::count();
        app(LegacyArticleImporter::class)->import(false, true);
        $this->assertSame($count, Article::count(), 'Reimport must not create duplicates');
        foreach (Article::all() as $article) {
            $codes = [];
            $headings = [];
            foreach (['en', 'fa'] as $locale) {
                $localized = app(ArticleLocalization::class)->apply(clone $article, $locale);
                $html = $localized->displayContent();
                $this->assertSame([], app(ArticleStructure::class)->numberingIssues($html), $article->slug.' '.$locale.' rendered numbering');
                $this->assertSame($html, app(ArticleStructure::class)->order($html, $article->slug), $article->slug.' '.$locale);
                $this->assertSame($html, app(ArticleStructure::class)->cleanReviews($html), $article->slug.' '.$locale.' review labels');
                $dom = new \DOMDocument;
                @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
                $xp = new \DOMXPath($dom);
                $ids = [];
                foreach ($xp->query('//*[@id]') as $node) { $ids[] = $node->getAttribute('id'); }
                $this->assertSame($ids, array_values(array_unique($ids)), $article->slug);
                foreach ([['introduction', 'conclusion'], ['prerequisites', 'configuration'], ['architecture', 'configuration'], ['conclusion', 'faq'], ['faq', 'official-references']] as [$earlier, $later]) {
                    if (in_array($earlier, $ids, true) && in_array($later, $ids, true)) {
                        $this->assertLessThan(strpos($html, 'id="'.$later.'"'), strpos($html, 'id="'.$earlier.'"'), $article->slug.' '.$earlier.' before '.$later);
                    }
                }
                $dependencies = match ($article->slug) {
                    'truenas-zfs-enterprise' => [['section-18', 'section-19'], ['section-23', 'section-15'], ['section-19','section-20'], ['section-20','section-22'], ['section-22','section-27']],
                    'mongodb-installation-configuration-production-deployment' => [['backup', 'verification'], ['tls','remote-access'], ['replica-security','replica-set']],
                    'redis-installation-configuration-replication' => [['verify-replication', 'sentinel'], ['replication-architecture','installation'], ['backup-restore','troubleshooting'], ['memory-management','troubleshooting']],
                    'zabbix-server-linux-windows-agents-backup' => [['security','monitoring'], ['automated-backup','recovery'], ['recovery','troubleshooting']],
                    default => [],
                };
                foreach ($dependencies as [$earlier, $later]) {
                    $this->assertLessThan(strpos($html, 'id="'.$later.'"'), strpos($html, 'id="'.$earlier.'"'), $article->slug.' technical dependency');
                }
                foreach ($xp->query('//a[starts-with(@href,"#")]') as $link) {
                    $this->assertContains(rawurldecode(substr($link->getAttribute('href'), 1)), $ids, $article->slug);
                }
                foreach ($xp->query('//*[@data-en]') as $node) {
                    if (trim($node->textContent) === '' && $node->getAttribute('data-en') === '' && $node->getAttribute('data-fa') === '') { continue; }
                    $this->assertNotSame('', $node->getAttribute('data-fa'), $article->slug.' missing Persian');
                }
                $codes[$locale] = array_map(fn ($node) => $node->textContent, iterator_to_array($xp->query('//pre')));
                $headings[$locale] = array_map(fn ($node) => $node->parentNode->getAttribute('id'), iterator_to_array($xp->query('//h2')));
            }
            $this->assertSame($codes['en'], $codes['fa'], $article->slug.' executable code');
            $this->assertSame($headings['en'], $headings['fa'], $article->slug.' navigation');
        }
    }

    public function test_complete_nested_sections_move_without_changing_commands_or_technical_dates(): void
    {
        $code = '<pre><code>echo "Last reviewed: 2026-10-09"'."\n".'date=2026-10-09</code></pre>';
        $installation = '<section id="installation"><h2>Installation</h2><p>Install.</p>'.$code.'<section id="nested"><h3>Check the operation</h3><p>Keep this attached.</p></section></section>';
        $html = $installation.'<section id="conclusion"><h2>Conclusion</h2><p>Done.</p></section>'
            .'<section id="introduction"><h2>Introduction</h2><p>Start.</p></section>'
            .'<section id="prerequisites"><h2>Prerequisites</h2><p>Prepare.</p></section>'
            .'<section id="architecture"><h2>Architecture</h2><p>Design.</p></section>'
            .'<p data-en="Last reviewed: October 9, 2026" data-fa="آخرین بازبینی: ۹ اکتبر ۲۰۲۶">Last reviewed: October 9, 2026</p>'
            .'<p>Release published on 17 September 2026. Backups at 02:00.</p>';
        $service = app(ArticleStructure::class);
        $result = $service->repair($html, 'custom-draft');
        $this->assertStringContainsString($installation, $result);
        $this->assertStringContainsString('Release published on 17 September 2026', $result);
        $this->assertStringNotContainsString('آخرین بازبینی', $result);
        $this->assertLessThan(strpos($result, 'id="installation"'), strpos($result, 'id="prerequisites"'));
        $this->assertLessThan(strpos($result, 'id="installation"'), strpos($result, 'id="architecture"'));
        $this->assertSame($result, $service->repair($result, 'custom-draft'));
        foreach (['Last reviewed: January 2, 2026', 'Reviewed on: 02/12/2026', 'Technical review date: 2026.02.12', 'آخرین بازبینی: ۲ بهمن ۱۴۰۵', 'تاریخ بررسی فنی: ۱۴۰۵/۱۱/۰۲'] as $label) {
            $this->assertSame('', $service->cleanReviews('<p>'.$label.'</p>'), $label);
        }
    }

    public function test_migration_cleans_cms_only_drafts_without_changing_metadata_or_timestamps(): void
    {
        $article = Article::create(['title' => 'Draft', 'slug' => 'cms-only-draft', 'status' => 'draft',
            'content' => '<section id="installation"><h2>Installation</h2><p>Install.</p></section><section id="prerequisites"><h2>Prerequisites</h2><p>Prepare.</p></section><p>Last verified: 2026-10-09</p>',
            'seo_data' => ['schema' => ['dateModified' => '2026-10-09']], 'presentation' => ['custom' => 'keep'],
        ]);
        $before = $article->getAttributes();
        $migration = require database_path('migrations/2026_10_09_000050_repair_article_structure_and_review_labels.php');
        $migration->up();
        $after = $article->fresh();
        foreach ($before as $key => $value) {
            if ($key !== 'content') { $this->assertSame($value, $after->getAttributes()[$key], $key); }
        }
        $this->assertStringNotContainsString('Last verified:', $after->content);
        $this->assertSame(2, substr_count($after->content, '<h2'), 'Cleanup must not add sections to a draft');
        $this->assertLessThan(strpos($after->content, 'id="configuration"'), strpos($after->content, 'id="prerequisites"'));
        $migration->up();
        $this->assertSame($after->content, $after->fresh()->content);
        $this->assertSame(1, Article::where('slug', 'cms-only-draft')->count());
    }
}
