<?php

namespace App\Services\Media;

use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ExternalVideoService
{
    public function create(string $url, User $owner): MediaAsset
    {
        $normalized = $this->normalize($url);
        $checksum = hash('sha256', $normalized);

        return MediaAsset::firstOrCreate(
            ['owner_id' => $owner->id, 'checksum' => $checksum, 'transformation_profile' => 'external-video-v1', 'status' => MediaAsset::STATUS_READY],
            [
                'kind' => 'video',
                'source' => 'external',
                'original_name' => 'video-enlace.url',
                'mime_type' => 'text/uri-list',
                'size_bytes' => strlen($normalized),
                'external_url' => $normalized,
                'ready_at' => now(),
                'metadata' => ['provider' => str_contains($normalized, 'youtube') ? 'youtube' : 'vimeo'],
                'unreferenced_at' => now(),
            ],
        );
    }

    public function normalize(string $url): string
    {
        $parts = parse_url(trim($url));
        if (($parts['scheme'] ?? null) !== 'https' || empty($parts['host'])) {
            $this->invalid();
        }
        $host = strtolower($parts['host']);
        $path = trim($parts['path'] ?? '', '/');

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $id = explode('/', $path)[0] ?? '';

            return $this->youtube($id);
        }
        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            parse_str($parts['query'] ?? '', $query);
            $segments = explode('/', $path);
            $id = $query['v'] ?? (in_array($segments[0] ?? '', ['embed', 'shorts'], true) ? ($segments[1] ?? '') : '');

            return $this->youtube((string) $id);
        }
        if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)) {
            preg_match('/(?:video\/)?(\d{6,12})/', $path, $matches);
            if (! isset($matches[1])) {
                $this->invalid();
            }

            return 'https://player.vimeo.com/video/'.$matches[1];
        }

        $this->invalid();
    }

    private function youtube(string $id): string
    {
        if (! preg_match('/^[A-Za-z0-9_-]{11}$/', $id)) {
            $this->invalid();
        }

        return 'https://www.youtube-nocookie.com/embed/'.$id;
    }

    private function invalid(): never
    {
        throw ValidationException::withMessages(['video_url' => 'Ingresa un enlace HTTPS válido de YouTube o Vimeo.']);
    }
}
