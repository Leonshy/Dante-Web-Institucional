<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;

class DocumentPolicy
{
    use AuthorizesViaPermissions;

    protected function permissionPrefix(): string
    {
        return 'documents';
    }
}
