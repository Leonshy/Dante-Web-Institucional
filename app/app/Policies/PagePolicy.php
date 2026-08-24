<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;

class PagePolicy
{
    use AuthorizesViaPermissions;

    protected function permissionPrefix(): string
    {
        return 'pages';
    }
}
