<?php

namespace App\Policies\Concerns;

use App\Models\User;

/**
 * Autorización granular por permiso, no por rol hardcodeado — a diferencia de
 * IPG, que solo verifica `hasAnyRole(['admin','editor'])` (docs/01 §A.3).
 * Cada Policy implementa permissionPrefix() (ej. "pages") y este trait resuelve
 * viewAny/view/create/update/delete/publish contra "{prefix}.{accion}".
 */
trait AuthorizesViaPermissions
{
    abstract protected function permissionPrefix(): string;

    public function viewAny(User $user): bool
    {
        return $user->can("{$this->permissionPrefix()}.view");
    }

    public function view(User $user, $model): bool
    {
        return $user->can("{$this->permissionPrefix()}.view");
    }

    public function create(User $user): bool
    {
        return $user->can("{$this->permissionPrefix()}.create");
    }

    public function update(User $user, $model): bool
    {
        return $user->can("{$this->permissionPrefix()}.update");
    }

    public function delete(User $user, $model): bool
    {
        return $user->can("{$this->permissionPrefix()}.delete");
    }

    public function publish(User $user, $model): bool
    {
        return $user->can("{$this->permissionPrefix()}.publish");
    }
}
