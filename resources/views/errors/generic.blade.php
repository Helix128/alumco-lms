@if (view()->exists('layouts.app'))
    <x-layouts.app :title="'Error '.$statusCode">
        <main class="mx-auto flex min-h-screen w-full max-w-3xl items-center px-6 py-16">
            <section class="w-full rounded-lg border border-Alumco-blue/20 bg-white p-8 shadow-sm">
                <h1 class="text-2xl font-bold text-Alumco-blue">Tuvimos un problema</h1>
                <p class="mt-3 text-Alumco-gray">
                    No pudimos completar tu solicitud en este momento. Inténtalo nuevamente en unos minutos.
                </p>
                <dl class="mt-6 space-y-2 text-sm text-Alumco-gray">
                    <div>
                        <dt class="font-semibold">Código de error</dt>
                        <dd>{{ $statusCode }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold">Código de seguimiento</dt>
                        <dd>{{ $traceId }}</dd>
                    </div>
                </dl>
            </section>
        </main>
    </x-layouts.app>
@else
    <!DOCTYPE html>
    <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error {{ $statusCode }}</title>
        </head>
        <body style="font-family: Arial, sans-serif; margin: 0; padding: 2rem; color: #1f2937;">
            <main style="max-width: 700px; margin: 0 auto;">
                <h1>Tuvimos un problema</h1>
                <p>No pudimos completar tu solicitud en este momento. Inténtalo nuevamente en unos minutos.</p>
                <p><strong>Código de error:</strong> {{ $statusCode }}</p>
                <p><strong>Código de seguimiento:</strong> {{ $traceId }}</p>
            </main>
        </body>
    </html>
@endif
