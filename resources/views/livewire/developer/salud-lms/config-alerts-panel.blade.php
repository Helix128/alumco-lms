<section class="admin-surface p-6">
    <h3 class="text-sm font-black uppercase tracking-[0.2em] text-Alumco-blue">Alertas de configuración</h3>
    <p class="mt-1 text-sm font-medium text-gray-500">Cada alerta muestra registros afectados y enlaces de corrección.</p>

    <div class="mt-5 space-y-4">
        @foreach ($alerts as $alert)
            <details wire:key="config-alert-{{ $alert['key'] }}" class="rounded-2xl border border-gray-100 bg-white p-4" @if($alert['count'] > 0) open @endif>
                <summary class="flex cursor-pointer items-center justify-between gap-4">
                    <span class="text-sm font-black text-Alumco-gray">{{ $alert['label'] }}</span>
                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $alert['level'] === 'danger' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $alert['count'] }}</span>
                </summary>
                <div class="mt-4 space-y-2">
                    @forelse ($alert['records'] as $record)
                        <div class="rounded-xl bg-gray-50 px-3 py-2">
                            <p class="text-sm font-bold text-Alumco-gray">{{ $record['label'] }}</p>
                            <p class="text-xs font-medium text-gray-500">{{ $record['detail'] }}</p>
                            @if ($record['url'])
                                <a href="{{ $record['url'] }}" class="mt-2 inline-flex text-xs font-black text-Alumco-blue hover:underline">Abrir registro</a>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm font-bold text-green-700">Sin registros afectados.</p>
                    @endforelse
                </div>
            </details>
        @endforeach
    </div>
</section>
