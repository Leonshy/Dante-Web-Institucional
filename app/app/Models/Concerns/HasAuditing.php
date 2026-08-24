<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Registro de auditoría estándar del proyecto (docs/05 §2 `activity_log`).
 * Todo modelo editorial usa este trait para que quede registrado quién
 * cambió qué y cuándo — requisito de seguridad no negociable dado el
 * antecedente de compromiso del sitio anterior (CLAUDE.md §2).
 */
trait HasAuditing
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
