<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageContent();
    }

    public function view(User $user, Tag $tag): bool
    {
        return $user->canManageContent();
    }

    public function create(User $user): bool
    {
        return $user->canManageContent();
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->canManageContent();
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $user->canManageContent();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageContent();
    }
}
