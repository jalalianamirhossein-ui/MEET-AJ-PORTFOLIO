<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Service;
use App\Models\Tag;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $articles = Article::published()
            ->forListing()
            ->with(['category', 'tags'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $services = Service::query()->publicCatalog()->get();

        return view('home', [
            'articles' => $articles,
            'services' => $services,
            'q' => '',
            'tagSlug' => '',
            'searching' => false,
            'activeTag' => null,
            'tags' => Tag::query()->orderBy('name')->get(),
        ]);
    }
}
