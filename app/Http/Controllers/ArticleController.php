<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleRedirect;
use App\Models\Category;
use App\Models\Tag;
use App\Services\ArticleSeo;
use App\Services\ArticleLocalization;
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
            ->orderByDesc('published_at')
            ->orderBy('sort_order')
            ->orderBy('id');

        $results = null;
        $articles = collect();
        $filterCategories = Category::query()
            ->whereIn('language', ['en', 'fa'])
            ->whereHas('articles', fn ($query) => $query->published())
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($searching) {
            $results = Article::published()
                ->forListing()
                ->with(['category', 'tags'])
                ->search($q)
                ->withTag($tagSlug)
                ->orderByDesc('published_at')
                ->orderBy('sort_order')
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
            'filterCategories' => $filterCategories,
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $article = Article::published()
            ->with(['category', 'tags'])
            ->where('slug', $slug)
            ->where('language', 'en')
            ->first();
        abort_if($article === null, 404);
        $localizer = app(ArticleLocalization::class);
        $languageSeo = [];
        if (data_get($article->presentation, 'localizations')) {
            foreach (['en', 'fa'] as $locale) {
                $localized = $localizer->apply(clone $article, $locale, false);
                $languageSeo[$locale] = array_intersect_key(
                    app(ArticleSeo::class)->forArticle($localized),
                    array_flip(['title', 'description', 'keywords', 'canonical', 'og_title', 'og_description', 'og_url', 'twitter_title', 'twitter_description', 'schema', 'faq_schema', 'breadcrumb'])
                );
                $languageSeo[$locale]['share'] = app(ArticleShareLinks::class)->for($localized);
            }
        }
        $article = $localizer->apply($article, $request->query('lang') === 'fa' ? 'fa' : 'en');

        return view('articles.show', [
            'article' => $article,
            'languageSeo' => $languageSeo,
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
