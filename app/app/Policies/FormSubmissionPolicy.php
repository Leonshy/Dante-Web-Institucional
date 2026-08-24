<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;

class FormSubmissionPolicy
{
    use AuthorizesViaPermissions;

    protected function permissionPrefix(): string
    {
        return 'form_submissions';
    }
}
