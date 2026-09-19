<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Service;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $origin = rtrim((string) config('app.url'), '/');
        $urls = [
            ['loc' => $origin.'/', 'lastmod' => $this->sourceLastmod(resource_path('legacy/index.html'))],
        ];

        foreach (Service::query()->publicCatalog()->get() as $service) {
            $urls[] = [
                'loc' => $service->canonicalUrl(),
                'lastmod' => $service->published_at?->toDateString()
                    ?: $this->sourceLastmod(resource_path('legacy/services/'.$service->slug.'.html')),
            ];
        }

        $articles = Article::published()->where('language', 'en')->orderBy('sort_order')->get();
        foreach ($articles as $article) {
            $urls[] = [
                'loc' => $article->canonicalUrl(),
                'lastmod' => $article->published_at?->toDateString(),
            ];
        }

        $xml = view('seo.sitemap', compact('urls'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function sourceLastmod(string $path): ?string
    {
        $mtime = is_file($path) ? filemtime($path) : false;

        return $mtime === false ? null : gmdate('Y-m-d', $mtime);
    }
}
