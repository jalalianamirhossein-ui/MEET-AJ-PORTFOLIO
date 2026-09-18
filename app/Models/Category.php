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
    protected $fillable = ['name', 'slug', 'language', 'translation_key'];
    protected $attributes = ['language' => 'en'];
    public function articles(): HasMany { return $this->hasMany(Article::class); }

    /**
     * Public article topic key. Matches homepage/library accents; not a stored column.
     */
    public function topicKey(): string
    {
        return match ($this->slug) {
            'microsoft', 'windows-server' => 'microsoft',
            'linux' => 'linux',
            'mikrotik' => 'mikrotik',
            'vmware' => 'vmware',
            'security' => 'security',
            'devops' => 'devops',
            default => 'others',
        };
    }

    /**
     * Existing public topic hex values from the site design system.
     */
    public function accentColor(): string
    {
        return match ($this->topicKey()) {
            'microsoft' => '#2563eb',
            'linux' => '#15803d',
            'mikrotik' => '#c2410c',
            'vmware' => '#6d28d9',
            'security' => '#be123c',
            'devops' => '#0e7490',
            default => '#a16207',
        };
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
            Validator::make($category->attributesToArray(), [
                'name' => ['required', 'string', 'max:255'], 'language' => ['required', Rule::in(['en', 'fa', 'de'])],
                'slug' => ['required', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('categories')->where('language', $category->language)->ignore($category->id)],
                'translation_key' => ['required', 'uuid', Rule::unique('categories')->where('language', $category->language)->ignore($category->id)],
            ])->validate();
            if ($category->exists && $category->isDirty('language') && $category->articles()->exists()) {
                throw ValidationException::withMessages(['language' => 'Create a translated category instead of changing the language of an assigned category.']);
            }
        });
    }
}
