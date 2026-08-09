<?php

namespace App\Http\Controllers;

use App\Models\MediaUpload;
use App\Models\User;
use App\Services\Media\MediaUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaUploadController extends Controller
{
    public function __construct(private readonly MediaUploadService $uploads) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'purpose' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'mime_type' => ['nullable', 'string', 'max:160'],
            'size' => ['required', 'integer', 'min:1'],
        ]);
        $upload = $this->uploads->create($this->user(), $data['purpose'], $data['name'], $data['mime_type'] ?? '', $data['size']);

        $client = $this->uploads->clientState($upload);

        return response()->json([
            'id' => $upload->id,
            'chunk_size' => $upload->chunk_size,
            'total_parts' => $upload->total_parts,
            'received_parts' => array_map('intval', array_keys($upload->received_parts ?? [])),
            'direct' => $client['direct'],
            'part_urls' => $client['part_urls'],
            'part_etags' => $client['part_etags'],
        ], 201);
    }

    public function show(MediaUpload $upload): JsonResponse
    {
        $this->owns($upload);
        $client = $upload->status === 'uploading' ? $this->uploads->clientState($upload) : ['direct' => $upload->temp_disk === 's3', 'received_parts' => [], 'part_urls' => [], 'part_etags' => []];

        return response()->json([
            'id' => $upload->id,
            'status' => $upload->status,
            'chunk_size' => $upload->chunk_size,
            'total_parts' => $upload->total_parts,
            'received_parts' => $client['received_parts'],
            'direct' => $client['direct'],
            'part_urls' => $client['part_urls'],
            'part_etags' => $client['part_etags'],
            'asset' => $upload->asset ? ['id' => $upload->asset->id, 'status' => $upload->asset->status] : null,
        ]);
    }

    public function part(Request $request, MediaUpload $upload, int $part): JsonResponse
    {
        $this->owns($upload);
        $length = (int) $request->header('Content-Length', 0);
        $stream = fopen('php://input', 'rb');
        try {
            $upload = $this->uploads->acceptPart($upload, $part, $stream, $length);
        } finally {
            fclose($stream);
        }

        return response()->json(['received_parts' => array_map('intval', array_keys($upload->received_parts ?? []))]);
    }

    public function complete(Request $request, MediaUpload $upload): JsonResponse
    {
        $this->owns($upload);
        if ($upload->temp_disk === 's3') {
            $data = $request->validate([
                'parts' => ['required', 'array', 'size:'.$upload->total_parts],
                'parts.*.PartNumber' => ['required', 'integer', 'min:1', 'max:'.$upload->total_parts],
                'parts.*.ETag' => ['required', 'string', 'max:200'],
            ]);
            $upload = $this->uploads->completeS3($upload, $data['parts']);
        } else {
            $upload = $this->uploads->complete($upload);
        }

        return response()->json(['asset_id' => $upload->media_asset_id, 'status' => $upload->asset->status]);
    }

    public function destroy(MediaUpload $upload): JsonResponse
    {
        $this->owns($upload);
        $this->uploads->cancel($upload);

        return response()->json(null, 204);
    }

    private function owns(MediaUpload $upload): void
    {
        abort_unless($upload->user_id === $this->user()->id, 404);
    }

    private function user(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
