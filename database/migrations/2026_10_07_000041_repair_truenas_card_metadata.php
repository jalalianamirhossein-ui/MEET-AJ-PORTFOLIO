<?php

use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $article = Article::where('slug', 'truenas-zfs-enterprise')->first();
        if (! $article) { return; }
        $presentation = $article->presentation ?? [];
        foreach ([
            'card_title_en' => 'TrueNAS Enterprise NAS with ZFS',
            'hero_title_en' => 'TrueNAS Enterprise NAS with ZFS',
            'card_excerpt_en' => 'Build a TrueNAS storage server with ZFS pools, datasets, SMB, NFS, iSCSI, snapshots, replication and a tested backup plan.',
        ] as $key => $value) {
            $current = trim((string) ($presentation[$key] ?? ''));
            if ($current === '' || preg_match('/\p{Arabic}/u', $current)) { $presentation[$key] = $value; }
        }
        $presentation['card_title_fa'] ??= $article->title;
        $presentation['card_excerpt_fa'] ??= $article->excerpt;
        $category = Category::where('slug', 'qnap')->where('language', 'en')->first();
        if ($category && in_array($article->category?->slug, ['others', 'qnap'], true)) {
            $article->category_id = $category->id;
            $presentation['filter_class'] = 'filter-qnap';
        }
        $article->presentation = $presentation;
        $article->saveQuietly();
    }

    public function down(): void
    {
        // Preserve editorial metadata when rolling back application code.
    }
};
