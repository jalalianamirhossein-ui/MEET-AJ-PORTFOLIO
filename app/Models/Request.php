<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function isServiceRequest(): bool
    {
        return $this->service_id !== null;
    }

    public function typeLabel(): string
    {
        return $this->isServiceRequest() ? 'Service request' : 'Contact request';
    }

    public function inboxLabel(): string
    {
        return $this->status === 'new' ? 'New' : 'Processed';
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function scopeContacts(Builder $query): Builder
    {
        return $query->whereNull('service_id');
    }

    public function scopeServiceRequests(Builder $query): Builder
    {
        return $query->whereNotNull('service_id');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }

    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('status', '!=', 'new');
    }

    protected static function booted(): void
    {
        static::saving(fn (Request $request) => Validator::make(
            ['status' => $request->status],
            ['status' => ['required', Rule::in(array_keys(self::STATUSES))]]
        )->validate());
    }
}
