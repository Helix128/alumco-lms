@extends('layouts.auth')

@section('title', 'Recuperar contrase&ntilde;a - Alumco')

@section('content')
    <div class="overflow-hidden rounded-xl border-2 border-slate-200 bg-white shadow-sm">
                        <div class="bg-Alumco-blue px-8 py-4 lg:py-5">
                            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl lg:text-4xl">
                                Recuperar contraseña
                            </h1>
                        </div>

                        <form method="POST" action="{{ route('password.email') }}" class="space-y-6 px-8 py-8 lg:px-12 lg:py-10" novalidate>
                            @csrf

                            <p class="text-base text-Alumco-gray/80">
                                Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
                            </p>

                            @if (session('status'))
                                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <!-- Correo -->
                            <div class="space-y-2">
                                <label for="email" class="text-2xl font-bold text-Alumco-gray lg:text-3xl">Correo electrónico</label>

                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-5 flex items-center text-Alumco-gray/40" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-7 w-7">
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
                                        class="h-14 w-full rounded-xl border-2 border-slate-200 bg-slate-50/30 pl-14 pr-4 text-xl font-medium text-Alumco-gray transition placeholder:text-slate-400 focus:border-Alumco-blue focus:bg-white focus:ring-0 @error('email') border-red-500 @enderror"
                                    >
                                </div>
                                @error('email')
                                    <p class="text-sm font-bold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Botones -->
                            <div class="flex flex-col items-center gap-4 pt-2">
                                <button type="submit" class="flex h-16 w-full max-w-sm cursor-pointer items-center justify-center rounded-xl bg-Alumco-blue px-8 text-3xl font-bold text-white shadow-[0_6px_0_0_#163a71] transition-all hover:translate-y-[2px] hover:shadow-[0_4px_0_0_#163a71] active:translate-y-[6px] active:shadow-none focus:outline-none">
                                    Enviar enlace
                                </button>

                                <a href="{{ route('login') }}" class="text-lg font-bold text-Alumco-blue transition hover:text-Alumco-coral focus:outline-none">
                                    &larr; Volver al inicio de sesión
                                </a>
                            </div>
                        </form>
                    </div>
@endsection
