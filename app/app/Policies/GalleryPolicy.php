<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;

class GalleryPolicy
{
    use AuthorizesViaPermissions;

    protected function permissionPrefix(): string
    {
        return 'galleries';
    }
}
