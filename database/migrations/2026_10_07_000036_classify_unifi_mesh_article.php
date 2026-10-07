<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $category = Category::firstOrCreate(
            ['language' => 'en', 'slug' => 'ubiquiti'],
            ['name' => 'Ubiquiti', 'translation_key' => (string) Str::uuid()]
        );
        $translation = Category::firstOrCreate(
            ['language' => 'fa', 'slug' => 'ubiquiti'],
            ['name' => 'یوبیکیوتی', 'translation_key' => $category->translation_key]
        );
        foreach ([$category, $translation] as $item) {
            $item->update(['accent_color' => Tag::BRAND_COLORS['ubiquiti']]);
        }
        Article::where('slug', 'ubiquiti-unifi-wireless-mesh-network')->get()->each(function (Article $article) use ($category): void {
            $article->update([
                'category_id' => $category->id,
                'presentation' => array_replace($article->presentation ?? [], [
                    'category_label_en' => 'Ubiquiti',
                    'category_label_fa' => 'یوبیکیوتی',
                    'filter_class' => 'filter-ubiquiti',
                ]),
            ]);
        });
    }

    public function down(): void
    {
        // Preserve editorial classification and colors during rollback.
    }
};
