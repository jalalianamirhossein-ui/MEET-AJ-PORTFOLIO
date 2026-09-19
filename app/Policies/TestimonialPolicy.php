<?php

namespace App\Policies;

use App\Models\Testimonial;
use App\Models\User;

class TestimonialPolicy
{
    public function viewAny(User $user): bool { return $user->canManageContent(); }
    public function view(User $user, Testimonial $testimonial): bool { return $user->canManageContent(); }
    public function create(User $user): bool { return $user->canManageContent(); }
    public function update(User $user, Testimonial $testimonial): bool { return $user->canManageContent(); }
    public function delete(User $user, Testimonial $testimonial): bool { return $user->canManageContent(); }
    public function deleteAny(User $user): bool { return $user->canManageContent(); }
}
