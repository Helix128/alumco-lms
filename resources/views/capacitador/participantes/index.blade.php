@extends('layouts.panel')

@section('title', 'Participantes — ' . $curso->titulo)

@section('content')
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('capacitador.cursos.show', $curso) }}" class="text-sm text-Alumco-blue hover:underline">
            ← Volver a la capacitación
        </a>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h2 class="text-2xl font-bold text-Alumco-gray">Participantes — {{ $curso->titulo }}</h2>
        @if (auth()->user()->isCapacitadorInterno() || auth()->user()->hasAdminAccess())
            <a href="{{ route('capacitador.cursos.participantes.exportar', $curso) }}"
               class="border border-Alumco-green-vivid text-Alumco-green-vivid px-4 py-2 rounded-lg
                      text-sm font-semibold hover:bg-Alumco-green/20 transition">
                Exportar Excel
            </a>
        @endif
    </div>

    {{-- Gestión de estamentos (Interno o Admin) --}}
    @if (auth()->user()->isCapacitadorInterno() || auth()->user()->hasAdminAccess())
        <div class="filter-card mb-6">
            <h3 class="font-bold text-Alumco-gray mb-4">Asignación de estamentos</h3>
            @livewire('capacitador.gestion-estamentos', ['curso' => $curso])
        </div>
    @endif

    {{-- Tabla de participantes --}}
    <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Nombre</th>
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Email</th>
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">RUT</th>
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 hidden sm:table-cell">Estamento</th>
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 hidden md:table-cell">Sede</th>
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Progreso</th>
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 text-right">Certificado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($usuarios as $usuario)
                        <tr class="hover:bg-Alumco-cream/30 transition-colors group">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-Alumco-blue/5 text-Alumco-blue flex items-center justify-center font-display font-bold text-xs shrink-0">
                                        {{ collect(explode(' ', $usuario->name))->map(fn($n) => $n[0])->take(2)->join('') }}
                                    </div>
                                    <p class="font-display font-bold text-Alumco-gray leading-tight text-sm">{{ $usuario->name }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-sm text-Alumco-gray font-medium">{{ $usuario->email }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-xs font-bold text-Alumco-blue/60 uppercase tracking-tight">{{ $usuario->rut ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-5 hidden sm:table-cell">
                                <span class="text-sm font-bold text-Alumco-gray">{{ $usuario->estamento?->nombre ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-5 hidden md:table-cell">
                                <span class="text-[11px] font-black text-Alumco-blue/40 uppercase tracking-tighter">{{ $usuario->sede?->nombre ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden min-w-[80px]"
                                         role="progressbar"
                                         aria-valuenow="{{ $usuario->progreso_porcentaje }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100"
                                         aria-label="Progreso del participante {{ $usuario->name }}">
                                        <div class="h-full {{ $usuario->progreso_porcentaje >= 100 ? 'bg-Alumco-green-vivid' : 'bg-Alumco-blue' }} rounded-full transition-all duration-500" 
                                             style="width: {{ $usuario->progreso_porcentaje }}%"></div>
                                    </div>
                                    <span class="text-xs font-display font-black text-Alumco-blue w-10 text-right">{{ $usuario->progreso_porcentaje }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                @if ($usuario->certificado)
                                    <a href="{{ route('capacitador.certificados.descargar', $usuario->certificado) }}"
                                       class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-black uppercase bg-Alumco-green/20 text-Alumco-blue border border-Alumco-green/30 hover:brightness-95 transition" title="Descargar">
                                        Descargar
                                    </a>
                                @elseif ($usuario->progreso_porcentaje >= 100)
                                    <form action="{{ route('capacitador.certificados.generar', [$curso, $usuario]) }}"
                                          method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-black uppercase bg-Alumco-blue/10 text-Alumco-blue border border-Alumco-blue/20 hover:bg-Alumco-blue hover:text-white transition" title="Generar">
                                            Generar
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[10px] font-black uppercase text-gray-300 tracking-widest">En proceso</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-Alumco-gray/40">
                                <div class="flex flex-col items-center opacity-40">
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <p class="font-display font-bold">No hay participantes aún</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('estamentos-guardados', () => {
            window.location.reload();
        });
    });
</script>
@endpush
