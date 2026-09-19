<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Category extends Model
{
    public const ACCENT_PATTERN = '/^#[0-9A-Fa-f]{6}$/';

    protected $fillable = ['name', 'slug', 'language', 'translation_key', 'accent_color', 'sort_order'];
    protected $attributes = ['language' => 'en', 'sort_order' => 0];
    public function articles(): HasMany { return $this->hasMany(Article::class); }

    /**
     * Public article topic key. Matches homepage/library accents; not a stored column.
     */
    public function topicKey(): string
    {
        return match ($this->slug) {
            'microsoft', 'windows-server' => 'microsoft',
            'linux' => 'linux',
            'mikrotik', 'networking' => 'mikrotik',
            'vmware' => 'vmware',
            'security' => 'security',
            'devops' => 'devops',
            default => 'others',
        };
    }

    /**
     * Stored #RRGGBB when valid; otherwise the existing slug palette.
     */
    public function accentColor(): string
    {
        return self::normalizeAccentColor($this->accent_color) ?? $this->fallbackAccentColor();
    }

    public function fallbackAccentColor(): string
    {
        return self::fallbackAccentColorForTopic($this->topicKey());
    }

    public static function fallbackAccentColorForTopic(string $topic): string
    {
        return match ($topic) {
            'microsoft' => '#2563eb',
            'linux' => '#15803d',
            'mikrotik' => '#c2410c',
            'vmware' => '#6d28d9',
            'security' => '#be123c',
            'devops' => '#0e7490',
            default => '#a16207',
        };
    }

    public static function normalizeAccentColor(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (preg_match(self::ACCENT_PATTERN, $value)) {
            return '#'.strtolower(substr($value, 1));
        }

        return null;
    }

    public static function accentColorForSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        if ($slug === 'other') {
            $slug = 'others';
        }

        $existing = static::query()
            ->where('slug', $slug)
            ->orderByRaw("case when language = 'en' then 0 else 1 end")
            ->first();

        if ($existing) {
            return $existing->accentColor();
        }

        $probe = new static(['slug' => $slug]);

        return $probe->fallbackAccentColor();
    }

    public function accentCustomProperties(): string
    {
        $color = $this->accentColor();

        return '--topic: '.$color.'; --meetaj-topic: '.$color.'; --article-primary: '.$color.'; --article-primary-strong: color-mix(in srgb, '.$color.' 78%, #0f172a); --article-bg-accent: color-mix(in srgb, '.$color.' 14%, transparent);';
    }

    public function accentChipHtml(?string $label = null): string
    {
        $color = $this->accentColor();
        $text = e($label ?? $this->name);

        return '<span class="meetaj-category-chip" style="--meetaj-topic: '.$color.'"><i class="meetaj-category-dot" aria-hidden="true"></i>'.$text.'</span>';
    }
    protected static function booted(): void {
        static::saving(function (Category $category) {
            $category->translation_key ??= (string) Str::uuid();
            $rawAccent = is_string($category->accent_color) ? trim($category->accent_color) : $category->accent_color;
            if (is_string($rawAccent) && $rawAccent !== '' && Category::normalizeAccentColor($rawAccent) === null) {
                throw ValidationException::withMessages([
                    'accent_color' => 'Accent color must be a #RRGGBB hex value.',
                ]);
            }
            $category->accent_color = self::normalizeAccentColor(is_string($rawAccent) ? $rawAccent : null);
            Validator::make($category->attributesToArray(), [
                'name' => ['required', 'string', 'max:255'], 'language' => ['required', Rule::in(['en', 'fa', 'de'])],
                'slug' => ['required', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('categories')->where('language', $category->language)->ignore($category->id)],
                'translation_key' => ['required', 'uuid', Rule::unique('categories')->where('language', $category->language)->ignore($category->id)],
                'accent_color' => ['nullable', 'string', 'size:7', 'regex:'.self::ACCENT_PATTERN],
            ])->validate();
            if ($category->exists && $category->isDirty('language') && $category->articles()->exists()) {
                throw ValidationException::withMessages(['language' => 'Create a translated category instead of changing the language of an assigned category.']);
            }
        });
    }
}
