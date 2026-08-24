<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;

class AnnouncementPolicy
{
    use AuthorizesViaPermissions;

    protected function permissionPrefix(): string
    {
        return 'announcements';
    }
}
