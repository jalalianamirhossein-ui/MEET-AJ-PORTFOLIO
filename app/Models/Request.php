<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Request extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'in_discussion' => 'In discussion',
        'quoted' => 'Quoted',
        'approved' => 'Approved',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = ['name', 'email', 'phone', 'subject', 'message', 'status', 'service_id', 'internal_notes'];

    protected $hidden = ['internal_notes'];

    protected $attributes = ['status' => 'new'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    protected static function booted(): void
    {
        static::saving(fn (Request $request) => Validator::make(
            ['status' => $request->status],
            ['status' => ['required', Rule::in(array_keys(self::STATUSES))]]
        )->validate());
    }
}
