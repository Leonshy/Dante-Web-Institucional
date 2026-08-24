<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;

class CalendarEventPolicy
{
    use AuthorizesViaPermissions;

    protected function permissionPrefix(): string
    {
        return 'calendar_events';
    }
}
