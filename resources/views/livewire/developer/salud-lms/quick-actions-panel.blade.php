<section class="admin-surface p-6">
    <h3 class="text-sm font-black uppercase tracking-[0.2em] text-Alumco-blue">Acciones rápidas</h3>
    <p class="mt-1 text-sm font-medium text-gray-500">Acciones con auditoría en `admin_actions`.</p>

    @if ($message)
        <div class="mt-4 rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-bold text-green-700">{{ $message }}</div>
    @endif

    <div class="mt-5 grid gap-3">
        <button type="button" wire:click="requestAction('clear_cache')" class="rounded-2xl bg-Alumco-blue px-4 py-3 text-left text-sm font-black text-white shadow-lg shadow-Alumco-blue/15 transition hover:bg-Alumco-blue/90">
            Limpiar caché optimizada
        </button>
        <button type="button" wire:click="requestAction('flush_failed_jobs')" class="rounded-2xl border border-Alumco-coral/20 bg-Alumco-coral/10 px-4 py-3 text-left text-sm font-black text-Alumco-coral-accessible transition hover:bg-Alumco-coral/15">
            Vaciar jobs fallidos
        </button>
    </div>

    <div class="mt-6">
        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Historial reciente</p>
        <div class="mt-3 space-y-3">
            @forelse ($actions as $action)
                <div wire:key="admin-action-{{ $action->id }}" class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2">
                    <p class="text-xs font-black text-Alumco-gray">{{ $action->action }} · {{ $action->status }}</p>
                    <p class="mt-1 text-[11px] font-medium text-gray-500">{{ $action->user?->name }} · {{ $action->executed_at->diffForHumans() }}</p>
                </div>
            @empty
                <p class="rounded-xl border border-dashed border-gray-200 p-4 text-sm font-bold text-gray-400">Sin acciones auditadas.</p>
            @endforelse
        </div>
    </div>

    <x-admin-confirmation-modal
        :show="$pendingConfirmation['show']"
        :title="$pendingConfirmation['title']"
        :description="$pendingConfirmation['description']"
        :confirm-label="$pendingConfirmation['confirm_label']"
        :tone="$pendingConfirmation['tone']"
    />
</section>
