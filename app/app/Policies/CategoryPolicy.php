<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;

class CategoryPolicy
{
    use AuthorizesViaPermissions;

    protected function permissionPrefix(): string
    {
        return 'categories';
    }
}
