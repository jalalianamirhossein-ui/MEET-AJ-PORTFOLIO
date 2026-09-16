<?php
namespace App\Policies;
use App\Models\{User, Request};
class RequestPolicy
{
    public function viewAny(User $user): bool { return $user->isAdmin(); }
    public function view(User $user, Request $request): bool { return $user->isAdmin(); }
    public function create(User $user): bool { return false; }
    public function update(User $user, Request $request): bool { return $user->isAdmin(); }
    public function delete(User $user, Request $request): bool { return $user->isAdmin(); }
    public function deleteAny(User $user): bool { return $user->isAdmin(); }
}
