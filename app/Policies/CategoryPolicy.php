<?php
namespace App\Policies;
use App\Models\{User, Category};
class CategoryPolicy
{
    public function viewAny(User $user): bool { return $user->canManageContent(); }
    public function view(User $user, Category $category): bool { return $user->canManageContent(); }
    public function create(User $user): bool { return $user->canManageContent(); }
    public function update(User $user, Category $category): bool { return $user->canManageContent(); }
    public function delete(User $user, Category $category): bool { return $user->canManageContent(); }
    public function deleteAny(User $user): bool { return $user->canManageContent(); }
}
