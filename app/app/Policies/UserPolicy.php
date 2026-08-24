<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;

class UserPolicy
{
    use AuthorizesViaPermissions;

    protected function permissionPrefix(): string
    {
        return 'users';
    }
}
