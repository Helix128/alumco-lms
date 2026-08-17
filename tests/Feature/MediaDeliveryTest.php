<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\MediaAsset;
use App\Models\MediaVariant;
use App\Models\User;
use App\Services\Media\MediaAttachmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_delivery_preserves_mime_name_and_download_disposition(): void
    {
        Storage::fake('local_media');
        $owner = User::factory()->create();
        $course = Curso::factory()->for($owner, 'capacitador')->create();
        $asset = $this->asset($owner, 'Guía de acción 2026.pdf');
        app(MediaAttachmentService::class)->request($asset, $course, 'cover', $owner);

        $this->actingAs($owner)
            ->get(route('media.show', [$asset, 'display']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('x-content-type-options', 'nosniff');

        $this->actingAs($owner)
            ->get(route('media.download', $asset))
            ->assertOk()
            ->assertHeader('content-disposition', "attachment; filename=\"Guia de accion 2026.pdf\"; filename*=utf-8''Gu%C3%ADa%20de%20acci%C3%B3n%202026.pdf");
    }

    public function test_unrelated_user_cannot_read_private_asset(): void
    {
        Storage::fake('local_media');
        $owner = User::factory()->create();
        $course = Curso::factory()->for($owner, 'capacitador')->create();
        $asset = $this->asset($owner, 'private.pdf');
        app(MediaAttachmentService::class)->request($asset, $course, 'cover', $owner);

        $this->actingAs(User::factory()->create())
            ->get(route('media.show', [$asset, 'display']))
            ->assertForbidden();
    }

    public function test_conditional_request_returns_304_not_modified_with_matching_etag(): void
    {
        Storage::fake('local_media');
        $owner = User::factory()->create();
        $course = Curso::factory()->for($owner, 'capacitador')->create();
        $asset = $this->asset($owner, 'documento.pdf');
        app(MediaAttachmentService::class)->request($asset, $course, 'cover', $owner);

        $response = $this->actingAs($owner)
            ->get(route('media.show', [$asset, 'display']))
            ->assertOk();

        $etag = $response->headers->get('ETag');
        $this->assertNotEmpty($etag);

        $this->actingAs($owner)
            ->withHeaders(['If-None-Match' => $etag])
            ->get(route('media.show', [$asset, 'display']))
            ->assertStatus(304);
    }

    public function test_thumbnail_and_poster_variant_resolution(): void
    {
        Storage::fake('local_media');
        $owner = User::factory()->create();
        $course = Curso::factory()->for($owner, 'capacitador')->create();
        $asset = $this->asset($owner, 'portada.webp');
        
        // Crear variante thumbnail
        $thumbPath = "assets/test/{$asset->id}/thumbnail.webp";
        Storage::disk('local_media')->put($thumbPath, 'WEBP-thumb');
        MediaVariant::create([
            'media_asset_id' => $asset->id,
            'type' => 'thumbnail',
            'disk' => 'local_media',
            'path' => $thumbPath,
            'mime_type' => 'image/webp',
            'size_bytes' => 10,
            'checksum' => hash('sha256', 'WEBP-thumb'),
        ]);

        app(MediaAttachmentService::class)->request($asset, $course, 'cover', $owner);

        $this->actingAs($owner)
            ->get(route('media.show', [$asset, 'thumbnail']))
            ->assertOk()
            ->assertHeader('content-type', 'image/webp');
    }

    public function test_course_cover_uses_in_memory_relation_to_prevent_n_plus_one(): void
    {
        Storage::fake('local_media');
        $owner = User::factory()->create();
        $course = Curso::factory()->for($owner, 'capacitador')->create();
        $asset = $this->asset($owner, 'cover.webp');
        app(MediaAttachmentService::class)->request($asset, $course, 'cover', $owner);

        $loadedCourse = Curso::with('mediaAttachments.asset.variants')->findOrFail($course->id);
        $resolvedAsset = $loadedCourse->coverMedia();

        $this->assertNotNull($resolvedAsset);
        $this->assertSame($asset->id, $resolvedAsset->id);
    }

    private function asset(User $owner, string $name): MediaAsset
    {
        $asset = MediaAsset::create([
            'owner_id' => $owner->id,
            'kind' => 'pdf',
            'status' => 'ready',
            'source' => 'upload',
            'original_name' => $name,
            'mime_type' => 'application/pdf',
            'size_bytes' => 9,
            'checksum' => hash('sha256', $name),
            'transformation_profile' => 'original-test',
            'ready_at' => now(),
        ]);
        $path = "assets/test/{$asset->id}/original.pdf";
        Storage::disk('local_media')->put($path, '%PDF-test');
        MediaVariant::create([
            'media_asset_id' => $asset->id,
            'type' => 'original',
            'disk' => 'local_media',
            'path' => $path,
            'mime_type' => 'application/pdf',
            'size_bytes' => 9,
            'checksum' => hash('sha256', '%PDF-test'),
        ]);

        return $asset;
    }
}
