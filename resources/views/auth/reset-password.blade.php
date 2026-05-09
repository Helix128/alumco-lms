@extends('layouts.auth')

@section('title', 'Nueva contraseña - Alumco')

@section('content')
    <div class="overflow-hidden rounded-3xl border border-white/40 bg-white/80 shadow-2xl backdrop-blur-xl">
        <div class="bg-Alumco-blue/90 px-8 py-6 lg:px-12">
            <h1 class="font-display text-2xl font-black tracking-tight text-white sm:text-3xl">
                Configura tu contraseña
            </h1>
            <p class="mt-1 text-sm font-medium text-Alumco-cyan">Crea una clave segura para tu cuenta</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-6 px-8 py-8 lg:px-12 lg:py-10" novalidate x-data="{ showPassword: false }">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

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
                        value="{{ old('email', $email) }}"
                        required
                        autocomplete="username"
                        placeholder="ejemplo@correo.com"
                        class="h-14 w-full rounded-2xl border-2 border-slate-200/60 bg-white/50 pl-14 pr-4 text-lg font-bold text-Alumco-gray transition-all placeholder:text-slate-400 focus:border-Alumco-blue focus:bg-white focus:ring-4 focus:ring-Alumco-blue/10 @error('email') border-red-500 @enderror"
                    >
                </div>
                @error('email')
                    <p class="text-sm font-bold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Contraseña -->
            <div class="group space-y-2">
                <div class="flex items-center justify-between">
                    <label for="password" class="text-lg font-extrabold text-Alumco-gray transition-colors group-focus-within:text-Alumco-blue">Nueva contraseña</label>
                    <span class="text-xs font-bold text-Alumco-gray/40">Mín. 8 caracteres</span>
                </div>

                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-5 flex items-center text-Alumco-gray/30 transition-colors group-focus-within:text-Alumco-blue/50" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                            <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 00-5.25 5.25v3a3 3 0 00-3 3v6.75a3 3 0 003 3h10.5a3 3 0 003-3v-6.75a3 3 0 00-3-3v-3c0-2.9-2.35-5.25-5.25-5.25zm3.75 8.25v-3a3.75 3.75 0 10-7.5 0v3h7.5z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        name="password"
                        required
                        autofocus
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="h-14 w-full rounded-2xl border-2 border-slate-200/60 bg-white/50 pl-14 pr-14 text-lg font-bold text-Alumco-gray transition-all placeholder:text-slate-400 focus:border-Alumco-blue focus:bg-white focus:ring-4 focus:ring-Alumco-blue/10 @error('password') border-red-500 @enderror"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-5 text-Alumco-gray/30 hover:text-Alumco-blue focus:outline-none"
                    >
                        <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 17.772 17.772m0 0a10.446 10.446 0 0 1-2.909 1.557m0 0A10.473 10.473 0 0 1 12 19.5c-4.756 0-8.773-3.162-10.065-7.498a10.522 10.522 0 0 1 4.293-5.774M6.228 6.228 1 1m16.772 16.772 5.228 5.228" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-sm font-bold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirmar contraseña -->
            <div class="group space-y-2">
                <label for="password_confirmation" class="text-lg font-extrabold text-Alumco-gray transition-colors group-focus-within:text-Alumco-blue">Confirmar contraseña</label>

                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-5 flex items-center text-Alumco-gray/30 transition-colors group-focus-within:text-Alumco-blue/50" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                            <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 00-5.25 5.25v3a3 3 0 00-3 3v6.75a3 3 0 003 3h10.5a3 3 0 003-3v-6.75a3 3 0 00-3-3v-3c0-2.9-2.35-5.25-5.25-5.25zm3.75 8.25v-3a3.75 3.75 0 10-7.5 0v3h7.5z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <input
                        id="password_confirmation"
                        :type="showPassword ? 'text' : 'password'"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="h-14 w-full rounded-2xl border-2 border-slate-200/60 bg-white/50 pl-14 pr-4 text-lg font-bold text-Alumco-gray transition-all placeholder:text-slate-400 focus:border-Alumco-blue focus:bg-white focus:ring-4 focus:ring-Alumco-blue/10"
                    >
                </div>
            </div>

            <!-- Botón -->
            <div class="flex flex-col items-center gap-6 pt-2">
                <x-auth.primary-button>Guardar nueva contraseña</x-auth.primary-button>

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
