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
