<section class="admin-surface p-6">
    <h3 class="text-sm font-black uppercase tracking-[0.2em] text-Alumco-blue">Performance y caché</h3>
    <p class="mt-1 text-sm font-medium text-gray-500">Store, actividad y tendencia operacional de los últimos snapshots.</p>

    <div class="mt-4 grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-2xl bg-gray-50 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Store</p>
            <p class="mt-1 text-sm font-black text-Alumco-gray">{{ $stats['store'] }}</p>
            <p class="mt-1 truncate text-xs font-semibold text-gray-500">{{ $stats['driver'] }} · {{ $stats['prefix'] }}</p>
        </div>
        <div class="rounded-2xl bg-gray-50 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Keys DB</p>
            <p class="mt-1 text-xl font-black text-Alumco-blue">{{ $stats['database_keys'] ?? 'N/D' }}</p>
        </div>
        <div class="rounded-2xl bg-gray-50 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Activos</p>
            <p class="mt-1 text-xl font-black text-Alumco-green-accessible">{{ $stats['active_users'] }}</p>
        </div>
        <div class="rounded-2xl bg-gray-50 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Último snapshot</p>
            <p class="mt-1 text-sm font-black text-Alumco-gray">{{ $stats['latest_snapshot_at'] }}</p>
            <p class="mt-1 text-xs font-semibold text-gray-500">{{ $stats['snapshot_count'] }} registros</p>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-3 gap-3">
        <div class="rounded-2xl bg-gray-50 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Error promedio</p>
            <p class="mt-1 text-xl font-black text-Alumco-coral-accessible">{{ $stats['avg_error_rate'] }}</p>
        </div>
        <div class="rounded-2xl bg-gray-50 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Peak pendientes</p>
            <p class="mt-1 text-xl font-black text-Alumco-blue">{{ $stats['max_pending_jobs'] }}</p>
        </div>
        <div class="rounded-2xl bg-gray-50 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Activos promedio</p>
            <p class="mt-1 text-xl font-black text-Alumco-green-accessible">{{ $stats['avg_active_users'] }}</p>
        </div>
    </div>

    <div class="mt-5 space-y-2">
        @forelse ($stats['snapshots'] as $snapshot)
            <div wire:key="snapshot-{{ $snapshot['id'] }}-{{ $snapshot['captured_at_key'] }}" class="grid grid-cols-5 gap-2 rounded-xl bg-gray-50 px-3 py-2 text-xs font-bold text-gray-500">
                <span>{{ $snapshot['captured_at_display'] }}</span>
                <span>Fallidos: {{ $snapshot['failed_jobs_count'] }}</span>
                <span>Pendientes: {{ $snapshot['pending_jobs_count'] }}</span>
                <span>Errores: {{ $snapshot['error_rate'] }}</span>
                <span>Activos: {{ $snapshot['active_users'] }}</span>
            </div>
        @empty
            <p class="rounded-2xl border border-dashed border-gray-200 p-6 text-sm font-bold text-gray-400">Sin snapshots aún. El scheduler guardará uno cada 5 minutos.</p>
        @endforelse
    </div>
</section>
