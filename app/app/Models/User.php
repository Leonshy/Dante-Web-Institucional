<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\Email\Concerns\InteractsWithEmailAuthentication;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasEmailAuthentication
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    use InteractsWithEmailAuthentication;

    /**
     * Cuentas del proveedor (webparaguay) que no pueden borrarse, desactivarse ni
     * cambiar de correo, por ninguna vía (panel, tinker, seeders). Su nombre y
     * contraseña solo los cambia la propia cuenta o la consola.
     */
    public const PROTECTED_EMAILS = [
        'webmaster@webparaguay.com',
    ];

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($user->isProtected()) {
                throw new \RuntimeException("El usuario {$user->email} está protegido y no puede eliminarse.");
            }
        });

        static::updating(function (User $user) {
            if (! in_array($user->getOriginal('email'), self::PROTECTED_EMAILS, true)) {
                return;
            }

            if ($user->isDirty('email') || ($user->isDirty('is_active') && ! $user->is_active)) {
                throw new \RuntimeException("El usuario {$user->getOriginal('email')} está protegido: no puede cambiar de correo ni desactivarse.");
            }

            $isOwnAccountOrConsole = app()->runningInConsole() || auth()->id() === $user->getKey();

            if (! $isOwnAccountOrConsole && $user->isDirty(['name', 'password'])) {
                throw new \RuntimeException("El usuario {$user->email} está protegido: solo puede modificarlo su propia cuenta.");
            }
        });
    }

    public function isProtected(): bool
    {
        return in_array($this->email, self::PROTECTED_EMAILS, true);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
