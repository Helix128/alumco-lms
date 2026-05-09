<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasFactory;

    public const StatusNew = 'nuevo';

    public const StatusInReview = 'en_revision';

    public const StatusWaitingUser = 'esperando_usuario';

    public const StatusResolved = 'resuelto';

    public const StatusClosed = 'cerrado';

    public const PriorityLow = 'baja';

    public const PriorityMedium = 'media';

    public const PriorityHigh = 'alta';

    public const PriorityCritical = 'critica';

    public const CategoryAccess = 'acceso';

    public const CategoryPlatformError = 'error_plataforma';

    public const CategoryCourseContent = 'curso_o_contenido';

    public const CategoryCertificates = 'certificados';

    public const CategoryAccount = 'cuenta';

    public const CategoryOther = 'otro';

    /**
     * @var array<int, string>
     */
    public const Statuses = [
        self::StatusNew,
        self::StatusInReview,
        self::StatusWaitingUser,
        self::StatusResolved,
        self::StatusClosed,
    ];

    /**
     * @var array<int, string>
     */
    public const Priorities = [
        self::PriorityLow,
        self::PriorityMedium,
        self::PriorityHigh,
        self::PriorityCritical,
    ];

    /**
     * @var array<int, string>
     */
    public const Categories = [
        self::CategoryAccess,
        self::CategoryPlatformError,
        self::CategoryCourseContent,
        self::CategoryCertificates,
        self::CategoryAccount,
        self::CategoryOther,
    ];

    protected $fillable = [
        'requester_user_id',
        'assigned_to_id',
        'contact_name',
        'contact_email',
        'subject',
        'description',
        'category',
        'priority',
        'status',
        'technical_context',
        'last_activity_at',
        'resolved_at',
        'closed_at',
    ];

    protected $attributes = [
        'priority' => self::PriorityMedium,
        'status' => self::StatusNew,
    ];

    protected function casts(): array
    {
        return [
            'technical_context' => 'array',
            'last_activity_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SupportTicketAttachment::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::StatusResolved, self::StatusClosed]);
    }

    public function requesterName(): string
    {
        return $this->requester?->name ?? $this->contact_name ?? 'Solicitante';
    }

    public function requesterEmail(): ?string
    {
        return $this->requester?->email ?? $this->contact_email;
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::StatusNew => 'Nuevo',
            self::StatusInReview => 'En revisión',
            self::StatusWaitingUser => 'Esperando usuario',
            self::StatusResolved => 'Resuelto',
            self::StatusClosed => 'Cerrado',
            default => $status,
        };
    }

    public static function priorityLabel(string $priority): string
    {
        return match ($priority) {
            self::PriorityLow => 'Baja',
            self::PriorityMedium => 'Media',
            self::PriorityHigh => 'Alta',
            self::PriorityCritical => 'Crítica',
            default => $priority,
        };
    }

    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            self::CategoryAccess => 'Acceso',
            self::CategoryPlatformError => 'Error de plataforma',
            self::CategoryCourseContent => 'Curso o contenido',
            self::CategoryCertificates => 'Certificados',
            self::CategoryAccount => 'Cuenta',
            self::CategoryOther => 'Otro',
            default => $category,
        };
    }
}
