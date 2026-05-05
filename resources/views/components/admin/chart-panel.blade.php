@props([
    'title',
    'description' => null,
    'badge' => null,
    'accent' => '32 80 153',
    'canvasClass' => 'chart-panel__canvas',
])

<section {{ $attributes->merge(['class' => 'chart-panel p-6']) }} style="--chart-accent: {{ $accent }};">
    <div class="chart-panel__header">
        <div>
            <span class="chart-panel__eyebrow">Dashboard Analítico</span>
            <h3 class="chart-panel__title">{{ $title }}</h3>
            @if($description)
                <p class="chart-panel__description">{{ $description }}</p>
            @endif
        </div>

        @if($badge)
            <span class="chart-panel__badge">{{ $badge }}</span>
        @endif
    </div>

    <div class="{{ $canvasClass }}">
        {{ $slot }}
    </div>
</section>
