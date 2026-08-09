<?php

namespace Tests\Feature;

use App\Actions\Cursos\DuplicateCourseAction;
use App\Models\Curso;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Media\MediaAttachmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaAttachmentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_replacement_keeps_previous_asset_until_new_one_is_ready(): void
    {
        $owner = User::factory()->create();
        $course = Curso::factory()->for($owner, 'capacitador')->create();
        $old = $this->asset($owner, 'ready', 'old');
        $new = $this->asset($owner, 'processing', 'new');
        $service = app(MediaAttachmentService::class);

        $service->request($old, $course, 'cover', $owner);
        $pending = $service->request($new, $course, 'cover', $owner);

        $this->assertSame($old->id, $course->coverMedia()->id);
        $this->assertFalse($pending->active);

        $new->update(['status' => 'ready', 'ready_at' => now()]);
        $service->activatePending($new->refresh());

        $this->assertSame($new->id, $course->coverMedia()->id);
        $this->assertNotNull($old->refresh()->unreferenced_at);
    }

    public function test_duplicate_course_shares_the_same_asset(): void
    {
        $owner = User::factory()->create();
        $course = Curso::factory()->for($owner, 'capacitador')->create();
        $asset = $this->asset($owner, 'ready', 'shared');
        app(MediaAttachmentService::class)->request($asset, $course, 'cover', $owner);

        $copy = app(DuplicateCourseAction::class)->execute($course, 'Copia');

        $this->assertSame($asset->id, $copy->coverMedia()->id);
        $this->assertSame(2, $asset->attachments()->where('active', true)->count());
    }

    private function asset(User $owner, string $status, string $seed): MediaAsset
    {
        return MediaAsset::create([
            'owner_id' => $owner->id,
            'kind' => 'cover',
            'status' => $status,
            'source' => 'upload',
            'original_name' => $seed.'.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 10,
            'checksum' => hash('sha256', $seed),
            'transformation_profile' => 'cover-test',
            'ready_at' => $status === 'ready' ? now() : null,
            'unreferenced_at' => now(),
        ]);
    }
}
