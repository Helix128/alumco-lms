<?php

namespace App\Support;

final class AccessibilityPreferences
{
    /**
     * @return array{fontLevel: int, highContrast: bool, reducedMotion: bool}
     */
    public static function defaults(): array
    {
        return [
            'fontLevel' => 0,
            'highContrast' => false,
            'reducedMotion' => false,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $preferences
     * @return array{fontLevel: int, highContrast: bool, reducedMotion: bool}
     */
    public static function normalize(?array $preferences): array
    {
        $preferences = array_merge(self::defaults(), $preferences ?? []);
        $fontLevel = (int) $preferences['fontLevel'];

        return [
            'fontLevel' => max(0, min(2, $fontLevel)),
            'highContrast' => (bool) $preferences['highContrast'],
            'reducedMotion' => (bool) $preferences['reducedMotion'],
        ];
    }

    public static function fontSizeFor(int $fontLevel): int
    {
        return [18, 20, 22][max(0, min(2, $fontLevel))];
    }
}
