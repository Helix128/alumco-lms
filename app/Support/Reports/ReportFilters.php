<?php

namespace App\Support\Reports;

class ReportFilters
{
    /**
     * @param  array<int, int>  $sedeIds
     * @param  array<int, int>  $estamentoIds
     * @param  array<int, int>  $courseIds
     */
    public function __construct(
        public readonly array $sedeIds,
        public readonly array $estamentoIds,
        public readonly array $courseIds,
        public readonly ?int $edadMin,
        public readonly ?int $edadMax,
        public readonly ?string $fechaInicio,
        public readonly ?string $fechaFin,
        public readonly string $estadoCapacitacion
    ) {}

    /**
     * @param  array<string, mixed>  $validatedInput
     */
    public static function fromValidatedInput(array $validatedInput): self
    {
        return new self(
            sedeIds: self::sanitizeIds($validatedInput['sede_id'] ?? []),
            estamentoIds: self::sanitizeIds($validatedInput['estamento_id'] ?? []),
            courseIds: self::sanitizeIds($validatedInput['curso_id'] ?? []),
            edadMin: is_numeric($validatedInput['edad_min'] ?? null) ? (int) $validatedInput['edad_min'] : null,
            edadMax: is_numeric($validatedInput['edad_max'] ?? null) ? (int) $validatedInput['edad_max'] : null,
            fechaInicio: $validatedInput['fecha_inicio'] ?? null,
            fechaFin: $validatedInput['fecha_fin'] ?? null,
            estadoCapacitacion: (string) ($validatedInput['estado_capacitacion'] ?? ''),
        );
    }

    public function usesSingleCourseStatusContext(): bool
    {
        return $this->estadoCapacitacion !== '' && count($this->courseIds) === 1;
    }

    /**
     * @return array<int, int>
     */
    private static function sanitizeIds(mixed $ids): array
    {
        $rawIds = is_array($ids) ? $ids : [$ids];

        return array_values(array_unique(array_filter(
            array_map('intval', $rawIds),
            fn (int $id): bool => $id > 0
        )));
    }
}
