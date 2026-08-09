<?php

namespace App\Services\Media;

use Illuminate\Validation\ValidationException;
use ZipArchive;

class MediaValidationService
{
    public function validateDeclaration(string $purpose, string $name, int $size): void
    {
        $purpose = $this->normalizePurpose($purpose, $name);
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $limit = (int) config("media.limits.{$purpose}", 0);
        $allowed = config("media.allowed.{$purpose}", []);

        if ($size < 1 || $limit === 0 || $size > $limit) {
            throw ValidationException::withMessages([
                'file' => 'El archivo supera el límite permitido de '.(int) ceil($limit / 1048576).' MB.',
            ]);
        }

        if (! in_array($extension, $allowed, true)) {
            throw ValidationException::withMessages(['file' => 'La extensión del archivo no está permitida.']);
        }
    }

    /** @return array{mime:string,extension:string,width?:int,height?:int} */
    public function inspect(string $purpose, string $name, string $path): array
    {
        $this->validateDeclaration($purpose, $name, filesize($path) ?: 0);
        $purpose = $this->normalizePurpose($purpose, $name);
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path) ?: 'application/octet-stream';

        if (in_array($purpose, ['cover', 'image'], true)) {
            $info = @getimagesize($path);
            if ($info === false || ! in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
                $this->invalidMime();
            }

            return ['mime' => $info['mime'], 'extension' => $extension, 'width' => $info[0], 'height' => $info[1]];
        }

        if ($purpose === 'pdf') {
            $header = file_get_contents($path, false, null, 0, 5);
            if ($header !== '%PDF-') {
                $this->invalidMime();
            }

            return ['mime' => 'application/pdf', 'extension' => 'pdf'];
        }

        if ($purpose === 'document') {
            if ($extension === 'pdf') {
                return $this->inspect('pdf', $name, $path);
            }

            if (in_array($extension, ['docx', 'pptx'], true)) {
                $zip = new ZipArchive;
                if ($zip->open($path) !== true) {
                    $this->invalidMime();
                }
                $required = $extension === 'docx' ? 'word/document.xml' : 'ppt/presentation.xml';
                $valid = $zip->locateName($required) !== false;
                $zip->close();
                if (! $valid) {
                    $this->invalidMime();
                }
            } elseif (! in_array(bin2hex((string) file_get_contents($path, false, null, 0, 8)), ['d0cf11e0a1b11ae1'], true)) {
                $this->invalidMime();
            }

            return ['mime' => $mime, 'extension' => $extension];
        }

        if ($purpose === 'video') {
            if (! in_array($mime, ['video/mp4', 'application/mp4', 'application/octet-stream'], true)) {
                $this->invalidMime();
            }

            return ['mime' => 'video/mp4', 'extension' => 'mp4'];
        }

        throw ValidationException::withMessages(['purpose' => 'Tipo de medio no permitido.']);
    }

    public function normalizePurpose(string $purpose, string $name = ''): string
    {
        if ($purpose === 'document' && strtolower(pathinfo($name, PATHINFO_EXTENSION)) === 'pdf') {
            return 'pdf';
        }

        if (in_array($purpose, ['cover', 'image', 'pdf', 'document', 'video'], true)) {
            return $purpose;
        }

        throw ValidationException::withMessages(['purpose' => 'Tipo de medio no permitido.']);
    }

    private function invalidMime(): never
    {
        throw ValidationException::withMessages(['file' => 'El contenido real del archivo no coincide con su extensión.']);
    }
}
