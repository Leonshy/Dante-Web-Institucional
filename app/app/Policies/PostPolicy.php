<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;

class PostPolicy
{
    use AuthorizesViaPermissions;

    protected function permissionPrefix(): string
    {
        return 'posts';
    }
}
