<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $origin = rtrim((string) config('app.url'), '/');
        $urls = [
            ['loc' => $origin.'/'],
            ['loc' => $origin.'/articles'],
        ];

        $articles = Article::published()->where('language', 'en')->orderByDesc('published_at')->orderBy('sort_order')->get();
        foreach ($articles as $article) {
            // Match the robots metadata rendered by ArticleSeo. "none"
            // includes noindex, so neither directive belongs in the sitemap.
            $robots = (string) data_get($article->seo_data, 'robots', 'index, follow');
            if (preg_match('/(?:^|[\s,])(?:noindex|none)(?:$|[\s,])/i', $robots)) {
                continue;
            }

            $urls[] = [
                'loc' => $article->canonicalUrl(),
                'lastmod' => ($article->updated_at ?? $article->published_at)?->toDateString(),
            ];
        }

        $xml = view('seo.sitemap', compact('urls'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
