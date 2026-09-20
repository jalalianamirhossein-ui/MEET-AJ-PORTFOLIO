<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class Tag extends Model
{
    public const BRAND_FILTERS = [
        'microsoft', 'cisco', 'vmware', 'mikrotik', 'fortinet', 'linux',
        'supermicro', 'hpe', 'ubiquiti', 'juniper', 'avaya', 'qnap', 'dell', 'other',
    ];
    public const BRAND_COLORS = [
        'netbox' => '#263238', 'microsoft' => '#0078D4', 'windows' => '#00A4EF', 'cisco' => '#049FD9',
        'linux' => '#FCC624', 'ubuntu' => '#E95420',
        'mikrotik' => '#293239', 'vmware' => '#607078', 'esxi' => '#F59E0B', 'vsphere' => '#4B5563',
        'fortinet' => '#EE3124', 'supermicro' => '#2B579A', 'hpe' => '#01A982', 'ubiquiti' => '#0559C9',
        'juniper' => '#0096A6', 'avaya' => '#DA291C', 'qnap' => '#6F2DA8', 'dell' => '#007DB8', 'other' => '#A16207',
        'nginx' => '#009639', 'oxidized' => '#CC342D', 'sql-server' => '#CC2927',
        'openvpn' => '#EA7E20', 'ssh' => '#222222',
    ];
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

    public function accentColor(): string
    {
        return self::BRAND_COLORS[$this->slug] ?? '#64748b';
    }

    public function isBrandFilter(): bool
    {
        return in_array($this->slug, self::BRAND_FILTERS, true);
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
