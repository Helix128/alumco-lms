<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard ONG Alumco - Reportes</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        Alumco: {
                            blue: '#205099',
                            green: '#AFDD83',
                            coral: '#FF6364',
                            yellow: '#F8B606',
                            cyan: '#A5B6F5',
                            gray: '#5E5E5E',
                            cream: '#FFF8EB'
                        }
                    },
                    fontFamily: {
                        sans: ['Roboto', 'sans-serif'],
                        logo: ['Nexa Black', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        .rounded-Alumco { border-radius: 8px; } /* 8pt radius */
    </style>
</head>

<body class="bg-Alumco-cream font-sans leading-normal tracking-normal text-Alumco-gray h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-20 bg-Alumco-blue flex flex-col items-center py-6 gap-8 z-10 shrink-0">
        <!-- Icons placeholder (SVG) -->
        <div class="text-white hover:text-Alumco-cyan cursor-pointer">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
        </div>
        <div class="text-white hover:text-Alumco-cyan cursor-pointer">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        </div>
        <div class="text-white hover:text-Alumco-cyan cursor-pointer">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
        <div class="mt-auto text-white hover:text-Alumco-cyan cursor-pointer">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col overflow-hidden relative">
        
        <!-- Header -->
        <header class="bg-Alumco-blue text-white py-4 px-8 shadow-sm">
            <h1 class="text-2xl font-bold">Vista de administrador</h1>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto p-8 relative pb-24">
            
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-[32px] font-bold text-Alumco-gray mb-1">Reporte de Capacitaciones</h2>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('reportes.exportar', request()->query()) }}"
                        class="bg-Alumco-green hover:bg-opacity-90 text-Alumco-blue font-bold py-2 px-4 rounded-Alumco shadow flex items-center transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Descargar reporte Excel
                    </a>
                </div>
            </div>

        <div class="mb-8">
            <form action="{{ route('reportes.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-Alumco-gray mb-1">Filtrar por Estamento</label>
                    <select name="estamento_id" class="w-full border-Alumco-gray/30 rounded-Alumco shadow-sm border p-2 bg-white text-Alumco-gray outline-none focus:border-Alumco-blue">
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
                    <label class="block text-sm font-medium text-Alumco-gray mb-1">Curso Aprobado</label>
                    <select name="curso_id" class="w-full border-Alumco-gray/30 rounded-Alumco shadow-sm border p-2 bg-white text-Alumco-gray outline-none focus:border-Alumco-blue">
                        <option value="">Cualquier curso</option>
                        @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}" {{ request('curso_id')==$curso->id ? 'selected' : '' }}>
                            {{ $curso->titulo }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-Alumco-gray mb-1">Aprobado Desde</label>
                    <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}"
                        class="w-full border-Alumco-gray/30 rounded-Alumco shadow-sm border p-2 text-Alumco-gray outline-none focus:border-Alumco-blue">
                </div>

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-Alumco-gray mb-1">Aprobado Hasta</label>
                    <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}"
                        class="w-full border-Alumco-gray/30 rounded-Alumco shadow-sm border p-2 text-Alumco-gray outline-none focus:border-Alumco-blue">
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="bg-Alumco-blue hover:bg-opacity-90 text-white font-bold py-2 px-6 rounded-Alumco shadow transition-colors">Filtrar</button>
                    <a href="{{ route('reportes.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-Alumco-gray font-bold py-2 px-6 rounded-Alumco shadow transition-colors flex items-center">Limpiar</a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-Alumco border border-Alumco-gray/20 overflow-hidden shadow-sm">
            <table class="min-w-full leading-normal text-Alumco-gray">
                <thead>
                    <tr>
                        <th
                            class="px-5 py-3 border-b border-Alumco-gray/20 bg-white text-left text-sm font-bold tracking-wider">
                            Nombre</th>
                        <th
                            class="px-5 py-3 border-b border-Alumco-gray/20 bg-white text-left text-sm font-bold tracking-wider">
                            Sede</th>
                        <th
                            class="px-5 py-3 border-b border-Alumco-gray/20 bg-white text-left text-sm font-bold tracking-wider">
                            Estamento</th>
                        <th
                            class="px-5 py-3 border-b border-Alumco-gray/20 bg-white text-left text-sm font-bold tracking-wider">
                            Estado</th>
                        <th
                            class="px-5 py-3 border-b border-Alumco-gray/20 bg-white text-left text-sm font-bold tracking-wider">
                            Cursos Aprobados</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $user)
                    <tr class="hover:bg-Alumco-cream/50 transition-colors">
                        <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm">
                            <p class="font-bold whitespace-no-wrap">{{ $user->name }}</p>
                            <p class="text-xs opacity-75 mt-0.5">{{ $user->email }}</p>
                        </td>
                        <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm font-medium">
                            <p class="whitespace-no-wrap">{{ $user->sede->nombre ?? 'N/A' }}</p>
                        </td>
                        <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm font-medium">
                            {{ $user->estamento->nombre ?? 'N/A' }}
                        </td>
                        <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm">
                            @if($user->activo)
                            <span class="text-green-600 font-bold">Activo</span>
                            @else
                            <span class="text-red-600 font-bold">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm">
                            @forelse($user->certificados as $certificado)
                            <div class="mb-1">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-Alumco-green text-Alumco-blue">
                                    ✓ {{ $certificado->curso->titulo }}
                                </span>
                                <span class="text-xs opacity-75 ml-1">({{
                                    $certificado->fecha_emision->format('d/m/Y') }})</span>
                            </div>
                            @empty
                            <span class="opacity-75 italic text-sm">Sin cursos aprobados</span>
                            @endforelse
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5"
                            class="px-5 py-8 border-b border-Alumco-gray/10 bg-transparent text-sm text-center opacity-75">
                            No se encontraron registros con esos filtros.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-5 py-3 bg-white border-t border-Alumco-gray/10 flex flex-col xs:flex-row items-center xs:justify-between">
                {{ $usuarios->links() }}
            </div>
        </div>

        <!-- Brand Watermark -->
        <div class="mt-8 flex justify-end items-center pointer-events-none w-full pb-8">
            <!-- Logo real referenciado -->
            <img src="{{ asset('images/logo/alumco-full.svg') }}" alt="Ong Alumco" class="h-14 w-auto pointer-events-auto" dropzone="none">
        </div>

        </main>
    </div>
</body>

</html>