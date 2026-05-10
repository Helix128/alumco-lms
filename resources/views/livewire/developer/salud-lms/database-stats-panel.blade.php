<section class="admin-surface p-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h3 class="text-sm font-black uppercase tracking-[0.2em] text-Alumco-blue">Base de datos</h3>
            <p class="mt-1 text-sm font-medium text-gray-500">Tamaño, filas y tablas que concentran más peso.</p>
        </div>
        <span class="rounded-full bg-Alumco-blue/10 px-3 py-1 text-xs font-black text-Alumco-blue">{{ $stats['connection'] }} · {{ $stats['database'] }}</span>
    </div>

    <div class="mt-5 grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-2xl bg-gray-50 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Tamaño</p>
            <p class="mt-1 text-2xl font-black text-Alumco-blue">{{ $stats['total_size_mb'] }} MB</p>
        </div>
        <div class="rounded-2xl bg-gray-50 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Data / índices</p>
            <p class="mt-1 text-sm font-black text-Alumco-gray">{{ $stats['data_size_mb'] }} MB / {{ $stats['index_size_mb'] }} MB</p>
        </div>
        <div class="rounded-2xl bg-gray-50 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Tablas / filas</p>
            <p class="mt-1 text-sm font-black text-Alumco-gray">{{ $stats['table_count'] }} / {{ $stats['total_rows'] }}</p>
            <p class="mt-1 truncate text-xs font-semibold text-gray-500">Mayor: {{ $stats['largest_table'] }}</p>
        </div>
        <div class="rounded-2xl bg-gray-50 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Jobs</p>
            <p class="mt-1 text-sm font-black text-Alumco-gray">{{ $stats['pending_jobs'] }} pendientes · {{ $stats['failed_jobs'] }} fallidos</p>
        </div>
    </div>

    <div class="mt-5 overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-gray-100 text-[10px] font-black uppercase tracking-widest text-gray-400">
                    <th class="pb-3">Tabla</th>
                    <th class="pb-3 text-right">Filas</th>
                    <th class="pb-3 text-right">Data / índices</th>
                    <th class="pb-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($stats['tables'] as $table)
                    <tr wire:key="db-table-{{ $table['name'] }}">
                        <td class="py-3 text-sm font-bold text-Alumco-gray">{{ $table['name'] }}</td>
                        <td class="py-3 text-right text-sm font-bold text-gray-500">{{ $table['rows'] }}</td>
                        <td class="py-3 text-right text-sm font-bold text-gray-500">{{ $table['data_mb'] }} / {{ $table['index_mb'] }} MB</td>
                        <td class="py-3 text-right text-sm font-black text-Alumco-gray">{{ $table['size_mb'] }} MB</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
