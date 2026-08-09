<?php

namespace Tests\Feature;

use App\Jobs\ProcessMediaAsset;
use App\Models\MediaUpload;
use App\Models\User;
use App\Services\Media\MediaUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MediaUploadServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local_media');
        Storage::fake('media_temp');
        config()->set('media.disk', 'local_media');
        config()->set('media.temp_disk', 'media_temp');
        config()->set('media.chunk_size', 4);
        config()->set('media.capacity.minimum_free_bytes', 0);
        config()->set('media.capacity.block_percent', 101);
    }

    public function test_uploads_can_resume_repeat_a_part_and_complete_atomically(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $content = '%PDF-test';
        $service = app(MediaUploadService::class);
        $upload = $service->create($user, 'pdf', 'manual.pdf', 'application/pdf', strlen($content));

        $this->putPart($service, $upload, 1, substr($content, 0, 4));
        $this->putPart($service, $upload->refresh(), 1, substr($content, 0, 4));
        $this->putPart($service, $upload->refresh(), 2, substr($content, 4, 4));
        $this->putPart($service, $upload->refresh(), 3, substr($content, 8));

        $completed = $service->complete($upload->refresh());

        $this->assertSame('completed', $completed->status);
        $this->assertNotNull($completed->media_asset_id);
        $this->assertSame(3, $completed->total_parts);
        $this->assertDatabaseHas('media_variants', ['media_asset_id' => $completed->media_asset_id, 'type' => 'original']);
        Queue::assertPushed(ProcessMediaAsset::class, fn ($job) => $job->assetId === $completed->media_asset_id);
        Storage::disk('media_temp')->assertMissing($upload->temp_path);
    }

    public function test_expired_session_and_spoofed_pdf_are_rejected(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $service = app(MediaUploadService::class);
        $upload = $service->create($user, 'pdf', 'manual.pdf', 'application/pdf', 8);
        $upload->update(['expires_at' => now()->subMinute()]);

        $this->expectException(ValidationException::class);
        $this->putPart($service, $upload->refresh(), 1, 'not-');
    }

    public function test_spoofed_pdf_fails_after_all_parts_are_assembled(): void
    {
        Queue::fake();
        $service = app(MediaUploadService::class);
        $upload = $service->create(User::factory()->create(), 'pdf', 'manual.pdf', 'application/pdf', 8);
        $this->putPart($service, $upload, 1, 'not-');
        $this->putPart($service, $upload->refresh(), 2, 'pdf!');

        $this->expectException(ValidationException::class);
        $service->complete($upload->refresh());
    }

    private function putPart(MediaUploadService $service, MediaUpload $upload, int $number, string $bytes): void
    {
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, $bytes);
        rewind($stream);
        try {
            $service->acceptPart($upload, $number, $stream, strlen($bytes));
        } finally {
            fclose($stream);
        }
    }
}
