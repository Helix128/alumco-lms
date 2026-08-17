<?php

return [
    'disk' => env('MEDIA_DISK', 'local_media'),
    'temp_disk' => env('MEDIA_TEMP_DISK', 'media_temp'),
    'chunk_size' => 8 * 1024 * 1024,
    'upload_ttl_hours' => 24,
    'unreferenced_grace_days' => 7,
    'local_internal_prefix' => env('MEDIA_INTERNAL_PREFIX', '/protected-media'),
    'temporary_url_minutes' => (int) env('MEDIA_TEMPORARY_URL_MINUTES', 180),
    'variants' => [
        'cover_hero_max_side' => 1440,
        'cover_hero_quality' => 82,
        'cover_thumb_max_side' => 480,
        'cover_thumb_quality' => 80,
    ],
    'capacity' => [
        'warn_percent' => 80,
        'block_percent' => 90,
        'minimum_free_bytes' => 5 * 1024 * 1024 * 1024,
    ],
    'limits' => [
        'cover' => 10 * 1024 * 1024,
        'image' => 20 * 1024 * 1024,
        'pdf' => 50 * 1024 * 1024,
        'document' => 100 * 1024 * 1024,
        'video' => 250 * 1024 * 1024,
    ],
    'allowed' => [
        'cover' => ['jpg', 'jpeg', 'png', 'webp'],
        'image' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'pdf' => ['pdf'],
        'document' => ['pdf', 'doc', 'docx', 'ppt', 'pptx'],
        'video' => ['mp4'],
    ],
];
