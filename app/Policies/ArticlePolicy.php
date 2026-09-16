<?php
namespace App\Policies;
use App\Models\{User, Article};
class ArticlePolicy
{
    public function viewAny(User $user): bool { return $user->canManageContent(); }
    public function view(User $user, Article $article): bool { return $user->canManageContent(); }
    public function create(User $user): bool { return $user->canManageContent(); }
    public function update(User $user, Article $article): bool { return $user->canManageContent(); }
    public function delete(User $user, Article $article): bool { return $user->canManageContent(); }
    public function deleteAny(User $user): bool { return $user->canManageContent(); }
}
