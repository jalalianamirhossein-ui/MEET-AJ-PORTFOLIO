<?php

use App\Models\Article;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $banner = '/assets/img/articles/banners/MikroTik Firewall Hardening Network Blueprint.png';
        DB::transaction(function () use ($banner): void {
            foreach (Article::where('slug', 'mikrotik-firewall-hardening-input-forward-chain')->get() as $article) {
                $presentation = $article->presentation ?? [];
                $presentation['thumbnail'] = $banner;
                $presentation['gallery'] = $banner;
                $presentation['image_alt'] = 'MikroTik Firewall Hardening Network Blueprint';
                $presentation['image_alt_en'] = 'MikroTik Firewall Hardening Network Blueprint';
                $presentation['image_alt_fa'] = 'هاردنینگ فایروال MikroTik با Input و Forward Chain';
                $seo = $article->seo_data ?? [];
                $seo['og_image'] = $banner;
                $seo['twitter_image'] = $banner;
                if (isset($seo['schema']) && is_array($seo['schema'])) {
                    $seo['schema']['image'] = $banner;
                }
                $article->fill([
                    'featured_image' => $banner,
                    'presentation' => $presentation,
                    'seo_data' => $seo,
                ]);
                if ($article->isDirty()) {
                    $article->save();
                }
            }
        });
    }

    public function down(): void
    {
        // Preserve the selected banner and subsequent editorial changes.
    }
};
