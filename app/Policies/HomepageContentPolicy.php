<?php

namespace App\Policies;

use App\Models\HomepageContent;
use App\Models\User;

class HomepageContentPolicy
{
    public function viewAny(User $user): bool { return $user->canManageContent(); }
    public function view(User $user, HomepageContent $content): bool { return $user->canManageContent(); }
    public function create(User $user): bool { return $user->canManageContent(); }
    public function update(User $user, HomepageContent $content): bool { return $user->canManageContent(); }
    public function delete(User $user, HomepageContent $content): bool { return $user->canManageContent(); }
}
