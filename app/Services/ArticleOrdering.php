<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ArticleOrdering
{
    public function apply(Builder $query): Builder
    {
        // Publication time leads every list. A later insertion wins an exact
        // timestamp tie, including articles published on the same calendar day.
        return $query->orderByDesc('articles.published_at')
            ->orderByDesc('articles.id');
    }

    /** Persist the same public order, independently for each content language. */
    public function synchronize(): array
    {
        return DB::transaction(function (): array {
            $before = DB::table('articles')->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $result = [];
            foreach ($this->apply(Article::query())->get()->groupBy('language') as $language => $articles) {
                foreach ($articles->values() as $index => $article) {
                    // Direct update intentionally preserves updated_at and all content/SEO fields.
                    if ((int) $article->sort_order !== $index) {
                        DB::table('articles')->where('id', $article->id)->update(['sort_order' => $index]);
                    }
                    $result[] = ['language' => $language, 'sort_order' => $index, 'slug' => $article->slug];
                }
            }
            $after = DB::table('articles')->orderBy('id')->get()->keyBy('id');
            if ($before->keys()->all() !== $after->keys()->all()) {
                throw new RuntimeException('Article identities changed during ordering; rolling back.');
            }
            foreach ($before as $id => $row) {
                $old = (array) $row;
                $new = (array) $after[$id];
                unset($old['sort_order'], $new['sort_order']);
                if ($old !== $new) {
                    throw new RuntimeException('Non-ordering article fields changed; rolling back.');
                }
            }

            return $result;
        });
    }
}
