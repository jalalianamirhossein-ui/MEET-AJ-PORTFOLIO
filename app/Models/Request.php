<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class Request extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'subject', 'message', 'status'];
    protected $attributes = ['status' => 'new'];
    protected static function booted(): void {
        static::saving(fn (Request $request) => Validator::make(['status' => $request->status], ['status' => ['required', Rule::in(['new', 'in_progress', 'resolved', 'spam'])]])->validate());
    }
}
