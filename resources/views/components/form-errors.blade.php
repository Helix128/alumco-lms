@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'rounded-2xl border border-red-200 bg-red-50 p-5 text-red-900']) }}
         role="alert"
         aria-live="assertive"
         tabindex="-1"
         data-error-summary>
        <div class="flex gap-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <div>
                <h2 class="font-display font-black">Revisa la información antes de continuar</h2>
                <p class="mt-1 text-sm font-medium">Encontramos {{ $errors->count() }} {{ $errors->count() === 1 ? 'problema' : 'problemas' }}. Tus datos se conservaron.</p>
                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm font-bold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
