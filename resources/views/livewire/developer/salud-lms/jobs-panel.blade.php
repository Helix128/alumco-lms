<section class="admin-surface p-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <h3 class="text-sm font-black uppercase tracking-[0.2em] text-Alumco-blue">Jobs fallidos</h3>
            <p class="mt-1 text-sm font-medium text-gray-500">Payload, excepción completa y acciones de retry/forget auditadas.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <input type="search" wire:model.live.debounce.400ms="search" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-bold outline-none focus:border-Alumco-blue" placeholder="Buscar job, cola o error">
            <select wire:model.live="hours" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-bold outline-none focus:border-Alumco-blue">
                <option value="1">Última hora</option>
                <option value="24">Últimas 24h</option>
                <option value="168">Últimos 7 días</option>
            </select>
        </div>
    </div>

    @if ($message)
        <div class="mt-4 rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-bold text-green-700">{{ $message }}</div>
    @endif

    <div class="mt-5 space-y-4">
        @forelse ($jobs as $job)
            <article wire:key="failed-job-{{ $job['uuid'] }}" class="rounded-2xl border border-red-100 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="font-display text-base font-black text-Alumco-gray">{{ $job['display_name'] }}</p>
                        <p class="mt-1 text-xs font-bold text-gray-500">{{ $job['connection'] }} / {{ $job['queue'] }} · {{ $job['failed_at'] }} · UUID {{ $job['uuid'] }}</p>
                        <p class="mt-3 rounded-xl bg-red-50 px-3 py-2 text-sm font-bold text-red-800 ring-1 ring-red-100">{{ $job['error'] }}</p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <button type="button" wire:click="requestRetry('{{ $job['uuid'] }}')" class="rounded-xl bg-Alumco-blue px-4 py-2 text-xs font-black text-white transition hover:bg-Alumco-blue/90">Retry</button>
                        <button type="button" wire:click="requestForget('{{ $job['uuid'] }}')" class="rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs font-black text-red-800 transition hover:bg-red-100">Forget</button>
                    </div>
                </div>

                <details class="mt-4">
                    <summary class="cursor-pointer text-xs font-black uppercase tracking-widest text-Alumco-blue">Ver payload y stack trace</summary>
                    <div class="mt-3 grid gap-3 xl:grid-cols-2">
                        <div>
                            <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-gray-400">Payload</p>
                            <pre class="max-h-96 overflow-auto rounded-xl border border-gray-800 bg-gray-950 p-4 font-mono text-xs leading-6 text-gray-50 shadow-inner">{{ $job['payload'] }}</pre>
                        </div>
                        <div>
                            <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-gray-400">Stack trace</p>
                            <pre class="max-h-96 overflow-auto rounded-xl border border-gray-800 bg-gray-950 p-4 font-mono text-xs leading-6 text-gray-50 shadow-inner">{{ $job['exception'] }}</pre>
                        </div>
                    </div>
                </details>
            </article>
        @empty
            <p class="rounded-2xl border border-dashed border-gray-200 p-8 text-center text-sm font-bold text-gray-400">No hay jobs fallidos para el filtro actual.</p>
        @endforelse
    </div>

    <x-admin-confirmation-modal
        :show="$pendingConfirmation['show']"
        :title="$pendingConfirmation['title']"
        :description="$pendingConfirmation['description']"
        :confirm-label="$pendingConfirmation['confirm_label']"
        :tone="$pendingConfirmation['tone']"
    />
</section>
