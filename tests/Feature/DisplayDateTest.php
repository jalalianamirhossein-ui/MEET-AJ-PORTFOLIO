<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\DisplayDate;
use App\Services\LegacyArticleImporter;
use App\Services\LegacySitePublisher;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisplayDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_persian_calendar_changes_at_nowruz_and_uses_tehran_day(): void
    {
        config(['cms.display_timezone' => 'Asia/Tehran']);
        $formatter = app(DisplayDate::class);
        $date = CarbonImmutable::parse('2025-03-20T20:45:00Z');
        $this->assertSame('۱ فروردین ۱۴۰۴', $formatter->format($date, 'fa'));
        $this->assertSame('Mar 21, 2025', $formatter->format($date, 'en'));
        $this->assertSame('۱۴۰۳', $formatter->format($date->subDay(), 'fa', true));
        $this->assertSame('۱۴۰۴', $formatter->format($date, 'fa', true));
        $this->assertSame('2025', $formatter->format($date, 'en', true));
        $this->assertSame('2025-03-20T20:45:00+00:00', $date->toAtomString());
    }

    public function test_public_dates_render_in_both_calendars_and_retain_machine_readable_timestamps(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-05T12:00:00+03:30'));
        app(LegacySitePublisher::class)->buildViews();
        app(LegacyArticleImporter::class)->import(false);
        $article = Article::where('slug', 'mikrotik-ping-triggered-policy-routing')->firstOrFail();
        $article->update(['published_at' => CarbonImmutable::parse('2026-10-05T00:00:00+03:30')]);
        foreach (['fa' => '۱۳ مهر ۱۴۰۵', 'en' => 'Oct 5, 2026'] as $locale => $expected) {
            foreach (['/', '/articles', '/articles?q=mikrotik', $article->path()] as $path) {
                $html = $this->withUnencryptedCookie('lang', $locale)->get($path)->assertOk()->getContent();
                $dom = new \DOMDocument;
                @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
                $xp = new \DOMXPath($dom);
                $dates = $xp->query('//time[@data-en="Oct 5, 2026"]');
                $this->assertGreaterThan(0, $dates->length, $path);
                foreach ($dates as $date) {
                    $this->assertSame($expected, trim($date->textContent), $path);
                    $this->assertSame('۱۳ مهر ۱۴۰۵', $date->getAttribute('data-fa'));
                    $this->assertStringContainsString('2026-10-', $date->getAttribute('datetime'));
                }
                $this->assertSame($locale === 'fa' ? '۱۴۰۵' : '2026', $xp->evaluate('string(//span[@data-current-year])'));
            }
        }
    }
}
