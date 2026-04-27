<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\ResetPasswordNotification;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'fecha_nacimiento', 'sexo', 'activo', 'firma_digital', 'sede_id', 'estamento_id'
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
        return $this->hasMany(Curso::class , 'capacitador_id');
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
}