<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleRedirect;
use App\Models\Tag;
use App\Services\ArticleSeo;
use App\Services\ArticleShareLinks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $tagSlug = trim((string) $request->query('tag', ''));
        $searching = $q !== '' || $tagSlug !== '';

        $listing = Article::published()
            ->forListing()
            ->with(['category', 'tags'])
            ->orderBy('sort_order')
            ->orderBy('id');

        $results = null;
        $articles = collect();

        if ($searching) {
            $results = Article::published()
                ->forListing()
                ->with(['category', 'tags'])
                ->search($q)
                ->withTag($tagSlug)
                ->orderByDesc('published_at')
                ->orderBy('id')
                ->paginate(9)
                ->withQueryString();
        } else {
            $articles = $listing->get();
        }

        return view('articles.index', [
            'articles' => $articles,
            'results' => $results,
            'searching' => $searching,
            'q' => $q,
            'tagSlug' => $tagSlug,
            'activeTag' => $tagSlug !== '' ? Tag::query()->where('slug', $tagSlug)->first() : null,
            'tags' => Tag::query()->orderBy('name')->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $article = Article::published()
            ->with(['category', 'tags'])
            ->where('slug', $slug)
            ->where('language', 'en')
            ->first();
        abort_if($article === null, 404);

        return view('articles.show', [
            'article' => $article,
            'seo' => app(ArticleSeo::class)->forArticle($article),
            'share' => app(ArticleShareLinks::class)->for($article),
            'related' => $article->relatedArticles(3),
        ]);
    }

    public function legacy(Request $request, string $slug): RedirectResponse
    {
        $path = '/articles/'.$slug.'.html';
        $redirect = ArticleRedirect::query()->where('old_path', $path)->first();
        $article = $redirect?->article ?: Article::published()->where('slug', $slug)->where('language', 'en')->first();
        abort_if($article === null || $article->language === 'de' || $article->status !== 'published' || $article->published_at === null || $article->published_at->isFuture(), 404);

        $target = $article->path();
        $query = $request->getQueryString();
        if ($query) {
            $target .= '?'.$query;
        }

        return redirect()->to($target, 301);
    }
}
