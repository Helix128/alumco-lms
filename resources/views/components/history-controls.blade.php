@props([
    'context',
    'scopeId',
    'state' => null,
    'livewire' => false,
])

@php
    $historyState = $state ?? app(\App\Services\History\EditHistoryService::class)
        ->availability(auth()->user(), $context, (string) $scopeId);
@endphp

<div {{ $attributes->merge(['class' => 'history-controls']) }} role="group" aria-label="Historial de cambios">
    @if($livewire)
        <button type="button"
                wire:click="deshacer"
                wire:loading.attr="disabled"
                data-history-undo
                @disabled(!$historyState['can_undo'])
                class="history-control-button"
                title="{{ $historyState['can_undo'] ? 'Deshacer: '.$historyState['undo_label'].' (Ctrl/Cmd+Z)' : 'No hay cambios para deshacer' }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 7 4 12l5 5M5 12h8a6 6 0 0 1 6 6" /></svg>
            <span>Deshacer</span>
        </button>
        <button type="button"
                wire:click="rehacer"
                wire:loading.attr="disabled"
                data-history-redo
                @disabled(!$historyState['can_redo'])
                class="history-control-button"
                title="{{ $historyState['can_redo'] ? 'Rehacer: '.$historyState['redo_label'].' (Ctrl/Cmd+Shift+Z)' : 'No hay cambios para rehacer' }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 7 5 5-5 5m4-5h-8a6 6 0 0 0-6 6" /></svg>
            <span>Rehacer</span>
        </button>
    @else
        <form method="POST" action="{{ route('history.undo', $context) }}">
            @csrf
            <input type="hidden" name="scope_id" value="{{ $scopeId }}">
            <button type="submit"
                    data-history-undo
                    @disabled(!$historyState['can_undo'])
                    class="history-control-button"
                    title="{{ $historyState['can_undo'] ? 'Deshacer: '.$historyState['undo_label'].' (Ctrl/Cmd+Z)' : 'No hay cambios para deshacer' }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 7 4 12l5 5M5 12h8a6 6 0 0 1 6 6" /></svg>
                <span>Deshacer</span>
            </button>
        </form>
        <form method="POST" action="{{ route('history.redo', $context) }}">
            @csrf
            <input type="hidden" name="scope_id" value="{{ $scopeId }}">
            <button type="submit"
                    data-history-redo
                    @disabled(!$historyState['can_redo'])
                    class="history-control-button"
                    title="{{ $historyState['can_redo'] ? 'Rehacer: '.$historyState['redo_label'].' (Ctrl/Cmd+Shift+Z)' : 'No hay cambios para rehacer' }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 7 5 5-5 5m4-5h-8a6 6 0 0 0-6 6" /></svg>
                <span>Rehacer</span>
            </button>
        </form>
    @endif
    <span class="sr-only" aria-live="polite">El historial conserva hasta 20 cambios durante 30 minutos.</span>
</div>
