<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Service extends Model
{
    protected $fillable = [
        'title', 'slug', 'language', 'translation_key', 'short_description', 'description',
        'content', 'features', 'process', 'faq', 'price', 'price_currency', 'price_label',
        'price_type', 'featured_image', 'seo_title', 'seo_description', 'og_title',
        'og_description', 'presentation', 'sort_order', 'status', 'show_in_catalog', 'published_at',
    ];

    protected $attributes = [
        'language' => 'en',
        'status' => 'draft',
        'show_in_catalog' => true,
        'sort_order' => 0,
        'price_currency' => 'AED',
        'price_type' => 'fixed',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'features' => 'array',
            'process' => 'array',
            'faq' => 'array',
            'presentation' => 'array',
            'show_in_catalog' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereIn('language', config('cms.public_languages', ['en', 'fa']))
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopePublicCatalog(Builder $query): Builder
    {
        return $query->published()
            ->where('language', 'en')
            ->where('show_in_catalog', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function path(): string
    {
        return '/services/'.$this->slug;
    }

    public function legacyPath(): string
    {
        return $this->path().'.html';
    }

    public function publicUrl(): string
    {
        return rtrim((string) config('app.url'), '/').$this->path();
    }

    public function canonicalUrl(): string
    {
        return $this->publicUrl();
    }

    public function iconClass(): string
    {
        return (string) (data_get($this->presentation, 'icon') ?: 'bi bi-briefcase');
    }

    public function translated(string $field, string $locale = 'en'): string
    {
        if ($locale === 'fa') {
            return (string) (data_get($this->presentation, $field.'_fa') ?: $this->{$field} ?: '');
        }

        return (string) ($this->{$field} ?? '');
    }

    public function displayPrice(string $locale = 'en'): string
    {
        $type = $this->price_type ?: 'fixed';
        if ($type === 'custom_quote' || $this->price === null || $this->price === '') {
            return $locale === 'fa'
                ? (string) (data_get($this->presentation, 'quote_label_fa') ?: 'درخواست پیش‌فاکتور')
                : (string) ($this->price_label ?: 'Request a quote');
        }

        $amount = number_format((float) $this->price);
        $currency = $this->price_currency ?: 'AED';
        if ($locale === 'fa') {
            $stored = data_get($this->presentation, 'price_display_fa');
            if (is_string($stored) && $stored !== '' && $this->priceMatchesImported()) {
                return $stored;
            }
            $persian = $this->toPersianDigits($amount);

            return $type === 'starting_from'
                ? 'شروع از '.$persian.' درهم'
                : $persian.' درهم';
        }

        if ($type === 'starting_from') {
            return 'Starting from '.$amount.' '.$currency;
        }

        $storedEn = data_get($this->presentation, 'price_display_en');
        if (is_string($storedEn) && $storedEn !== '' && $this->priceMatchesImported()) {
            return $storedEn;
        }

        return $currency.' '.$amount;
    }

    public function priceTypeLabel(string $locale = 'en'): string
    {
        $labels = [
            'fixed' => ['en' => 'Fixed Price', 'fa' => 'قیمت ثابت'],
            'starting_from' => ['en' => 'Starting from', 'fa' => 'شروع از'],
            'custom_quote' => ['en' => 'Custom quote', 'fa' => 'پیش‌فاکتور سفارشی'],
        ];
        if ($this->price_label && $locale === 'en') {
            return (string) $this->price_label;
        }
        if ($locale === 'fa' && data_get($this->presentation, 'price_label_fa')) {
            return (string) data_get($this->presentation, 'price_label_fa');
        }

        return $labels[$this->price_type][$locale] ?? ($labels['fixed'][$locale] ?? 'Fixed Price');
    }

    public function publishedTranslations(): array
    {
        return static::query()
            ->published()
            ->where('translation_key', $this->translation_key)
            ->pluck('language')
            ->all();
    }

    public function save(array $options = []): bool
    {
        return DB::transaction(fn () => parent::save($options));
    }

    protected static function booted(): void
    {
        static::saving(function (Service $service) {
            $service->translation_key ??= (string) Str::uuid();
            $service->price_currency = $service->price_currency ?: 'AED';
            Validator::make($service->attributesToArray(), [
                'title' => ['required', 'string', 'max:255'],
                'language' => ['required', Rule::in(['en', 'fa', 'de'])],
                'slug' => ['required', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('services')->where('language', $service->language)->ignore($service->id)],
                'translation_key' => ['required', 'uuid', Rule::unique('services')->where('language', $service->language)->ignore($service->id)],
                'price_type' => ['required', Rule::in(['fixed', 'starting_from', 'custom_quote'])],
                'price_currency' => ['required', 'string', 'max:8'],
                'price' => ['nullable', 'numeric', 'min:0', 'required_unless:price_type,custom_quote'],
                'status' => ['required', Rule::in(['draft', 'published'])],
                'show_in_catalog' => ['boolean'],
                'published_at' => ['nullable', 'date', 'required_if:status,published'],
                'featured_image' => ['nullable', 'string', 'max:2048', 'not_regex:/\.(php|phtml|phar|exe|js)$/i'],
            ])->validate();
            if ($service->language === 'de' && $service->status !== 'draft') {
                throw ValidationException::withMessages(['status' => 'German content remains draft until real translations are approved for release.']);
            }
            if ($service->exists && $service->isDirty('language')) {
                throw ValidationException::withMessages(['language' => 'Create a separate translation; an existing service language cannot change.']);
            }
        });
    }

    private function priceMatchesImported(): bool
    {
        $imported = data_get($this->presentation, 'imported_price');
        if ($imported === null || $imported === '' || $this->price === null || $this->price === '') {
            return false;
        }

        return abs((float) $imported - (float) $this->price) < 0.001;
    }

    private function toPersianDigits(string $value): string
    {
        return strtr($value, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹', ',' => '٬']);
    }
}
