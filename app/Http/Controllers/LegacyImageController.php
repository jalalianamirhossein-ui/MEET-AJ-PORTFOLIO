<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LegacyImageController extends Controller
{
    public function __invoke(string $path): RedirectResponse
    {
        $target = config('image-paths.legacy')[$path] ?? null;
        abort_unless(is_string($target) && is_file(public_path('assets/img/'.$target)), 404);

        return redirect('/assets/img/'.$target, 301);
    }
}
