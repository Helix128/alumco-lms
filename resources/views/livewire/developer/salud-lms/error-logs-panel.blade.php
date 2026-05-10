<section
    class="admin-surface p-6"
    x-data="{
        copiedKey: null,
        async copyText(content, key) {
            try {
                await navigator.clipboard.writeText(content);
            } catch (error) {
                const textarea = document.createElement('textarea');
                textarea.value = content;
                textarea.setAttribute('readonly', 'readonly');
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
            }

            this.copiedKey = key;
            setTimeout(() => this.copiedKey = null, 2500);
        }
    }"
>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h3 class="text-sm font-black uppercase tracking-[0.2em] text-Alumco-blue">Logs Laravel</h3>
            <p class="mt-1 text-sm font-medium text-gray-500">Revisa entradas recientes y copia el stack trace exacto que estás depurando.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <select wire:model.live="level" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-bold outline-none focus:border-Alumco-blue">
                <option value="all">Todos</option>
                <option value="error">Error</option>
                <option value="warning">Warning</option>
                <option value="info">Info</option>
            </select>
            <input type="search" wire:model.live.debounce.400ms="search" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-bold outline-none focus:border-Alumco-blue" placeholder="Buscar en logs">
        </div>
    </div>

    <div class="mt-5 grid gap-4 xl:grid-cols-[minmax(0,1fr)_18rem]">
        <div class="rounded-2xl border border-gray-100 bg-white p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-blue/60">Uso en disco</p>
                    <p class="mt-1 font-display text-3xl font-black text-Alumco-blue">{{ $logStorage['total_human'] }}</p>
                    <p class="mt-1 text-xs font-bold text-gray-400">{{ $logStorage['file_count'] }} archivos · {{ $logStorage['directory'] }}</p>
                </div>

                <div class="rounded-xl bg-gray-50 px-4 py-3 text-xs font-bold text-gray-500">
                    <span class="font-black text-Alumco-gray">Flujo recomendado:</span> filtra el incidente, abre su contexto y copia solo ese stack trace.
                </div>
            </div>

            <div class="mt-5 overflow-hidden rounded-2xl border border-gray-100">
                <table class="w-full text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-gray-400">Archivo</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-gray-400">Tamaño</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-gray-400">Actualizado</th>
                            <th class="px-4 py-3 text-right text-[10px] font-black uppercase tracking-widest text-gray-400">Limpieza</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($logStorage['files'] as $file)
                            <tr wire:key="log-file-{{ $file['name'] }}">
                                <td class="px-4 py-3">
                                    <span class="text-sm font-black text-Alumco-gray">{{ $file['name'] }}</span>
                                    @if ($file['is_current'])
                                        <span class="ml-2 rounded-full bg-Alumco-blue/10 px-2 py-0.5 text-[9px] font-black uppercase tracking-widest text-Alumco-blue">actual</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-gray-500">{{ $file['size_human'] }}</td>
                                <td class="px-4 py-3 text-sm font-bold text-gray-500">{{ $file['modified_at'] }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            wire:click="requestDeleteFile(@js($file['name']))"
                                            class="rounded-lg px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-red-600 transition hover:bg-red-50"
                                        >
                                            Borrar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm font-bold text-gray-400">No hay archivos de log.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($pendingAction)
            <aside class="rounded-2xl border border-red-100 bg-red-50 p-5 xl:w-80">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-red-700">Confirmación requerida</p>
                <p class="mt-2 text-sm font-bold leading-relaxed text-red-900">{{ $status }}</p>
                <div class="mt-4 flex gap-2">
                    <button type="button" wire:click="confirmPendingAction" class="rounded-xl bg-red-600 px-4 py-2 text-xs font-black uppercase tracking-widest text-white">
                        Confirmar
                    </button>
                    <button type="button" wire:click="cancelPendingAction" class="rounded-xl bg-white px-4 py-2 text-xs font-black uppercase tracking-widest text-gray-500">
                        Cancelar
                    </button>
                </div>
            </aside>
        @elseif ($status)
            <aside class="rounded-2xl border border-Alumco-blue/10 bg-Alumco-blue/5 p-5 xl:w-80">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-blue">Estado</p>
                <p class="mt-2 text-sm font-bold leading-relaxed text-Alumco-blue">{{ $status }}</p>
            </aside>
        @endif
    </div>

    <div class="mt-5 space-y-3">
        @forelse ($logs as $entry)
            @php($entryKey = md5($entry['timestamp'].$entry['message']))
            <article wire:key="log-{{ $entryKey }}" class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-3 py-1 text-[11px] font-black uppercase {{ in_array($entry['level'], ['error', 'critical', 'alert', 'emergency'], true) ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $entry['level'] }}</span>
                        <span class="text-xs font-bold text-gray-500">{{ $entry['timestamp'] }}</span>
                    </div>
                </div>
                <p class="mt-3 text-sm font-bold text-Alumco-gray">{{ $entry['message'] }}</p>
                @if ($entry['context'])
                    <details class="mt-3">
                        <summary class="cursor-pointer text-xs font-black uppercase tracking-widest text-Alumco-blue">Contexto / stack trace</summary>
                        <div class="mt-3 flex justify-end">
                            <button
                                type="button"
                                x-on:click.stop="copyText(@js($entry['context']), @js($entryKey))"
                                class="rounded-lg bg-Alumco-blue/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-Alumco-blue transition hover:bg-Alumco-blue/15"
                            >
                                <span x-show="copiedKey !== @js($entryKey)">Copiar stack trace</span>
                                <span x-show="copiedKey === @js($entryKey)" x-cloak>Copiado</span>
                            </button>
                        </div>
                        <pre class="mt-2 max-h-[34rem] overflow-auto rounded-xl border border-gray-800 bg-gray-950 p-4 font-mono text-xs leading-6 text-gray-50 shadow-inner">{{ $entry['context'] }}</pre>
                    </details>
                @endif
            </article>
        @empty
            <p class="rounded-2xl border border-dashed border-gray-200 p-8 text-center text-sm font-bold text-gray-400">No hay entradas de log para el filtro actual.</p>
        @endforelse
    </div>
</section>
