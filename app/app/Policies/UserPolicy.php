<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AuthorizesViaPermissions;

class UserPolicy
{
    use AuthorizesViaPermissions {
        update as private updateByPermission;
        delete as private deleteByPermission;
    }

    protected function permissionPrefix(): string
    {
        return 'users';
    }

    /** Las cuentas protegidas (User::PROTECTED_EMAILS) solo las edita su dueño. */
    public function update(User $user, $model): bool
    {
        if ($model instanceof User && $model->isProtected() && ! $user->is($model)) {
            return false;
        }

        return $this->updateByPermission($user, $model);
    }

    public function delete(User $user, $model): bool
    {
        if ($model instanceof User && $model->isProtected()) {
            return false;
        }

        return $this->deleteByPermission($user, $model);
    }
}
