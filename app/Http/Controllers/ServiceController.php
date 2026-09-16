<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function legacy(Request $request, string $slug): RedirectResponse
    {
        $target = '/services/'.$slug;
        if ($request->getQueryString()) {
            $target .= '?'.$request->getQueryString();
        }

        return redirect($target, 301);
    }

    public function show(string $slug): View
    {
        $service = Service::query()
            ->published()
            ->where('language', 'en')
            ->where('slug', $slug)
            ->firstOrFail();

        $catalog = Service::query()->publicCatalog()->get();

        return view('services.show', compact('service', 'catalog'));
    }
}
