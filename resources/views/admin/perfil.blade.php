@extends('layouts.panel')

@section('title', 'Mi Perfil')
@section('header_title', 'Perfil de Usuario')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h2 class="text-3xl font-display font-black text-Alumco-blue">Mi Perfil</h2>
        <p class="text-Alumco-gray/50 font-bold uppercase tracking-wider text-[10px] mt-1">Información de tu cuenta administrativa</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Columna Izquierda: Avatar y Rol -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-[24px] p-8 shadow-sm border border-gray-100 text-center">
                @php
                    $initials = collect(explode(' ', trim($user->name)))
                        ->map(fn($w) => strtoupper($w[0] ?? ''))
                        ->take(2)
                        ->join('');
                @endphp
                <div class="w-24 h-24 rounded-full bg-Alumco-blue/5 text-Alumco-blue flex items-center justify-center mx-auto mb-6">
                    <span class="font-display font-black text-3xl">{{ $initials }}</span>
                </div>

                <h3 class="font-display font-bold text-xl text-Alumco-gray leading-tight mb-1">{{ $user->name }}</h3>
                <p class="text-sm text-Alumco-gray/50 mb-4">{{ $user->email }}</p>

                <div class="inline-flex items-center px-3 py-1 rounded-full bg-Alumco-blue text-white text-[10px] font-black uppercase tracking-widest">
                    {{ $user->roles->first()?->name ?? 'Administrador' }}
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Detalles -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/30">
                    <h4 class="text-xs font-display font-black uppercase tracking-[0.2em] text-Alumco-blue/40">Datos Personales</h4>
                </div>
                
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-1">
                        <p class="text-[11px] font-black text-Alumco-blue/70 uppercase tracking-widest">Nombre Completo</p>
                        <p class="font-bold text-Alumco-gray">{{ $user->name }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-[11px] font-black text-Alumco-blue/70 uppercase tracking-widest">Correo Electrónico</p>
                        <p class="font-bold text-Alumco-gray">{{ $user->email }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-[11px] font-black text-Alumco-blue/70 uppercase tracking-widest">Fecha de Nacimiento</p>
                        <p class="font-bold text-Alumco-gray">
                            {{ $user->fecha_nacimiento ? \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d/m/Y') : 'No registrada' }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-[11px] font-black text-Alumco-blue/70 uppercase tracking-widest">Sexo / Género</p>
                        <p class="font-bold text-Alumco-gray">
                            @if($user->sexo == 'M') Masculino @elseif($user->sexo == 'F') Femenino @else {{ $user->sexo ?? 'No informado' }} @endif
                        </p>
                    </div>
                </div>

                <div class="px-8 py-6 border-b border-t border-gray-50 bg-gray-50/30">
                    <h4 class="text-xs font-display font-black uppercase tracking-[0.2em] text-Alumco-blue/40">Organización</h4>
                </div>

                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-1">
                        <p class="text-[11px] font-black text-Alumco-blue/70 uppercase tracking-widest">Sede Asignada</p>
                        <p class="font-bold text-Alumco-gray">{{ $user->sede->nombre ?? 'Sin Sede' }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-[11px] font-black text-Alumco-blue/70 uppercase tracking-widest">Estamento / Área</p>
                        <p class="font-bold text-Alumco-gray">{{ $user->estamento->nombre ?? 'Sin Estamento' }}</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 p-6 rounded-[24px] bg-amber-50 border border-amber-100 flex gap-4">
                <svg class="w-6 h-6 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86l-7.1 12.3A1 1 0 004.06 18h15.88a1 1 0 00.87-1.84l-7.1-12.3a1 1 0 00-1.74 0z"></path>
                </svg>
                <div>
                    <p class="text-sm text-amber-800 font-bold mb-1">Cuenta Administrativa</p>
                    <p class="text-xs text-amber-700 leading-relaxed">
                        Tienes acceso a funciones críticas del sistema. Para cambiar tu contraseña o datos sensibles, contacta al soporte técnico o utiliza el flujo de recuperación de contraseña al iniciar sesión.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
