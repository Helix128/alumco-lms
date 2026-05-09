<?php

namespace App\Http\Controllers;

use App\Models\SupportTicketAttachment;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportTicketAttachmentController extends Controller
{
    public function __invoke(SupportTicketAttachment $attachment): StreamedResponse|Response
    {
        $attachment->loadMissing(['ticket', 'message']);
        $this->authorize('view', $attachment->ticket);

        abort_if($attachment->message?->is_internal && ! auth()->user()?->isDesarrollador(), 403);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }
}
