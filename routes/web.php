<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/index.html', fn () => redirect('/', 301));

Route::get('/articles/{slug}.html', [ArticleController::class, 'legacy'])->name('articles.legacy');
Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');

Route::get('/forms/get-csrf-token.php', [ContactController::class, 'token'])->name('contact.token');
Route::post('/forms/contact.php', [ContactController::class, 'store'])->middleware('throttle:30,1')->name('contact.store');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/manifest.json', function () {
    $path = public_path('manifest.json');
    abort_unless(is_file($path), 404);

    return response()->file($path, [
        'Content-Type' => 'application/manifest+json; charset=UTF-8',
        'Cache-Control' => 'public, max-age=0, must-revalidate',
    ]);
})->name('manifest');
