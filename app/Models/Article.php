<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Article extends Model
{
    protected $fillable = [
        'title', 'slug', 'language', 'translation_key', 'excerpt', 'content', 'featured_image',
        'category_id', 'meta_title', 'meta_description', 'canonical_url', 'seo_data',
        'presentation', 'sort_order', 'status', 'published_at',
    ];

    protected $attributes = ['language' => 'en', 'status' => 'draft', 'sort_order' => 0];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'presentation' => 'array',
            'seo_data' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function redirects(): HasMany
    {
        return $this->hasMany(ArticleRedirect::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function scopeForListing(Builder $query): Builder
    {
        return $query->select([
            'articles.id',
            'articles.title',
            'articles.slug',
            'articles.language',
            'articles.excerpt',
            'articles.featured_image',
            'articles.category_id',
            'articles.presentation',
            'articles.sort_order',
            'articles.status',
            'articles.published_at',
        ]);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);
        if ($term === '') {
            return $query;
        }

        $like = '%'.addcslashes($term, '%_\\').'%';

        return $query->where(function (Builder $inner) use ($like): void {
            $inner->where('title', 'like', $like)
                ->orWhere('excerpt', 'like', $like)
                ->orWhere('content', 'like', $like)
                ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', $like))
                ->orWhereHas('tags', fn (Builder $tag) => $tag->where('name', 'like', $like)->orWhere('slug', 'like', $like));
        });
    }

    public function scopeWithTag(Builder $query, string $slug): Builder
    {
        $slug = trim($slug);
        if ($slug === '') {
            return $query;
        }

        return $query->whereHas('tags', fn (Builder $tag) => $tag->where('slug', $slug));
    }

    /**
     * @return \Illuminate\Support\Collection<int, Article>
     */
    public function relatedArticles(int $limit = 3)
    {
        $exclude = [$this->id];
        $query = static::published()
            ->where('language', $this->language)
            ->whereKeyNot($exclude)
            ->with(['category', 'tags']);

        $tagIds = $this->tags()->pluck('tags.id');
        $found = collect();

        if ($tagIds->isNotEmpty()) {
            $found = (clone $query)
                ->whereHas('tags', fn (Builder $tag) => $tag->whereIn('tags.id', $tagIds))
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get();
        }

        if ($found->count() < $limit && $this->category_id) {
            $needed = $limit - $found->count();
            $more = (clone $query)
                ->where('category_id', $this->category_id)
                ->whereNotIn('id', $found->pluck('id')->all())
                ->orderByDesc('published_at')
                ->limit($needed)
                ->get();
            $found = $found->concat($more);
        }

        if ($found->count() < $limit) {
            $needed = $limit - $found->count();
            $more = (clone $query)
                ->whereNotIn('id', $found->pluck('id')->all())
                ->orderByDesc('published_at')
                ->limit($needed)
                ->get();
            $found = $found->concat($more);
        }

        return $found->take($limit)->values();
    }

    public function readingMinutes(): int
    {
        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $this->content), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?: '');
        $words = $text === '' ? 0 : count(preg_split('/\s+/', $text) ?: []);

        return max(1, (int) ceil($words / 200));
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereIn('language', ['en', 'fa'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function path(): string
    {
        return self::pathFor($this->slug, $this->language);
    }

    public static function pathFor(string $slug, string $language): string
    {
        // Production public articles stay on unprefixed English/default URLs.
        // German never receives a public route.
        return '/articles/'.$slug;
    }

    public function publicUrl(): string
    {
        return rtrim((string) config('app.url'), '/').$this->path();
    }

    public function canonicalUrl(): string
    {
        return $this->canonical_url ?: $this->publicUrl();
    }

    public function imageUrl(): string
    {
        $image = $this->featured_image ?: data_get($this->presentation, 'thumbnail') ?: '/assets/img/hero-bg.jpg';
        if (str_starts_with((string) $image, 'http://') || str_starts_with((string) $image, 'https://')) {
            return $image;
        }
        if (str_starts_with((string) $image, '/')) {
            return rtrim((string) config('app.url'), '/').$image;
        }

        return rtrim((string) config('app.url'), '/').'/storage/'.ltrim((string) $image, '/');
    }

    public function galleryUrl(): string
    {
        $image = data_get($this->presentation, 'gallery') ?: $this->featured_image ?: '/assets/img/hero-bg.jpg';
        if (str_starts_with((string) $image, '/')) {
            return $image;
        }

        return '/'.ltrim((string) $image, '/');
    }

    public function thumbnailUrl(): string
    {
        $image = data_get($this->presentation, 'thumbnail') ?: $this->featured_image ?: '/assets/img/hero-bg.jpg';

        return str_starts_with((string) $image, '/') ? $image : '/'.ltrim((string) $image, '/');
    }

    public function filterClass(): string
    {
        return (string) (data_get($this->presentation, 'filter_class') ?: 'filter-others');
    }

    public function accentColor(): string
    {
        if ($this->category) {
            return $this->category->accentColor();
        }

        $slug = strtolower(str_replace('filter-', '', $this->filterClass()));

        return Category::accentColorForSlug($slug);
    }

    public function accentCustomProperties(): string
    {
        if ($this->category) {
            return $this->category->accentCustomProperties();
        }

        $color = $this->accentColor();

        return '--topic: '.$color.'; --meetaj-topic: '.$color.'; --article-primary: '.$color.'; --article-primary-strong: color-mix(in srgb, '.$color.' 78%, #0f172a); --article-bg-accent: color-mix(in srgb, '.$color.' 14%, transparent);';
    }

    public function categoryLabelEn(): string
    {
        $stored = trim((string) data_get($this->presentation, 'category_label_en'));

        return $stored !== '' ? $stored : $this->localizedCategoryLabel('en');
    }

    public function categoryLabelFa(): string
    {
        $stored = trim((string) data_get($this->presentation, 'category_label_fa'));

        return $stored !== '' ? $stored : $this->localizedCategoryLabel('fa');
    }

    protected function localizedCategoryLabel(string $locale): string
    {
        $name = trim((string) ($this->category?->name ?? ''));
        $slug = strtolower((string) ($this->category?->slug ?? ''));
        $filter = strtolower(str_replace('filter-', '', $this->filterClass()));
        $key = $slug !== '' ? $slug : $filter;
        $map = [
            'microsoft' => ['en' => 'Microsoft', 'fa' => 'مایکروسافت'],
            'linux' => ['en' => 'Linux', 'fa' => 'لینوکس'],
            'mikrotik' => ['en' => 'MikroTik', 'fa' => 'میکروتیک'],
            'vmware' => ['en' => 'VMware', 'fa' => 'مجازی‌سازی'],
            'other' => ['en' => 'Other', 'fa' => 'سایر'],
            'others' => ['en' => 'Other', 'fa' => 'سایر'],
        ];

        foreach ($map as $needle => $labels) {
            if ($key === $needle || str_contains($key, $needle) || strcasecmp($name, $labels['en']) === 0) {
                return $labels[$locale] ?? $labels['en'];
            }
        }

        if ($locale === 'en') {
            return $name !== '' ? $name : 'Article';
        }

        return $this->categoryLabelEn();
    }

    public function translatedText(string $field, string $locale): string
    {
        $original = data_get($this->presentation, 'original_'.$field);

        return $this->{$field} === $original
            ? (data_get($this->presentation, $field.'_translations.'.$locale) ?: (string) $this->{$field})
            : (string) $this->{$field};
    }

    public function save(array $options = []): bool
    {
        return DB::transaction(fn () => parent::save($options));
    }

    protected static function booted(): void
    {
        static::saving(function (Article $article) {
            $article->translation_key ??= (string) Str::uuid();
            Validator::make($article->attributesToArray(), [
                'title' => ['required', 'string', 'max:255'],
                'language' => ['required', Rule::in(['en', 'fa', 'de'])],
                'slug' => ['required', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('articles')->where('language', $article->language)->ignore($article->id)],
                'translation_key' => ['required', 'uuid', Rule::unique('articles')->where('language', $article->language)->ignore($article->id)],
                'content' => ['required', 'string'],
                'excerpt' => ['nullable', 'string'],
                'category_id' => ['nullable', Rule::exists('categories', 'id')->where('language', $article->language)],
                'meta_title' => ['nullable', 'string', 'max:255'],
                'meta_description' => ['nullable', 'string'],
                'status' => ['required', Rule::in(['draft', 'published'])],
                'published_at' => ['nullable', 'date', 'required_if:status,published'],
                'featured_image' => ['nullable', 'string', 'max:2048', 'not_regex:/\.(php|phtml|phar|exe|js)$/i'],
            ])->validate();
            if ($article->language === 'de' && $article->status !== 'draft') {
                throw ValidationException::withMessages(['status' => 'German content remains draft until real translations are approved for release.']);
            }
            if ($article->exists && $article->isDirty('language')) {
                throw ValidationException::withMessages(['language' => 'Create a separate translation; an existing article language cannot change.']);
            }
            if ($article->canonical_url) {
                $allowed = [$article->publicUrl(), 'https://meetaj.ir'.$article->path()];
                if (! in_array($article->canonical_url, $allowed, true)) {
                    throw ValidationException::withMessages(['canonical_url' => 'Use this article’s current public URL or leave blank for automatic canonical handling.']);
                }
            }
            $paths = [$article->path(), $article->path().'.html'];
            if (ArticleRedirect::whereIn('old_path', $paths)->where('article_id', '!=', $article->id ?? 0)->exists()) {
                throw ValidationException::withMessages(['slug' => 'This URL is reserved by an existing article redirect.']);
            }
        });
        static::updated(function (Article $article) {
            if ($article->wasChanged('slug')) {
                $path = self::pathFor($article->getOriginal('slug'), $article->language);
                foreach ([$path, $path.'.html'] as $oldPath) {
                    ArticleRedirect::firstOrCreate(['old_path' => $oldPath], ['article_id' => $article->id]);
                }
            }
        });
    }
}
