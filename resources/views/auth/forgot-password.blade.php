@extends('layouts.auth')

@section('title', 'Recuperar contraseña - Alumco')

@section('content')
    <div class="overflow-hidden rounded-3xl border border-white/40 bg-white/80 shadow-2xl backdrop-blur-xl">
        <div class="bg-Alumco-blue/90 px-8 py-6 lg:px-12">
            <h1 class="font-display text-2xl font-black tracking-tight text-white sm:text-3xl">
                Recuperar contraseña
            </h1>
            <p class="mt-1 text-sm font-medium text-Alumco-cyan">Te ayudaremos a volver a ingresar</p>
        </div>

        <form method="POST" action="{{ route('password.email') }}" class="space-y-6 px-8 py-8 lg:px-12 lg:py-10" novalidate>
            @csrf

            <p class="text-base font-medium text-Alumco-gray/70">
                Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña de forma segura.
            </p>

            @if (session('status'))
                <x-alert type="success" :message="session('status')" class="mb-6" />
            @endif

            <!-- Correo -->
            <div class="group space-y-2">
                <label for="email" class="text-lg font-extrabold text-Alumco-gray transition-colors group-focus-within:text-Alumco-blue">Correo electrónico</label>

                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-5 flex items-center text-Alumco-gray/30 transition-colors group-focus-within:text-Alumco-blue/50" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                            <path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a1.75 1.75 0 01-1.644 0L1.5 8.67z" />
                            <path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a.25.25 0 00.286 0L22.5 6.908z" />
                        </svg>
                    </span>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="ejemplo@correo.com"
                        class="h-14 w-full rounded-2xl border-2 border-slate-200/60 bg-white/50 pl-14 pr-4 text-lg font-bold text-Alumco-gray transition-all placeholder:text-slate-400 focus:border-Alumco-blue focus:bg-white focus:ring-4 focus:ring-Alumco-blue/10 @error('email') border-red-500 @enderror"
                    >
                </div>
                @error('email')
                    <p class="text-sm font-bold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botones -->
            <div class="flex flex-col items-center gap-6 pt-2">
                <x-auth.primary-button>Enviar enlace de recuperación</x-auth.primary-button>

                <a href="{{ route('login') }}" class="flex items-center gap-2 text-sm font-bold text-Alumco-blue transition hover:text-Alumco-coral focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                        <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
                    </svg>
                    Volver al inicio de sesión
                </a>
            </div>
        </form>
    </div>
@endsection
