<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HomepageContent extends Model
{
    protected $fillable = ['key', 'label', 'content', 'is_published', 'sort_order'];

    protected $attributes = ['is_published' => true, 'sort_order' => 0];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function value(string $key, mixed $fallback = null): mixed
    {
        return data_get($this->content, $key, $fallback);
    }
}
