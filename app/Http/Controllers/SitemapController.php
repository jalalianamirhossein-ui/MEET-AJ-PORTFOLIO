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
            ['loc' => $origin.'/', 'lastmod' => $this->sourceLastmod(resource_path('legacy/index.html'))],
        ];

        $articles = Article::published()->where('language', 'en')->orderByDesc('published_at')->orderBy('sort_order')->get();
        foreach ($articles as $article) {
            $alternates = data_get($article->presentation, 'localizations')
                ? ['fa' => $article->publicUrl().'?lang=fa', 'en' => $article->publicUrl()] : [];
            $urls[] = [
                'loc' => $article->canonicalUrl(),
                'alternates' => $alternates,
                'lastmod' => $article->published_at?->toDateString(),
            ];
            if ($alternates) {
                $urls[] = ['loc' => $alternates['fa'], 'alternates' => $alternates, 'lastmod' => $article->updated_at?->toDateString()];
            }
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
