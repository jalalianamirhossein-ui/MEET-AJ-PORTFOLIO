<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\ArticleStructure;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleHeadingNumberingTest extends TestCase
{
    use RefreshDatabase;

    public function test_numbering_validator_exposes_original_errors_without_normalizing_them(): void
    {
        $service = app(ArticleStructure::class);
        $html = '<h2 data-en="14. Architecture" data-fa="۱۴. معماری">14. Architecture</h2><h2>3. Install</h2><h2>3. Configure</h2>';
        $issues = $service->numberingIssues($html);
        $this->assertSame([14,3,3], $issues['en_sequence']);
        $this->assertArrayHasKey('en_backward', $issues);
        $this->assertArrayHasKey('en_duplicates', $issues);
        $this->assertArrayNotHasKey('bilingual_alignment', $issues);
        $this->assertArrayHasKey('bilingual_alignment', $service->numberingIssues('<h2 data-en="1. Intro" data-fa="۲. مقدمه">1. Intro</h2>'));
        $this->assertSame([], $service->numberingIssues('<h2>8.10.2 compatibility</h2><h2>10.0.0.1 topology</h2><h2>FAQ</h2><h3>1. Question</h3><pre><code>14. command output</code></pre><ol><li>12. example</li></ol>'));
        $pair = $service->numberHeadings('<h2>16. Peer authentication</h2><h2>15. Replica startup</h2><p data-en="sections 15 and 16" data-fa="بخش‌های ۱۵ و ۱۶">sections 15 and 16</p>');
        $this->assertStringContainsString('data-fa="بخش‌های ۱ و ۲"', $pair);
        $this->assertStringContainsString('data-en="sections 1 and 2"', $pair);
        $range = implode('', array_map(fn ($n) => '<h2>'.$n.'. Topic</h2>', [1,2,3,4,5,6,7,8,10,11,12,13,14,9]));
        $this->assertStringContainsString('بخش‌های ۵ تا ۱۴؛ بخش‌های ۹ تا ۱۳', $service->numberHeadings($range.'<p>بخش‌های ۵ تا ۱۴؛ بخش‌های ۱۰ تا ۱۴</p>'));
    }

    public function test_redis_sections_and_prose_references_follow_final_order_with_code_and_ids_intact(): void
    {
        $code = '<pre><code># section 14'."\n".'redis_version=8.10.2'."\n".'bind 10.10.20.10</code></pre>';
        $html = '<section id="installation"><h2 data-en="3. Installation" data-fa="۳. نصب">3. Installation</h2><p data-en="See section 14." data-fa="بخش ۱۴ را ببینید.">See section 14.</p>'.$code.'</section>'
            .'<section id="replication-architecture"><h2 data-en="14. Architecture" data-fa="۱۴. معماری">14. Architecture</h2><p>Design.</p></section>'
            .'<section id="cluster-comparison"><h2 data-en="17. Comparison" data-fa="۱۷. مقایسه">17. Comparison</h2><p>Compare.</p></section>';
        $service = app(ArticleStructure::class);
        $fixed = $service->repair($html, 'redis-installation-configuration-replication');
        $this->assertStringContainsString('data-en="1. Architecture" data-fa="۱. معماری"', $fixed);
        $this->assertStringContainsString('data-en="3. Installation" data-fa="۳. نصب"', $fixed);
        $this->assertStringContainsString('data-en="See section 1." data-fa="بخش ۱ را ببینید."', $fixed);
        $this->assertStringContainsString($code, $fixed);
        $this->assertSame([], $service->numberingIssues($fixed));
        $this->assertSame($fixed, $service->repair($fixed, 'redis-installation-configuration-replication'));
    }

    public function test_every_authoritative_source_has_valid_bilingual_numbering_and_stable_normalization(): void
    {
        $service = app(ArticleStructure::class);
        foreach (glob(resource_path('legacy/articles/*.html')) as $path) {
            $source = file_get_contents($path);
            $this->assertSame([], $service->numberingIssues($source), basename($path));
            $this->assertSame($source, $service->repair($source, basename($path, '.html')), basename($path).' PHP/source parity');
        }
        $mongo = file_get_contents(resource_path('legacy/articles/mongodb-installation-configuration-production-deployment.html'));
        $this->assertStringContainsString('بخش‌های ۵ تا ۱۴', $mongo);
        $this->assertStringContainsString('بخش‌های ۱۵ و ۱۶', $mongo);
        $this->assertStringContainsString('بخش‌های ۹ تا ۱۳', $mongo);
        $this->assertStringNotContainsString('بخش‌های ۱۶ و ۱۶', $mongo);
    }

    public function test_numbering_migration_preserves_every_other_attribute_in_all_supported_states(): void
    {
        $initialCount = Article::count();
        // Scheduled articles use published + a future published_at; archived is not a CMS state.
        foreach (['draft','published','scheduled'] as $state) {
            $article = Article::create(['title'=>$state, 'slug'=>'cms-'.$state,
                'status'=>$state === 'scheduled' ? 'published' : $state,
                'published_at'=>$state === 'scheduled' ? now()->addMonth() : ($state === 'published' ? now()->subMonth() : null),
                'content'=>'<section id="installation"><h2>1. Install</h2><p>See section 2.</p></section><section id="prerequisites"><h2>2. Prepare</h2><p>Prepare.</p></section>',
                'seo_data'=>['schema'=>['dateModified'=>'2026-10-01']], 'presentation'=>['custom'=>'keep']]);
            $before = $article->getAttributes();
            $migration = require database_path('migrations/2026_10_09_000051_correct_article_heading_sequences.php');
            $migration->up(); $after = $article->fresh();
            foreach ($before as $key=>$value) {
                if ($key !== 'content') { $this->assertSame($value, $after->getAttributes()[$key], $state.' '.$key); }
            }
            $this->assertStringContainsString('<h2>1. Prepare</h2>', $after->content);
            $this->assertStringContainsString('See section 1.', $after->content);
            $migration->up(); $this->assertSame($after->content, $after->fresh()->content);
        }
        $this->assertSame($initialCount + 3, Article::count());
    }

    public function test_reimport_preserves_identity_publication_state_and_all_timestamps(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        $article = Article::where('slug','redis-installation-configuration-replication')->firstOrFail();
        $article->forceFill(['status'=>'draft','created_at'=>'2020-01-01 01:02:03','updated_at'=>'2020-01-02 01:02:03'])->saveQuietly();
        $before = $article->fresh(); $count = Article::count();
        app(LegacyArticleImporter::class)->import(false, true);
        $after = $article->fresh();
        foreach (['id','slug','translation_key','status','created_at','updated_at','published_at'] as $key) {
            $this->assertEquals($before->$key, $after->$key, $key);
        }
        $this->assertSame($count, Article::count());
    }
}
