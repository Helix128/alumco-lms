<section class="admin-surface p-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h3 class="text-sm font-black uppercase tracking-[0.2em] text-Alumco-blue">Estado de servicios</h3>
            <p class="mt-1 text-sm font-medium text-gray-500">Health checks reales con caché corto para no castigar el panel.</p>
        </div>
        <span class="rounded-full bg-Alumco-blue/10 px-3 py-1 text-xs font-black text-Alumco-blue">TTL 1 min</span>
    </div>

    <div class="mt-5 grid gap-4 md:grid-cols-2">
        @foreach ($services as $service)
            <article wire:key="service-{{ $service['name'] }}" class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-display text-base font-black text-Alumco-gray">{{ $service['name'] }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-500">{{ $service['detail'] }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $service['status'] === 'ok' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $service['status'] === 'ok' ? 'OK' : 'Falla' }}
                    </span>
                </div>
                <p class="mt-4 text-[11px] font-bold uppercase tracking-widest text-gray-400">Revisado {{ $service['checked_at'] }}</p>
            </article>
        @endforeach
    </div>
</section>
