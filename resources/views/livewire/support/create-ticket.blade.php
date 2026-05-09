<div class="{{ $embedded ? 'worker-card p-5 lg:p-6' : '' }}">
    @if ($statusMessage)
        <div class="mb-5 rounded-2xl border border-Alumco-green-accessible/20 bg-Alumco-green-accessible/5 px-4 py-3 text-sm font-bold text-Alumco-green-accessible">
            {{ $statusMessage }}
        </div>
    @endif

    <form wire:submit="submit" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
                <label for="support-contact-name" class="text-sm font-black text-Alumco-gray">Nombre</label>
                <input id="support-contact-name" type="text" wire:model="contact_name" @auth readonly @endauth
                       class="worker-focus w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-Alumco-gray outline-none focus:border-Alumco-blue disabled:bg-gray-50">
                @error('contact_name') <p class="text-sm font-bold text-Alumco-coral-accessible">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-2">
                <label for="support-contact-email" class="text-sm font-black text-Alumco-gray">Correo</label>
                <input id="support-contact-email" type="email" wire:model="contact_email" @auth readonly @endauth
                       class="worker-focus w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-Alumco-gray outline-none focus:border-Alumco-blue disabled:bg-gray-50">
                @error('contact_email') <p class="text-sm font-bold text-Alumco-coral-accessible">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-[0.8fr_1.2fr]">
            <div class="space-y-2">
                <label for="support-category" class="text-sm font-black text-Alumco-gray">Categoría</label>
                <select id="support-category" wire:model="category"
                        class="worker-focus w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-Alumco-gray outline-none focus:border-Alumco-blue">
                    @foreach ($categories as $categoryOption)
                        <option value="{{ $categoryOption }}">{{ \App\Models\SupportTicket::categoryLabel($categoryOption) }}</option>
                    @endforeach
                </select>
                @error('category') <p class="text-sm font-bold text-Alumco-coral-accessible">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-2">
                <label for="support-subject" class="text-sm font-black text-Alumco-gray">Asunto</label>
                <input id="support-subject" type="text" wire:model="subject" maxlength="160"
                       class="worker-focus w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-Alumco-gray outline-none focus:border-Alumco-blue">
                @error('subject') <p class="text-sm font-bold text-Alumco-coral-accessible">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="space-y-2">
            <label for="support-description" class="text-sm font-black text-Alumco-gray">Descripción</label>
            <textarea id="support-description" wire:model="description" rows="6" maxlength="5000"
                      class="worker-focus w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold leading-relaxed text-Alumco-gray outline-none focus:border-Alumco-blue"></textarea>
            @error('description') <p class="text-sm font-bold text-Alumco-coral-accessible">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-2">
            <label for="support-attachments" class="text-sm font-black text-Alumco-gray">Capturas opcionales</label>
            <input id="support-attachments" type="file" wire:model="attachments" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                   class="worker-focus block w-full rounded-2xl border border-dashed border-Alumco-blue/25 bg-white px-4 py-4 text-sm font-bold text-Alumco-gray file:mr-4 file:rounded-full file:border-0 file:bg-Alumco-blue file:px-4 file:py-2 file:text-sm file:font-black file:text-white">
            <p class="text-xs font-semibold text-Alumco-gray/55">Hasta 3 capturas. JPG, PNG o WEBP. Máximo 4 MB cada una.</p>
            @error('attachments') <p class="text-sm font-bold text-Alumco-coral-accessible">{{ $message }}</p> @enderror
            @error('attachments.*') <p class="text-sm font-bold text-Alumco-coral-accessible">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="worker-focus rounded-full bg-Alumco-blue px-6 py-3 text-sm font-black text-white shadow-lg shadow-Alumco-blue/15 data-loading:opacity-70">
                Enviar ticket
            </button>
        </div>
    </form>
</div>
