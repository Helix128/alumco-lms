<section class="worker-card p-5 lg:p-6">
    <div class="mb-5 flex items-center justify-between gap-4">
        <div>
            <h2 class="font-display text-xl font-black text-Alumco-gray">Mis tickets</h2>
            <p class="text-sm font-semibold text-Alumco-gray/55">Historial de solicitudes enviadas desde tu cuenta.</p>
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($tickets as $ticket)
            <a wire:key="my-support-ticket-{{ $ticket->id }}" href="{{ route('support.show', $ticket) }}" wire:navigate.hover
               class="worker-focus block rounded-2xl border border-gray-100 bg-white px-4 py-3 transition hover:border-Alumco-blue/30 hover:shadow-md">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-Alumco-gray">#{{ $ticket->id }} {{ $ticket->subject }}</p>
                        <p class="mt-1 text-xs font-semibold text-Alumco-gray/50">{{ \App\Models\SupportTicket::categoryLabel($ticket->category) }} · {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="rounded-full bg-Alumco-blue/10 px-3 py-1 text-xs font-black text-Alumco-blue">
                        {{ \App\Models\SupportTicket::statusLabel($ticket->status) }}
                    </span>
                </div>
            </a>
        @empty
            <p class="rounded-2xl border border-dashed border-gray-200 px-4 py-8 text-center text-sm font-bold text-Alumco-gray/45">Aún no tienes tickets registrados.</p>
        @endforelse
    </div>
</section>
