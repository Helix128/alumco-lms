<section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h3 class="text-sm font-black uppercase tracking-[0.18em] text-Alumco-blue">Recursos multimedia</h3>
            <p class="mt-1 text-xs font-bold text-gray-400">Persistencia, procesamientos y recursos legados.</p>
        </div>
        <span class="rounded-full px-3 py-1 text-xs font-black {{ $capacity['blocked'] ? 'bg-red-100 text-red-700' : ($capacity['warning'] ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-700') }}">
            {{ $capacity['total'] ? number_format($capacity['percent'], 1).'%' : 'S3/R2' }}
        </span>
    </div>
    <dl class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
        @foreach ([
            'Legados faltantes' => $legacyMissing,
            'Fallidos' => $failed,
            'Atascados' => $stuck,
            'Sin referencia' => $unreferenced,
            'Cargas vencidas' => $expiredUploads,
            'Espacio libre' => $capacity['total'] ? number_format($capacity['free'] / 1073741824, 1).' GB' : 'Administrado',
        ] as $label => $value)
            <div class="rounded-2xl bg-gray-50 p-4">
                <dt class="text-[10px] font-black uppercase tracking-wider text-gray-400">{{ $label }}</dt>
                <dd class="mt-1 text-xl font-black text-Alumco-gray">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>
    @if ($capacity['blocked'])
        <p class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-xs font-bold text-red-700">Las nuevas cargas están bloqueadas: se alcanzó el 90% o quedan menos de 5 GB libres.</p>
    @endif
</section>
