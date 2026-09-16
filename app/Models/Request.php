<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Request extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'subject', 'message', 'status', 'service_id'];

    protected $attributes = ['status' => 'new'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    protected static function booted(): void
    {
        static::saving(fn (Request $request) => Validator::make(['status' => $request->status], ['status' => ['required', Rule::in(['new', 'in_progress', 'resolved', 'spam'])]])->validate());
    }
}
