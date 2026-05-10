@section('title', 'Salud LMS')
@section('header_title', 'Salud LMS')

<div wire:poll.30s="refresh" class="min-h-screen bg-gray-50">
    @livewire('developer.salud-lms.status-bar')

    <div class="mx-auto max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <h2 class="admin-page-title">Salud operacional del LMS</h2>
                <p class="admin-page-subtitle">Estado técnico accionable para colas, servicios, logs, configuración, base de datos y caché.</p>
            </div>

            @php
                $tabs = [
                    'resumen' => 'Resumen',
                    'jobs' => 'Jobs',
                    'logs' => 'Logs',
                    'datos' => 'Datos y performance',
                ];
            @endphp

            <nav class="flex overflow-x-auto rounded-2xl border border-white/80 bg-white p-1 shadow-sm" aria-label="Vistas de salud LMS">
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('dev.salud-lms', $key === 'resumen' ? [] : ['vista' => $key]) }}"
                       wire:navigate
                       class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-black transition {{ $view === $key ? 'bg-Alumco-blue text-white shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-Alumco-blue' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>

        @if ($view === 'resumen')
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <div class="xl:col-span-8">
                    @livewire('developer.salud-lms.services-health-panel')
                </div>
                <div class="xl:col-span-4">
                    @livewire('developer.salud-lms.quick-actions-panel')
                </div>
            </div>

            @livewire('developer.salud-lms.config-alerts-panel')
        @elseif ($view === 'jobs')
            @livewire('developer.salud-lms.jobs-panel')
        @elseif ($view === 'logs')
            @livewire('developer.salud-lms.error-logs-panel')
        @elseif ($view === 'datos')
            <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
                @livewire('developer.salud-lms.database-stats-panel')
                @livewire('developer.salud-lms.cache-stats-panel')
            </div>
        @endif
    </div>
</div>
