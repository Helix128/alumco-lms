@extends('layouts.panel')

@section('title', 'Mi Perfil')
@section('header_title', 'Perfil de Usuario')

@section('content')
@php
    $initials = collect(explode(' ', trim($user->name)))
        ->map(fn ($word) => strtoupper($word[0] ?? ''))
        ->take(2)
        ->join('');
@endphp

<div class="mx-auto max-w-5xl space-y-6 animate-page-entry">
    {{-- Page Header --}}
    <div class="flex flex-col gap-1">
        <h2 class="admin-page-title">Mi Perfil Profesional</h2>
        <p class="admin-page-subtitle">Identidad administrativa y firma autorizada.</p>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
        {{-- Sidebar Card - Compact Organic --}}
        <aside class="space-y-6">
            <div class="worker-soft-panel overflow-hidden border-none shadow-lg shadow-Alumco-blue/5">
                <div class="relative h-20 bg-Alumco-blue">
                    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 16px 16px;"></div>
                </div>
                
                <div class="relative px-6 pb-6 text-center">
                    <div class="-mt-10 mb-4 flex justify-center">
                        <div class="inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-white p-1 shadow-lg shadow-Alumco-blue/10">
                            <div class="flex h-full w-full items-center justify-center rounded-2xl bg-gradient-to-br from-Alumco-blue to-Alumco-blue/80 text-white">
                                <span class="font-display text-[24px] font-black">{{ $initials }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <h3 class="font-display text-[20px] font-black text-Alumco-blue tracking-tight leading-tight">{{ $user->name }}</h3>
                        <p class="text-[10px] font-black text-Alumco-gray/40 uppercase tracking-widest">{{ $user->email }}</p>
                    </div>

                    <div class="mt-4 flex flex-wrap justify-center gap-1.5">
                        @foreach ($user->roles as $role)
                            <span class="rounded-xl bg-Alumco-yellow/10 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-Alumco-gold-accessible ring-1 ring-Alumco-yellow/15">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </div>

                    <div class="mt-6 space-y-3 border-t border-slate-100 pt-5">
                        <div class="flex justify-between items-center px-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-Alumco-blue/30">Sede</span>
                            <span class="text-[14px] font-bold text-Alumco-gray">{{ $user->sede->nombre ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center px-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-Alumco-blue/30">Área</span>
                            <span class="text-[14px] font-bold text-Alumco-gray">{{ $user->estamento->nombre ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Support Card - Compact (Solo para no-desarrolladores) --}}
            @if (!$user->isDesarrollador())
                <div class="worker-card p-4 bg-slate-50 border-none shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-Alumco-yellow/20 text-Alumco-gold-accessible">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-[10px] font-black text-Alumco-blue uppercase tracking-widest">Soporte</h4>
                            <p class="text-[11px] font-bold text-Alumco-gray/50 leading-tight">¿Necesitas cambiar datos? Contacta con nosotros.</p>
                            <a href="{{ route('support.index') }}" class="inline-flex items-center gap-1 text-[11px] font-black text-Alumco-blue group">
                                <span>Ayuda</span>
                                <svg class="h-3 w-3 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

        </aside>

        {{-- Main Content area --}}
        <div class="space-y-6">
            {{-- Account Information Section - Compact --}}
            <section class="worker-card overflow-hidden border-none shadow-lg shadow-Alumco-blue/5">
                <div class="border-b border-slate-50 bg-slate-50/20 px-6 py-4">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-Alumco-green"></div>
                        <h3 class="font-display text-[18px] font-black text-Alumco-blue uppercase tracking-tight">Información de Identidad</h3>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-Alumco-blue/30 ml-2">Nombre completo</label>
                            <div class="rounded-2xl bg-slate-50/50 px-5 py-3.5 text-[14px] font-bold text-Alumco-blue border border-transparent">
                                {{ $user->name }}
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-Alumco-blue/30 ml-2">Correo institucional</label>
                            <div class="rounded-2xl bg-slate-50/50 px-5 py-3.5 text-[14px] font-bold text-Alumco-blue border border-transparent break-all">
                                {{ $user->email }}
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-Alumco-blue/30 ml-2">Nacimiento</label>
                            <div class="rounded-2xl bg-slate-50/50 px-5 py-3.5 text-[14px] font-bold text-Alumco-blue border border-transparent">
                                {{ $user->fecha_nacimiento ? \Carbon\Carbon::parse($user->fecha_nacimiento)->translatedFormat('d F, Y') : 'No registrado' }}
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-Alumco-blue/30 ml-2">Género / Sexo</label>
                            <div class="rounded-2xl bg-slate-50/50 px-5 py-3.5 text-[14px] font-bold text-Alumco-blue border border-transparent">
                                @if ($user->sexo === 'M') Masculino @elseif ($user->sexo === 'F') Femenino @else {{ $user->sexo ?? 'N/E' }} @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Signature Section (Livewire) --}}
            @livewire('profile.digital-signature')
        </div>
    </div>
</div>
@endsection
