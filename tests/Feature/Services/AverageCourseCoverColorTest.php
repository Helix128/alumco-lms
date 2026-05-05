<?php

namespace Tests\Feature\Services;

use App\Services\Cursos\AverageCourseCoverColor;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AverageCourseCoverColorTest extends TestCase
{
    public function test_it_returns_null_when_path_is_null(): void
    {
        $service = new AverageCourseCoverColor;

        $this->assertNull($service->fromPublicPath(null));
    }

    public function test_it_extracts_a_hex_color_from_valid_image(): void
    {
        $service = new AverageCourseCoverColor;

        $relativePath = 'portadas/test-color.png';
        $absolutePath = storage_path('app/public/'.$relativePath);
        File::ensureDirectoryExists(dirname($absolutePath));

        $image = imagecreatetruecolor(10, 10);
        $color = imagecolorallocate($image, 30, 90, 170);
        imagefill($image, 0, 0, $color);
        imagepng($image, $absolutePath);
        imagedestroy($image);

        $color = $service->fromPublicPath($relativePath);

        $this->assertNotNull($color);
        $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', (string) $color);
    }
}
