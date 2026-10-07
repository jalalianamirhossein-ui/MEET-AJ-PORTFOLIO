<?php

use App\Models\Article;
use App\Services\ImagePaths;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            foreach (Article::where('slug', 'mongodb-installation-configuration-production-deployment')->get() as $article) {
                $changes = [];
                foreach (['content', 'featured_image', 'presentation', 'seo_data'] as $field) {
                    $before = $article->getAttribute($field);
                    $after = ImagePaths::data($before);
                    if ($after !== $before) {
                        $changes[$field] = $after;
                    }
                }
                if ($changes !== []) {
                    $article->update($changes);
                }
            }
        });
    }

    public function down(): void
    {
        // Keep valid image references and preserve subsequent editorial changes.
    }
};
