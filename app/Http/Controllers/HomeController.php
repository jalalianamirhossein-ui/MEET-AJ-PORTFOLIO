<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Service;
use App\Models\Tag;
use App\Models\Testimonial;
use App\Services\HomepageContentCatalog;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(HomepageContentCatalog $contentCatalog): View
    {
        $homepageContent = $contentCatalog->forView();
        $articles = Article::published()
            ->forListing()
            ->with(['category', 'tags'])
            ->orderByRaw('CASE WHEN articles.slug = ? THEN 0 WHEN articles.slug = ? THEN 1 ELSE 2 END', ['mikrotik-ping-triggered-policy-routing', 'linux-security-auditor-bash'])
            ->orderByDesc('published_at')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $services = Service::query()->publicCatalog()->get();
        $testimonials = Testimonial::published()->orderBy('sort_order')->orderBy('id')->get();
        $filterCategories = Category::query()
            ->whereIn('language', ['en', 'fa'])
            ->whereHas('articles', fn ($query) => $query->published())
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('home', [
            'articles' => $articles,
            'services' => $services,
            'q' => '',
            'tagSlug' => '',
            'searching' => false,
            'activeTag' => null,
            'tags' => Tag::query()->orderBy('name')->get(),
            'filterCategories' => $filterCategories,
            'testimonials' => $testimonials,
            'homepageContent' => $homepageContent,
        ]);
    }
}
