<?php

namespace App\Policies;

use App\User;

// What a platform admin may do on other accounts. Kept deliberately small: one admin role,
// no permission matrix. Admins never act on themselves nor on other admins, so a ban or an
// edit can never lock the platform out (see also User::activeAdminCount()).
class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->isActiveAdmin($actor);
    }

    public function view(User $actor, User $target): bool
    {
        return $this->isActiveAdmin($actor);
    }

    public function create(User $actor): bool
    {
        return $this->isActiveAdmin($actor);
    }

    public function update(User $actor, User $target): bool
    {
        return $this->canActOn($actor, $target);
    }

    public function ban(User $actor, User $target): bool
    {
        return $this->canActOn($actor, $target) && ! $target->isBanned();
    }

    public function unban(User $actor, User $target): bool
    {
        return $this->canActOn($actor, $target) && $target->isBanned();
    }

    private function isActiveAdmin(User $actor): bool
    {
        return $actor->isAdmin() && ! $actor->isBanned();
    }

    private function canActOn(User $actor, User $target): bool
    {
        return $this->isActiveAdmin($actor)
            && $actor->id !== $target->id
            // another admin is never edited nor banned here: the active admins can only grow
            // from this panel, so the platform cannot drop to zero of them
            && ! $target->isAdmin()
            // the demo accounts of the portfolio stay usable for every visitor (D6)
            && ! $target->isDemoAccount();
    }
}
