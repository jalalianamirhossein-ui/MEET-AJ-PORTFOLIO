<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Service;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $articles = Article::published()->with('category')->orderBy('sort_order')->orderBy('id')->get();
        $services = Service::query()->publicCatalog()->get();

        return view('home', compact('articles', 'services'));
    }
}
