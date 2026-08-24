<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;

class SiteSettingPolicy
{
    use AuthorizesViaPermissions;

    protected function permissionPrefix(): string
    {
        return 'settings';
    }
}
