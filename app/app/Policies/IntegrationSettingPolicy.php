<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;

class IntegrationSettingPolicy
{
    use AuthorizesViaPermissions;

    protected function permissionPrefix(): string
    {
        return 'settings';
    }
}
