<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class)->withTimestamps();
    }

    public function path(): string
    {
        return '/articles?tag='.urlencode($this->slug);
    }

    public function displayName(): string
    {
        return match (strtolower((string) $this->slug)) {
            'vmware' => 'VMware',
            'mikrotik' => 'MikroTik',
            'devops' => 'DevOps',
            'linux' => 'Linux',
            'microsoft' => 'Microsoft',
            'windows-server' => 'Windows Server',
            default => (string) $this->name,
        };
    }

    protected static function booted(): void
    {
        static::saving(function (Tag $tag): void {
            $tag->slug = $tag->slug ?: Str::slug($tag->name);
            Validator::make($tag->attributesToArray(), [
                'name' => ['required', 'string', 'max:80', Rule::unique('tags')->ignore($tag->id)],
                'slug' => ['required', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('tags')->ignore($tag->id)],
            ])->validate();
        });
    }
};
