<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ArticleOrdering
{
    private function priorities(): array
    {
        $enterprise = config('article-order.enterprise', []);
        $guides = config('article-order.guides', []);
        $slugs = array_merge($enterprise, $guides);
        if (count($slugs) !== count(array_unique($slugs))) {
            throw new RuntimeException('Duplicate slug in config/article-order.php.');
        }

        // Rank explicit editorial priorities after the automatic new-article
        // bucket. Any slug not yet listed in this file therefore appears first
        // by publication date, while established articles keep their curated order.
        $priorities = [];
        foreach ($enterprise as $index => $slug) {
            $priorities[$slug] = $index + 1;
        }
        $newArticlePriority = count($enterprise) + 1;
        foreach ($guides as $index => $slug) {
            $priorities[$slug] = $newArticlePriority + $index;
        }

        return [$priorities, $newArticlePriority];
    }

    public function apply(Builder $query): Builder
    {
        [$priorities, $default] = $this->priorities();
        $sql = 'CASE articles.slug';
        $bindings = [];
        foreach ($priorities as $slug => $rank) {
            $sql .= ' WHEN ? THEN ?';
            array_push($bindings, $slug, $rank);
        }
        $sql .= ' ELSE ? END';
        $bindings[] = 0;

        return $query->orderByRaw($sql, $bindings)
            ->orderByDesc('articles.published_at')
            ->orderBy('articles.sort_order')
            ->orderBy('articles.id');
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
