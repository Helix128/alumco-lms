<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'descripcion',
        'imagen_portada',
        'color_promedio',
        'capacitador_id',
        'curso_original_id',
    ];

    protected static function booted()
    {
        static::saving(function (Curso $curso) {
            // Solo extraer si la imagen cambió y no se proporcionó un color manual (vía controller)
            if ($curso->isDirty('imagen_portada') && empty($curso->color_promedio)) {
                $curso->color_promedio = $curso->extraerColorPromedio();
            }
        });
    }

    public function extraerColorPromedio(): ?string
    {
        if (! $this->imagen_portada) {
            return null;
        }

        $path = storage_path('app/public/'.$this->imagen_portada);

        if (! file_exists($path)) {
            return null;
        }

        try {
            $info = getimagesize($path);
            if (! $info) {
                return null;
            }

            $type = $info[2];
            $img = match ($type) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($path),
                IMAGETYPE_PNG => imagecreatefrompng($path),
                IMAGETYPE_WEBP => imagecreatefromwebp($path),
                default => null
            };

            if (! $img) {
                return null;
            }

            // Redimensionar a 20x20 para obtener una muestra manejable (400 píxeles)
            $sampleW = 20;
            $sampleH = 20;
            $tmp = imagecreatetruecolor($sampleW, $sampleH);
            imagecopyresampled($tmp, $img, 0, 0, 0, 0, $sampleW, $sampleH, imagesx($img), imagesy($img));

            $buckets = [];
            for ($x = 0; $x < $sampleW; $x++) {
                for ($y = 0; $y < $sampleH; $y++) {
                    $rgb = imagecolorat($tmp, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    [$h, $s, $l] = $this->rgbToHsl($r, $g, $b);

                    // Filtramos colores muy oscuros, muy claros o muy grises
                    if ($l > 0.15 && $l < 0.85 && $s > 0.15) {
                        // Agrupamos por tono (36 buckets de 10 grados cada uno)
                        $bucketIdx = (int) round($h * 36);
                        if (! isset($buckets[$bucketIdx])) {
                            $buckets[$bucketIdx] = ['r' => 0, 'g' => 0, 'b' => 0, 'count' => 0];
                        }
                        $buckets[$bucketIdx]['r'] += $r;
                        $buckets[$bucketIdx]['g'] += $g;
                        $buckets[$bucketIdx]['b'] += $b;
                        $buckets[$bucketIdx]['count']++;
                    }
                }
            }

            if (empty($buckets)) {
                // Fallback si la imagen es escala de grises o muy oscura/clara
                return '#1a3a5a';
            }

            // Encontramos el bucket con más píxeles (tono dominante)
            $dominant = array_reduce($buckets, function ($carry, $item) {
                return ($item['count'] > ($carry['count'] ?? 0)) ? $item : $carry;
            }, ['count' => 0]);

            // Promediamos el RGB dentro de ese tono dominante
            $r = $dominant['r'] / $dominant['count'];
            $g = $dominant['g'] / $dominant['count'];
            $b = $dominant['b'] / $dominant['count'];

            // Normalización estética final
            [$h, $s, $l] = $this->rgbToHsl($r, $g, $b);

            // Lightness: Aseguramos contraste para texto blanco (rango 20%-40%)
            $l = max(0.20, min($l, 0.40));

            // Saturation: Boost si es muy apagado
            $s = max($s, 0.45);

            [$r, $g, $b] = $this->hslToRgb($h, $s, $l);

            return sprintf('#%02x%02x%02x', $r, $g, $b);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function rgbToHsl($r, $g, $b)
    {
        $r /= 255;
        $g /= 255;
        $b /= 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        if ($max == $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            switch ($max) {
                case $r: $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g: $h = ($b - $r) / $d + 2;
                    break;
                case $b: $h = ($r - $g) / $d + 4;
                    break;
            }
            $h /= 6;
        }

        return [$h, $s, $l];
    }

    private function hslToRgb($h, $s, $l)
    {
        if ($s == 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = $this->hue2rgb($p, $q, $h + 1 / 3);
            $g = $this->hue2rgb($p, $q, $h);
            $b = $this->hue2rgb($p, $q, $h - 1 / 3);
        }

        return [round($r * 255), round($g * 255), round($b * 255)];
    }

    private function hue2rgb($p, $q, $t)
    {
        if ($t < 0) {
            $t += 1;
        }
        if ($t > 1) {
            $t -= 1;
        }
        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < 1 / 2) {
            return $q;
        }
        if ($t < 2 / 3) {
            return $p + ($q - $p) * (2 / 3 - $t) * 6;
        }

        return $p;
    }

    protected $casts = [
    ];

    // --- RELACIONES ---

    public function capacitador()
    {
        return $this->belongsTo(User::class, 'capacitador_id');
    }

    public function cursoOriginal()
    {
        return $this->belongsTo(Curso::class, 'curso_original_id');
    }

    public function versionesDerivadas()
    {
        return $this->hasMany(Curso::class, 'curso_original_id');
    }

    public function estamentos()
    {
        return $this->belongsToMany(Estamento::class);
    }

    public function modulos()
    {
        return $this->hasMany(Modulo::class)->orderBy('orden');
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(SeccionCurso::class, 'curso_id')->orderBy('orden');
    }

    public function planificaciones(): HasMany
    {
        return $this->hasMany(PlanificacionCurso::class);
    }

    // --- LÓGICA DE NEGOCIO ---

    public function estaDisponible(): bool
    {
        $hoy = now()->startOfDay();

        return $this->planificaciones()
            ->where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy)
            ->exists();
    }

    public function estaDisponiblePara(User $user): bool
    {
        $hoy = now()->startOfDay();

        return $this->planificaciones()
            ->where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy)
            ->where(fn ($q) => $q->whereNull('sede_id')->orWhere('sede_id', $user->sede_id))
            ->exists();
    }

    public function progresoParaUsuario(User $user): int
    {
        $total = $this->modulos->count();

        if ($total === 0) {
            return 0;
        }

        $completados = $this->modulos
            ->filter(fn (Modulo $m) => $m->estaCompletadoPor($user))
            ->count();

        return (int) round(($completados / $total) * 100);
    }
}
