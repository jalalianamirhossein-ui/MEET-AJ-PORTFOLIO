<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleRedirect;
use App\Services\ArticleSeo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(): View
    {
        $articles = Article::published()->with('category')->orderBy('sort_order')->orderBy('id')->get();

        return view('articles.index', compact('articles'));
    }

    public function show(string $slug): View
    {
        $article = Article::published()->where('slug', $slug)->where('language', 'en')->first();
        abort_if($article === null, 404);

        return view('articles.show', [
            'article' => $article,
            'seo' => app(ArticleSeo::class)->forArticle($article),
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
