<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;

class MenuPolicy
{
    use AuthorizesViaPermissions;

    protected function permissionPrefix(): string
    {
        return 'menus';
    }
}
