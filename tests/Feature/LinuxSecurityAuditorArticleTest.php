<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\LegacyArticleImporter;
use App\Services\LegacySitePublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinuxSecurityAuditorArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_bilingual_routes_links_assets_and_cli_match_the_source(): void
    {
        $slug = 'linux-security-auditor-bash';
        app(LegacySitePublisher::class)->publishAssets();
        app(LegacyArticleImporter::class)->import(false);
        $article = Article::where('slug', $slug)->firstOrFail();
        $curated = Article::whereIn('slug', config('article-order.enterprise'))->inDisplayOrder()->pluck('slug')->all();
        $this->assertContains($slug, $curated);
        $this->assertSame('linux', $article->category->slug);
        $this->assertSame(['linux', 'ssh', 'ubuntu'], $article->tags()->orderBy('slug')->pluck('slug')->all());
        $this->assertSame('/assets/img/articles/banners/linux-security-auditor-bash.png', $article->thumbnailUrl());
        $this->assertFileExists(public_path('assets/img/articles/banners/linux-security-auditor-bash.png'));
        $this->assertSame(hash_file('sha256', resource_path('content/articles/linux-security-auditor-bash/security-audit.sh')), hash_file('sha256', public_path('docs/linux-security-auditor/security-audit.sh')));
        $source = file_get_contents(resource_path('content/articles/linux-security-auditor-bash/security-audit.sh'));
        preg_match("~usage\\(\\).*?cat <<'HELP'\\r?\\n(.*?)\\r?\\nHELP~s", $source, $help);
        preg_match_all('/--[a-z]+(?:-[a-z]+)*/', $help[1], $flags);
        foreach (array_unique($flags[0]) as $flag) {
            $this->assertStringContainsString($flag, $article->content, $flag);
        }
        $codes = [];
        foreach (['en', 'fa'] as $locale) {
            $html = $this->withUnencryptedCookie('lang', $locale)->get($article->path())->assertOk()->getContent();
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
            $xp = new \DOMXPath($dom);
            $this->assertSame($locale, $xp->evaluate('string(/html/@lang)'));
            $this->assertSame($locale === 'fa' ? 'rtl' : 'ltr', $xp->evaluate('string(/html/@dir)'));
            $this->assertSame($article->presentation['localizations'][$locale]['meta_title'], $xp->evaluate('string(//head/title)'));
            $this->assertStringEndsWith('/articles/'.$slug, $xp->evaluate('string(//link[@rel="canonical"]/@href)'));
            $this->assertStringNotContainsString('D:\\', $html);
            $this->assertStringNotContainsString('/resources/', $html);
            $this->assertStringNotContainsString('—', strip_tags($article->content));
            $this->assertGreaterThanOrEqual(25, $xp->query('//article[@class="article-body"]//section')->length);
            foreach ($xp->query('//article[@class="article-body"]//a') as $link) {
                $href = $link->getAttribute('href');
                if (str_starts_with($href, '/articles/')) {
                    $this->assertTrue(Article::published()->where('slug', substr($href, 10))->exists(), $href);
                } elseif (str_starts_with($href, '/docs/')) {
                    $this->assertFileExists(public_path(ltrim($href, '/')));
                    $this->assertSame('security-audit.sh', $link->getAttribute('download'));
                }
            }
            foreach ($xp->query('//ul[@class="article-toc-list"]//a') as $link) {
                $id = substr($link->getAttribute('href'), 1);
                $this->assertSame(1, $xp->query('//*[@id="'.$id.'"]')->length, $id);
            }
            foreach ($xp->query('//article[@class="article-body"]//img') as $img) {
                $this->assertFileExists(public_path(ltrim($img->getAttribute('src'), '/')));
                if ($locale === 'fa') {
                    $this->assertMatchesRegularExpression('/\p{Arabic}/u', $img->getAttribute('alt'));
                }
                $this->assertStringContainsString('Linux Security Auditor', $img->getAttribute('alt'));
            }
            $codes[$locale] = [];
            foreach ($xp->query('//article[@class="article-body"]//pre/code') as $block) {
                $codes[$locale][] = $block->textContent;
            }
            $this->assertGreaterThan(0, $xp->query('//code[@class="language-bash"]/span[@class="code-token-option"]')->length);
            $this->assertStringContainsString("--ssh-context 'user=admin,host=admin.example,addr=192.0.2.10'", implode("\n", $codes[$locale]));
        }
        $this->assertSame($codes['en'], $codes['fa']);
        $this->get('/articles/'.$slug.'.html')->assertStatus(301)->assertRedirect('/articles/'.$slug);
        $this->get('/sitemap.xml')->assertOk()->assertSee('/articles/'.$slug);
    }

    public function test_newer_linux_articles_precede_the_auditor_across_public_lists(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        Article::whereNotIn('slug', ['linux-security-auditor-bash', 'enable-ssh-linux-complete-guide'])->update(['status' => 'draft']);
        $auditor = Article::where('slug', 'linux-security-auditor-bash')->firstOrFail();
        $auditor->update(['published_at' => now()->subDays(10)]);
        Article::where('slug', 'enable-ssh-linux-complete-guide')->update(['published_at' => now()->subDay(), 'sort_order' => 0]);

        foreach (['/', '/articles', '/articles?tag=linux'] as $path) {
            $response = $this->get($path)->assertOk();
            $items = $response->viewData($path === '/articles?tag=linux' ? 'results' : 'articles');
            $this->assertSame(['enable-ssh-linux-complete-guide', $auditor->slug], collect($items->all())->pluck('slug')->all(), $path);
        }
    }
}
