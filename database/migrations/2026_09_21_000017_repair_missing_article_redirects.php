<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $articles = DB::table('articles')
            ->where('language', 'en')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->get(['id', 'slug']);

        foreach ($articles as $article) {
            DB::table('article_redirects')->updateOrInsert(
                ['old_path' => '/articles/'.$article->slug.'.html'],
                ['article_id' => $article->id]
            );
        }
    }

    public function down(): void
    {
        // Redirects may have been created by editors after this repair;
        // leaving them in place is safer than deleting editorial URL history.
    }
};
