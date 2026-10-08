<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    public function save(array $options = []): bool
    {
        return DB::transaction(fn () => parent::save($options));
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->exists && $user->isDirty('role') && $user->getOriginal('role') === 'admin'
                && $user->role !== 'admin' && ! static::where('role', 'admin')->lockForUpdate()->get()->contains(fn (User $admin) => ! $admin->is($user))) {
                throw ValidationException::withMessages(['role' => 'Keep at least one administrator account.']);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canManageContent(): bool
    {
        return in_array($this->role, ['admin', 'editor'], true);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canManageContent();
    }
}
