<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Article extends Model
{
    protected $fillable = ['title', 'slug', 'language', 'translation_key', 'excerpt', 'content', 'featured_image', 'category_id', 'meta_title', 'meta_description', 'canonical_url', 'status', 'published_at'];
    protected $attributes = ['language' => 'en', 'status' => 'draft', 'sort_order' => 0];
    protected function casts(): array { return ['published_at' => 'datetime', 'presentation' => 'array', 'seo_data' => 'array']; }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function redirects(): HasMany { return $this->hasMany(ArticleRedirect::class); }
    public function scopePublished(Builder $query): Builder {
        return $query->whereIn('language', ['en', 'fa'])->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }
    public function path(): string { return self::pathFor($this->slug, $this->language); }
    public static function pathFor(string $slug, string $language): string {
        return ($language === 'en' ? '' : '/'.$language).'/articles/'.$slug;
    }
    public function publicUrl(): string { return rtrim(config('app.url'), '/').$this->path(); }
    public function imageUrl(): string {
        $image = $this->featured_image ?: '/assets/img/hero-bg.jpg';
        return rtrim(config('app.url'), '/').(str_starts_with($image, '/assets/') ? $image : '/storage/'.ltrim($image, '/'));
    }
    public function translatedText(string $field, string $locale): string {
        // An edited title/excerpt must not revert to obsolete imported data-* text.
        $original = data_get($this->presentation, 'original_'.$field);
        return $this->{$field} === $original ? (data_get($this->presentation, $field.'_translations.'.$locale) ?: (string) $this->{$field}) : (string) $this->{$field};
    }
    public function save(array $options = []): bool {
        // A shared-host file lock serializes route claims across the two route tables.
        return Cache::lock('cms-article-route-write', 30)->block(10, fn () => DB::transaction(fn () => parent::save($options)));
    }
    protected static function booted(): void {
        static::saving(function (Article $article) {
            $article->translation_key ??= (string) Str::uuid();
            Validator::make($article->attributesToArray(), [
                'title' => ['required', 'string', 'max:255'], 'language' => ['required', Rule::in(['en', 'fa', 'de'])],
                'slug' => ['required', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('articles')->where('language', $article->language)->ignore($article->id)],
                'translation_key' => ['required', 'uuid', Rule::unique('articles')->where('language', $article->language)->ignore($article->id)],
                'content' => ['required', 'string'], 'excerpt' => ['nullable', 'string'],
                'category_id' => ['nullable', Rule::exists('categories', 'id')->where('language', $article->language)],
                'meta_title' => ['nullable', 'string', 'max:255'], 'meta_description' => ['nullable', 'string'],
                'status' => ['required', Rule::in(['draft', 'published'])], 'published_at' => ['nullable', 'date', 'required_if:status,published'],
                'featured_image' => ['nullable', 'string', 'max:2048', 'regex:~^(?:/assets/img/|articles/)[a-zA-Z0-9/_-]+\.(?:jpg|jpeg|png|webp)$~'],
            ])->validate();
            if ($article->language === 'de' && $article->status !== 'draft') {
                throw ValidationException::withMessages(['status' => 'German content remains draft until real translations are approved for release.']);
            }
            if ($article->exists && $article->isDirty('language')) {
                throw ValidationException::withMessages(['language' => 'Create a separate translation; an existing article language cannot change.']);
            }
            if ($article->canonical_url && $article->canonical_url !== $article->publicUrl()) {
                throw ValidationException::withMessages(['canonical_url' => 'Use this article’s current public URL or leave blank for automatic canonical handling.']);
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
