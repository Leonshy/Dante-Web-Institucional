<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;

class RedirectPolicy
{
    use AuthorizesViaPermissions;

    protected function permissionPrefix(): string
    {
        return 'redirects';
    }
}
