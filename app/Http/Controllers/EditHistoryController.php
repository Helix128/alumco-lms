<?php

namespace App\Http\Controllers;

use App\Exceptions\EditHistoryConflict;
use App\Services\History\EditHistoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EditHistoryController extends Controller
{
    public function undo(Request $request, string $context, EditHistoryService $history): RedirectResponse
    {
        return $this->travel($request, $context, $history, false);
    }

    public function redo(Request $request, string $context, EditHistoryService $history): RedirectResponse
    {
        return $this->travel($request, $context, $history, true);
    }

    private function travel(Request $request, string $context, EditHistoryService $history, bool $redo): RedirectResponse
    {
        $validated = $request->validate(['scope_id' => ['required', 'string', 'max:80']]);

        try {
            $step = $redo
                ? $history->redo($request->user(), $context, $validated['scope_id'])
                : $history->undo($request->user(), $context, $validated['scope_id']);
        } catch (EditHistoryConflict $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with(
            'success',
            ($redo ? 'Cambio rehecho: ' : 'Cambio deshecho: ').$step->label.'.'
        );
    }
}
