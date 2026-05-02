<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Support\AccessibilityPreferences;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'rut', 'password', 'fecha_nacimiento', 'sexo', 'activo', 'accessibility_preferences', 'firma_digital', 'sede_id', 'estamento_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return Attribute<array{fontLevel: int, highContrast: bool, reducedMotion: bool}, array<string, mixed>|null>
     */
    protected function accessibilityPreferences(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): array {
                $decoded = is_array($value) ? $value : json_decode($value ?? '[]', true);

                return AccessibilityPreferences::normalize(is_array($decoded) ? $decoded : null);
            },
            set: fn (?array $value): string => json_encode(
                AccessibilityPreferences::normalize($value),
                JSON_THROW_ON_ERROR
            ),
        );
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function estamento()
    {
        return $this->belongsTo(Estamento::class);
    }

    public function cursosImpartidos()
    {
        return $this->hasMany(Curso::class, 'capacitador_id');
    }

    public function certificados()
    {
        return $this->hasMany(Certificado::class);
    }

    public function progresos()
    {
        return $this->hasMany(ProgresoModulo::class);
    }

    public function isDesarrollador(): bool
    {
        return $this->hasRole('Desarrollador');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Administrador');
    }

    public function hasAdminAccess(): bool
    {
        return $this->isDesarrollador() || $this->isAdmin();
    }

    public function isCapacitadorInterno(): bool
    {
        return $this->hasRole('Capacitador Interno');
    }

    public function isCapacitadorExterno(): bool
    {
        return $this->hasRole('Capacitador Externo');
    }

    public function isCapacitador(): bool
    {
        return $this->hasAnyRole(['Capacitador Interno', 'Capacitador Externo']);
    }

    public function isTrabajador(): bool
    {
        return $this->hasRole('Trabajador');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function getHierarchyRank(): int
    {
        if ($this->isDesarrollador()) {
            return 3;
        }
        if ($this->isAdmin()) {
            return 2;
        }

        return 1;
    }

    public function canManageUser(User $targetUser): bool
    {
        // Regla 1: No puedes gestionarte a ti mismo en este panel
        if ($this->id === $targetUser->id) {
            return false;
        }

        $myRank = $this->getHierarchyRank();
        $targetRank = $targetUser->getHierarchyRank();

        // Regla 2: El Desarrollador puede gestionar a todos los demás
        if ($this->isDesarrollador()) {
            return true;
        }

        // Regla 3: Solo puedes gestionar a rangos ESTRICTAMENTE inferiores
        return $myRank > $targetRank;
    }
}
