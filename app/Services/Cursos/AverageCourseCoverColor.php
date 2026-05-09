<?php

namespace App\Services\Cursos;

class AverageCourseCoverColor
{
    private const SAMPLE_SIZE = 20;

    private const HUE_BUCKETS = 36;

    private const LIGHTNESS_MIN_THRESHOLD = 0.15;

    private const LIGHTNESS_MAX_THRESHOLD = 0.85;

    private const SATURATION_MIN_THRESHOLD = 0.15;

    private const LIGHTNESS_MIN_TARGET = 0.20;

    private const LIGHTNESS_MAX_TARGET = 0.40;

    private const SATURATION_MIN_TARGET = 0.45;

    private const FALLBACK_COLOR = '#1a3a5a';

    public function fromPublicPath(?string $relativePath): ?string
    {
        if (! $relativePath) {
            return null;
        }

        $path = storage_path('app/public/'.$relativePath);

        if (! file_exists($path)) {
            return null;
        }

        try {
            $imageMetadata = getimagesize($path);
            if (! $imageMetadata) {
                return null;
            }

            $imageType = $imageMetadata[2];
            $sourceImage = match ($imageType) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($path),
                IMAGETYPE_PNG => imagecreatefrompng($path),
                IMAGETYPE_WEBP => imagecreatefromwebp($path),
                default => null
            };

            if (! $sourceImage) {
                return null;
            }

            $sampleWidth = self::SAMPLE_SIZE;
            $sampleHeight = self::SAMPLE_SIZE;
            $sampleImage = imagecreatetruecolor($sampleWidth, $sampleHeight);
            imagecopyresampled($sampleImage, $sourceImage, 0, 0, 0, 0, $sampleWidth, $sampleHeight, imagesx($sourceImage), imagesy($sourceImage));

            $buckets = [];
            for ($x = 0; $x < $sampleWidth; $x++) {
                for ($y = 0; $y < $sampleHeight; $y++) {
                    $rgb = imagecolorat($sampleImage, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    [$h, $s, $l] = $this->rgbToHsl($r, $g, $b);

                    if ($l > self::LIGHTNESS_MIN_THRESHOLD && $l < self::LIGHTNESS_MAX_THRESHOLD && $s > self::SATURATION_MIN_THRESHOLD) {
                        $bucketIdx = (int) round($h * self::HUE_BUCKETS);
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
                return self::FALLBACK_COLOR;
            }

            $dominant = array_reduce($buckets, function ($carry, $item) {
                return ($item['count'] > ($carry['count'] ?? 0)) ? $item : $carry;
            }, ['count' => 0]);

            $r = $dominant['r'] / $dominant['count'];
            $g = $dominant['g'] / $dominant['count'];
            $b = $dominant['b'] / $dominant['count'];

            [$h, $s, $l] = $this->rgbToHsl($r, $g, $b);

            $l = max(self::LIGHTNESS_MIN_TARGET, min($l, self::LIGHTNESS_MAX_TARGET));
            $s = max($s, self::SATURATION_MIN_TARGET);

            [$r, $g, $b] = $this->hslToRgb($h, $s, $l);

            return sprintf('#%02x%02x%02x', $r, $g, $b);
        } catch (\Throwable $exception) {
            report($exception);

            return self::FALLBACK_COLOR;
        }
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    private function rgbToHsl(float $r, float $g, float $b): array
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
                default: $h = 0;
                    break;
            }
            $h /= 6;
        }

        return [$h, $s, $l];
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function hslToRgb(float $h, float $s, float $l): array
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

        return [(int) round($r * 255), (int) round($g * 255), (int) round($b * 255)];
    }

    private function hue2rgb(float $p, float $q, float $t): float
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
}
