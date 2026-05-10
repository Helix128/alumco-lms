<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-black text-Alumco-blue uppercase tracking-[0.2em]">Feedback de la capacitación</h3>
            <p class="text-[10px] text-Alumco-gray/65 font-bold uppercase mt-1">Satisfacción y comentarios</p>
        </div>
        <span class="rounded-full bg-Alumco-green/25 px-3 py-1 text-xs font-black text-Alumco-green-accessible">
            {{ $promedio ? number_format($promedio, 1) : '—' }}/5
        </span>
    </div>

    <div class="space-y-3">
        @forelse ($feedbacks as $feedback)
            <article wire:key="feedback-curso-{{ $feedback->id }}" class="rounded-2xl border border-gray-100 bg-white p-4">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-[10px] font-black uppercase tracking-widest text-Alumco-blue/45">
                        {{ $feedback->categoria }} · {{ $feedback->created_at->format('d/m/Y') }}
                    </p>
                    <span class="text-xs font-black text-Alumco-green-accessible">{{ $feedback->rating }}/5</span>
                </div>
                @if ($feedback->mensaje)
                    <p class="mt-2 text-sm font-semibold leading-relaxed text-Alumco-gray/75">{{ $feedback->mensaje }}</p>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-200 px-4 py-8 text-center">
                <p class="text-sm font-bold text-Alumco-gray/45">Sin feedback registrado.</p>
            </div>
        @endforelse
    </div>
</div>
