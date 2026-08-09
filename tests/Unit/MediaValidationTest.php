<?php

namespace Tests\Unit;

use App\Services\Media\ExternalVideoService;
use App\Services\Media\MediaValidationService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MediaValidationTest extends TestCase
{
    public function test_declared_limits_are_coherent(): void
    {
        $validator = app(MediaValidationService::class);
        $validator->validateDeclaration('cover', 'cover.webp', 10 * 1024 * 1024);
        $validator->validateDeclaration('image', 'chart.gif', 20 * 1024 * 1024);
        $validator->validateDeclaration('pdf', 'guide.pdf', 50 * 1024 * 1024);
        $validator->validateDeclaration('document', 'slides.pptx', 100 * 1024 * 1024);
        $validator->validateDeclaration('video', 'lesson.mp4', 250 * 1024 * 1024);
        $this->addToAssertionCount(5);
    }

    public function test_file_above_its_limit_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(MediaValidationService::class)->validateDeclaration('cover', 'cover.jpg', (10 * 1024 * 1024) + 1);
    }

    public function test_only_normalized_youtube_and_vimeo_https_links_are_accepted(): void
    {
        $videos = app(ExternalVideoService::class);
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $videos->normalize('https://youtu.be/dQw4w9WgXcQ?t=1'));
        $this->assertSame('https://player.vimeo.com/video/123456789', $videos->normalize('https://vimeo.com/123456789'));

        $this->expectException(ValidationException::class);
        $videos->normalize('https://example.com/embed/123');
    }
}
