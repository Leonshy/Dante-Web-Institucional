<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;

class HomeSettingPolicy
{
    use AuthorizesViaPermissions;

    protected function permissionPrefix(): string
    {
        return 'settings';
    }
}
