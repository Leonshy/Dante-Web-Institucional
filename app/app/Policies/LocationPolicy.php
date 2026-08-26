<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;

class LocationPolicy
{
    use AuthorizesViaPermissions;

    protected function permissionPrefix(): string
    {
        return 'locations';
    }
}
