{{-- Safelist para clases dinámicas de semáforo: bg-red-500 bg-yellow-500 bg-green-500 text-red-600 text-yellow-600 text-green-600 bg-red-100 bg-yellow-100 bg-green-100 text-red-700 text-yellow-700 text-green-700 --}}
<div class="sticky top-0 z-[60] -mx-6 -mt-6 mb-8 border-b border-gray-200 bg-white/95 shadow-sm backdrop-blur lg:-mx-10 lg:-mt-10">
    <div class="mx-auto flex max-w-screen-2xl flex-col gap-3 px-4 py-4 sm:px-6 lg:px-10 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex items-center gap-3">
            <span class="h-3.5 w-3.5 shrink-0 rounded-full {{ $status['level'] === 'danger' ? 'bg-red-500' : ($status['level'] === 'warning' ? 'bg-yellow-500' : 'bg-green-500') }}"></span>
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-gray-500">Estado global</p>
                <p class="font-display text-lg font-black {{ $status['level'] === 'danger' ? 'text-red-700' : ($status['level'] === 'warning' ? 'text-yellow-700' : 'text-green-700') }}">{{ $status['label'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Jobs fallidos</p>
                <p class="text-xl font-black text-Alumco-coral-accessible">{{ $status['failed_jobs'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Jobs pendientes</p>
                <p class="text-xl font-black text-Alumco-blue">{{ $status['pending_jobs'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Errores 1h</p>
                <p class="text-xl font-black text-Alumco-gray">{{ $status['errors_last_hour'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Alertas críticas</p>
                <p class="text-xl font-black text-Alumco-coral-accessible">{{ $status['critical_alerts'] }}</p>
            </div>
        </div>
    </div>
</div>
