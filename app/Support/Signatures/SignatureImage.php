<?php

namespace App\Support\Signatures;

use Illuminate\Support\Facades\Storage;

class SignatureImage
{
    public const Directory = 'firmas';

    /**
     * @return list<string>
     */
    public static function rules(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'image',
            'mimes:png,jpg,jpeg,webp',
            'max:1024',
        ];
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function dataUri(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $mimeType = Storage::disk('public')->mimeType($path) ?: 'image/png';

        if (! str_starts_with($mimeType, 'image/')) {
            return null;
        }

        return 'data:'.$mimeType.';base64,'.base64_encode(Storage::disk('public')->get($path));
    }
}
