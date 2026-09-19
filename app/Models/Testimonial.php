<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'quote_en', 'quote_fa', 'author_name', 'role_en', 'role_fa',
        'company_en', 'company_fa', 'avatar', 'sort_order', 'is_published',
    ];

    protected $attributes = ['sort_order' => 0, 'is_published' => true];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function avatarUrl(): string
    {
        $avatar = trim((string) $this->avatar);
        if ($avatar === '') {
            return '/assets/img/testimonials/testimonials-1.jpg';
        }
        if (preg_match('~^(?:https?:)?//~i', $avatar) || str_starts_with($avatar, '/')) {
            return $avatar;
        }
        if (str_starts_with($avatar, 'assets/') || str_starts_with($avatar, 'storage/')) {
            return '/'.$avatar;
        }

        return '/storage/'.$avatar;
    }
}
