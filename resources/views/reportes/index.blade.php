<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard ONG Alunco - Reportes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <div class="container mx-auto px-4 pt-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Reporte de Capacitaciones</h1>
                <p class="text-gray-600">Sistema LMS - ONG Alunco</p>
            </div>
            <a href="{{ route('reportes.exportar', request()->query()) }}"
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Exportar a Excel
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <form action="{{ route('reportes.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Estamento</label>
                    <select name="estamento_id" class="w-full border-gray-300 rounded-md shadow-sm border p-2 bg-white">
                        <option value="">Todos los estamentos</option>
                        @foreach($estamentos as $estamento)
                        <option value="{{ $estamento->id }}" {{ request('estamento_id')==$estamento->id ? 'selected' :
                            '' }}>
                            {{ $estamento->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Curso Aprobado</label>
                    <select name="curso_id" class="w-full border-gray-300 rounded-md shadow-sm border p-2 bg-white">
                        <option value="">Cualquier curso</option>
                        @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}" {{ request('curso_id')==$curso->id ? 'selected' : '' }}>
                            {{ $curso->titulo }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Aprobado Desde</label>
                    <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm border p-2">
                </div>

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Aprobado Hasta</label>
                    <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm border p-2">
                </div>

                <div>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow">Filtrar</button>
                    <a href="{{ route('reportes.index') }}"
                        class="ml-2 text-gray-600 hover:text-gray-900 underline text-sm">Limpiar</a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Nombre</th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Sede</th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Estamento</th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Estado</th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Cursos Aprobados</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $user)
                    <tr>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap font-medium">{{ $user->name }}</p>
                            <p class="text-gray-500 text-xs">{{ $user->email }}</p>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap">{{ $user->sede->nombre ?? 'N/A' }}</p>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <span class="relative inline-block px-3 py-1 font-semibold text-blue-900 leading-tight">
                                <span aria-hidden class="absolute inset-0 bg-blue-200 opacity-50 rounded-full"></span>
                                <span class="relative">{{ $user->estamento->nombre ?? 'N/A' }}</span>
                            </span>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            @if($user->activo)
                            <span class="text-green-600 font-bold">Activo</span>
                            @else
                            <span class="text-red-600 font-bold">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            @forelse($user->certificados as $certificado)
                            <div class="mb-1">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    ✓ {{ $certificado->curso->titulo }}
                                </span>
                                <span class="text-xs text-gray-500 ml-1">({{
                                    $certificado->fecha_emision->format('d/m/Y') }})</span>
                            </div>
                            @empty
                            <span class="text-gray-400 italic">Sin cursos aprobados</span>
                            @endforelse
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5"
                            class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">
                            No se encontraron registros con esos filtros.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-5 py-3 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
                {{ $usuarios->links() }}
            </div>
        </div>
    </div>
</body>

</html>