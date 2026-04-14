<div class="p-6 relative">
    <div class="flex justify-between items-center mb-6 bg-white p-4 rounded-lg shadow border border-gray-200">
        <div class="flex items-center gap-4">
            <button wire:click="mesAnterior" class="px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded transition font-bold shadow-sm">&lt;</button>
            <button wire:click="mesSiguiente" class="px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded transition font-bold shadow-sm">&gt;</button>
            <h2 class="text-2xl font-bold text-gray-800 ml-4">
                {{ ucfirst(\Carbon\Carbon::create()->month($mesActual)->locale('es')->translatedFormat('F')) }} {{ $anioActual }}
            </h2>
        </div>
        
        <div class="flex items-center gap-4">
            <select wire:model.live="filtroTipo" class="border border-gray-300 rounded-md py-2 px-4 text-sm text-gray-700 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                <option value="todos">Todas las evaluaciones</option>
                <option value="examen">Solo Exámenes (Rojo)</option>
                <option value="control">Solo Controles (Amarillo)</option>
                <option value="taller">Solo Talleres (Verde)</option>
            </select>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
        <div style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr));" class="border-b border-gray-200 bg-gray-50">
            @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $diaSemana)
                <div class="py-3 text-center text-sm font-bold text-gray-600 border-r border-gray-200 last:border-r-0">{{ $diaSemana }}</div>
            @endforeach
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr));" class="bg-gray-200 gap-px">
            @php
                $fechaBase = \Carbon\Carbon::createFromDate($anioActual, $mesActual, 1);
                $diasEnMes = $fechaBase->daysInMonth;
                $espaciosVacios = $fechaBase->dayOfWeekIso - 1; 
                $hoy = \Carbon\Carbon::now();
            @endphp

            @for ($i = 0; $i < $espaciosVacios; $i++) <div class="bg-gray-50 min-h-[120px] p-2 opacity-50"></div> @endfor

            @for ($dia = 1; $dia <= $diasEnMes; $dia++)
                @php $esHoy = $hoy->day == $dia && $hoy->month == $mesActual && $hoy->year == $anioActual; @endphp

                <div class="bg-white min-h-[120px] p-2 transition group relative {{ $esHoy ? 'bg-blue-50/50' : '' }}">
                    <div class="flex justify-between items-start">
                        <span class="text-sm font-bold {{ $esHoy ? 'text-white bg-blue-600 rounded-full w-7 h-7 flex items-center justify-center' : 'text-gray-500' }}">
                            {{ $dia }}
                        </span>
                        
                        <button wire:click="abrirModal({{ $dia }})" title="Añadir al día {{ $dia }}" class="opacity-0 group-hover:opacity-100 text-blue-500 hover:text-white hover:bg-blue-500 rounded p-1 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </div>

                    <div class="mt-2 flex flex-col gap-1">
                        @foreach($evaluaciones as $index => $ev)
                            @if($ev['dia'] == $dia && $ev['mes'] == $mesActual && $ev['anio'] == $anioActual)
                                @if($filtroTipo == 'todos' || $filtroTipo == $ev['tipo'])
                                    
                                    <div wire:key="ev-{{ $ev['id'] }}" 
                                         class="group/ev relative text-xs px-2 py-1 rounded text-white font-semibold shadow-sm flex justify-between items-center cursor-default
                                                {{ $ev['tipo'] == 'examen' ? 'bg-red-500' : '' }}
                                                {{ $ev['tipo'] == 'control' ? 'bg-yellow-500' : '' }}
                                                {{ $ev['tipo'] == 'taller' ? 'bg-green-500' : '' }}
                                                {{ !in_array($ev['tipo'], ['examen', 'control', 'taller']) ? 'bg-gray-500' : '' }}">
                                        
                                        <span class="truncate">{{ $ev['titulo'] }}</span>
                                        
                                        <button wire:click="borrarEvaluacion('{{ $ev['id'] }}')" class="opacity-0 group-hover/ev:opacity-100 ml-1 hover:text-gray-200 transition cursor-pointer" title="Borrar">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>

                                @endif
                            @endif
                        @endforeach
                    </div>
                </div>
            @endfor
            
            @php $espaciosSobrantes = (7 - (($espaciosVacios + $diasEnMes) % 7)) % 7; @endphp
            @for ($i = 0; $i < $espaciosSobrantes; $i++) <div class="bg-gray-50 min-h-[120px] p-2 opacity-50"></div> @endfor
        </div>
    </div>

    @if($mostrarModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-xl w-96 overflow-hidden">
            <div class="bg-blue-600 px-4 py-3 flex justify-between items-center text-white">
                <h3 class="font-bold text-lg">Nueva Evaluación - Día {{ $diaSeleccionado }}</h3>
                <button wire:click="cerrarModal" class="text-white hover:text-gray-200 font-bold text-xl">&times;</button>
            </div>
            
            <div class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Título de la Evaluación</label>
                    <input type="text" wire:model="tituloNuevo" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: Control de lectura">
                </div>
                
                <div class="flex gap-4">
                    <div class="w-1/2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        <select wire:model="tipoNuevo" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500">
                            <option value="examen">Examen (Rojo)</option>
                            <option value="control">Control (Amarillo)</option>
                            <option value="taller">Taller (Verde)</option>
                        </select>
                    </div>
                    <div class="w-1/2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hora</label>
                        <input type="time" wire:model="horaNueva" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 flex justify-end gap-2 border-t">
                <button wire:click="cerrarModal" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 font-medium transition shadow-sm">Cancelar</button>
                <button wire:click="guardarEvaluacion" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium transition shadow-sm">Guardar</button>
            </div>
        </div>
    </div>
    @endif
</div>