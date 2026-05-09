<div class="h-[calc(100vh-8rem)] flex flex-col gap-6">
    {{-- Header & Stats --}}
    <div class="shrink-0 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="admin-page-title">Centro de Soporte</h2>
            <p class="admin-page-subtitle text-xs">Gestión centralizada de incidencias y asistencia técnica.</p>
        </div>
        <div class="flex gap-3">
            <article class="admin-surface flex items-center gap-3 px-4 py-2">
                <div class="h-2 w-2 rounded-full bg-Alumco-coral-accessible animate-pulse"></div>
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-Alumco-blue/40 leading-none">Críticos</p>
                    <p class="mt-0.5 font-display text-lg font-black text-Alumco-blue leading-none">{{ $counters['critical'] }}</p>
                </div>
            </article>
            <article class="admin-surface flex items-center gap-3 px-4 py-2">
                <div class="h-2 w-2 rounded-full bg-Alumco-yellow"></div>
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-Alumco-blue/40 leading-none">Pendientes</p>
                    <p class="mt-0.5 font-display text-lg font-black text-Alumco-blue leading-none">{{ $counters['new'] + $counters['waiting'] }}</p>
                </div>
            </article>
        </div>
    </div>

    {{-- Main Container --}}
    <section class="min-h-0 flex-1 grid gap-6 xl:grid-cols-[400px_1fr]">
        
        {{-- List Column --}}
        <div class="flex flex-col min-h-0 admin-surface overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50/50 space-y-3">
                <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar por asunto, id o usuario..."
                       class="admin-toolbar-input worker-focus w-full border border-gray-200 bg-white px-4 py-2 text-xs font-bold text-Alumco-gray outline-none focus:border-Alumco-blue">
                
                <div class="grid grid-cols-2 gap-2">
                    <select wire:model.live="status" class="admin-toolbar-input w-full border border-gray-200 bg-white px-2 py-1.5 text-[11px] font-bold text-Alumco-gray outline-none">
                        <option value="">Estados</option>
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption }}">{{ \App\Models\SupportTicket::statusLabel($statusOption) }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="assigned" class="admin-toolbar-input w-full border border-gray-200 bg-white px-2 py-1.5 text-[11px] font-bold text-Alumco-gray outline-none">
                        <option value="">Asignación</option>
                        <option value="mine">Míos</option>
                        <option value="unassigned">Sin asignar</option>
                    </select>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-3 space-y-2 scrollbar-thin">
                @forelse ($tickets as $ticket)
                    @php
                        $statusColors = match($ticket->status) {
                            \App\Models\SupportTicket::StatusNew => 'bg-Alumco-blue/10 text-Alumco-blue border-Alumco-blue/20',
                            \App\Models\SupportTicket::StatusInReview => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                            \App\Models\SupportTicket::StatusWaitingUser => 'bg-Alumco-yellow/10 text-amber-700 border-Alumco-yellow/20',
                            \App\Models\SupportTicket::StatusResolved => 'bg-Alumco-green-accessible/10 text-Alumco-green-accessible border-Alumco-green-accessible/20',
                            \App\Models\SupportTicket::StatusClosed => 'bg-gray-100 text-gray-500 border-gray-200',
                            default => 'bg-gray-50 text-gray-600 border-gray-100',
                        };
                        $priorityColors = match($ticket->priority) {
                            \App\Models\SupportTicket::PriorityCritical => 'text-Alumco-coral-accessible',
                            \App\Models\SupportTicket::PriorityHigh => 'text-orange-600',
                            default => 'text-Alumco-blue/60',
                        };
                    @endphp
                    <button type="button" wire:key="support-ticket-row-{{ $ticket->id }}" wire:click="selectTicket({{ $ticket->id }})"
                            class="group relative w-full rounded-xl border p-3 text-left transition-all {{ $selectedTicketId === $ticket->id ? 'border-Alumco-blue bg-Alumco-blue/[0.03] shadow-sm' : 'border-gray-100 bg-white hover:border-gray-300' }}">
                        
                        @if($selectedTicketId === $ticket->id)
                            <div class="absolute inset-y-2 left-0 w-1 bg-Alumco-blue rounded-r-full"></div>
                        @endif

                        <div class="flex items-start justify-between gap-2">
                            <span class="text-[10px] font-black text-Alumco-blue/40 tracking-tighter">#{{ $ticket->id }}</span>
                            <span class="text-[9px] font-black uppercase {{ $priorityColors }}">
                                {{ \App\Models\SupportTicket::priorityLabel($ticket->priority) }}
                            </span>
                        </div>
                        
                        <h4 class="mt-1 line-clamp-1 text-xs font-black text-Alumco-gray group-hover:text-Alumco-blue transition-colors">
                            {{ $ticket->subject }}
                        </h4>
                        
                        <p class="mt-1 text-[10px] font-semibold text-Alumco-gray/50 italic">
                            {{ $ticket->requesterName() }}
                        </p>

                        <div class="mt-3 flex items-center justify-between">
                            <span class="rounded-full border px-2 py-0.5 text-[9px] font-black {{ $statusColors }}">
                                {{ \App\Models\SupportTicket::statusLabel($ticket->status) }}
                            </span>
                            <span class="text-[9px] font-bold text-Alumco-gray/30">
                                {{ $ticket->last_activity_at?->diffForHumans() }}
                            </span>
                        </div>
                    </button>
                @empty
                    <div class="py-12 text-center">
                        <p class="text-xs font-bold text-Alumco-gray/40">No hay tickets que coincidan.</p>
                    </div>
                @endforelse
            </div>

            <div class="p-3 border-t border-gray-100 bg-gray-50/30">
                {{ $tickets->links(data: ['compact' => true]) }}
            </div>
        </div>

        {{-- Detail Column --}}
        <div class="flex flex-col min-h-0 admin-surface overflow-hidden">
            @if ($selectedTicket)
                {{-- Detail Header --}}
                <header class="shrink-0 p-5 border-b border-gray-100 bg-white">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="rounded-md bg-Alumco-blue text-white px-1.5 py-0.5 text-[10px] font-black italic">TICKET-{{ $selectedTicket->id }}</span>
                                <span class="text-[10px] font-black text-Alumco-gray/40 uppercase tracking-widest">{{ \App\Models\SupportTicket::categoryLabel($selectedTicket->category) }}</span>
                            </div>
                            <h3 class="mt-2 font-display text-xl font-black text-Alumco-gray truncate" title="{{ $selectedTicket->subject }}">{{ $selectedTicket->subject }}</h3>
                            <div class="mt-1 flex items-center gap-2 text-xs font-semibold text-Alumco-gray/50">
                                <span>{{ $selectedTicket->requesterName() }}</span>
                                <span>•</span>
                                <a href="mailto:{{ $selectedTicket->requesterEmail() }}" class="text-Alumco-blue hover:underline">{{ $selectedTicket->requesterEmail() ?? 'Sin correo' }}</a>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(!$selectedTicket->assigned_to_id || $selectedTicket->assigned_to_id !== auth()->id())
                                <button type="button" wire:click="assignToMe" class="worker-focus rounded-full border border-Alumco-blue bg-Alumco-blue/5 px-4 py-1.5 text-[11px] font-black text-Alumco-blue hover:bg-Alumco-blue hover:text-white transition-all">Asignarme</button>
                            @endif
                            <div class="h-8 w-px bg-gray-100 mx-1"></div>
                            <select wire:model="newStatus" wire:change="updateTicket" class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-bold text-Alumco-gray outline-none focus:border-Alumco-blue">
                                @foreach ($statuses as $statusOption)
                                    <option value="{{ $statusOption }}">{{ \App\Models\SupportTicket::statusLabel($statusOption) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </header>

                {{-- Conversation & Info Scrollable --}}
                <div class="flex-1 overflow-y-auto p-6 space-y-8 scrollbar-thin bg-gray-50/30">
                    
                    {{-- Original Ticket Description --}}
                    <div class="relative pl-6 border-l-2 border-Alumco-blue/20">
                        <div class="absolute -left-[5px] top-0 h-2 w-2 rounded-full bg-Alumco-blue"></div>
                        <p class="text-[10px] font-black uppercase text-Alumco-blue/50 tracking-widest mb-2">Requerimiento Inicial</p>
                        <div class="rounded-2xl bg-white border border-gray-100 p-5 shadow-sm">
                            <p class="whitespace-pre-line text-sm font-semibold leading-relaxed text-Alumco-gray/80">{{ $selectedTicket->description }}</p>
                            @if ($selectedTicket->attachments->whereNull('support_ticket_message_id')->isNotEmpty())
                                <div class="mt-4 flex flex-wrap gap-2 pt-4 border-t border-gray-50">
                                    @foreach ($selectedTicket->attachments->whereNull('support_ticket_message_id') as $attachment)
                                        <a href="{{ route('support.attachments.download', $attachment) }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-50 border border-gray-100 px-3 py-1.5 text-[10px] font-black text-Alumco-blue hover:bg-Alumco-blue/5">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            {{ $attachment->original_name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        
                        @if ($selectedTicket->technical_context)
                            <div class="mt-4 flex gap-4 text-[10px] font-bold text-Alumco-gray/40">
                                <span class="flex items-center gap-1.5"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg> {{ $selectedTicket->technical_context['url'] ?? 'Sin URL' }}</span>
                                <span class="flex items-center gap-1.5"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> {{ Str::limit($selectedTicket->technical_context['user_agent'] ?? 'Sin Agente', 50) }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Message Thread --}}
                    <div class="space-y-6">
                        @foreach ($selectedTicket->messages as $message)
                            <article wire:key="support-message-{{ $message->id }}" class="flex {{ $message->author_user_id === $selectedTicket->requester_user_id ? 'justify-start' : 'justify-end' }}">
                                <div class="max-w-[85%] min-w-[200px]">
                                    <div class="flex items-center gap-2 mb-1.5 {{ $message->author_user_id === $selectedTicket->requester_user_id ? 'flex-row' : 'flex-row-reverse' }}">
                                        <span class="text-[10px] font-black text-Alumco-gray italic">{{ $message->author?->name ?? 'Sistema' }}</span>
                                        <span class="text-[9px] font-bold text-Alumco-gray/40">{{ $message->created_at->diffForHumans() }}</span>
                                        @if($message->is_internal)
                                            <span class="rounded bg-Alumco-yellow/20 px-1 py-0.5 text-[8px] font-black text-amber-700 uppercase tracking-tighter">Nota Interna</span>
                                        @endif
                                    </div>
                                    <div class="rounded-2xl p-4 shadow-sm border {{ $message->is_internal ? 'bg-amber-50 border-amber-100' : ($message->author_user_id === $selectedTicket->requester_user_id ? 'bg-white border-gray-100' : 'bg-Alumco-blue text-white border-Alumco-blue') }}">
                                        <p class="whitespace-pre-line text-sm font-semibold leading-relaxed {{ $message->author_user_id !== $selectedTicket->requester_user_id && !$message->is_internal ? 'text-white' : 'text-Alumco-gray/80' }}">
                                            {{ $message->body }}
                                        </p>
                                        
                                        @if ($message->attachments->isNotEmpty())
                                            <div class="mt-3 flex flex-wrap gap-2 pt-3 border-t {{ $message->author_user_id !== $selectedTicket->requester_user_id && !$message->is_internal ? 'border-white/20' : 'border-gray-50' }}">
                                                @foreach ($message->attachments as $attachment)
                                                    <a href="{{ route('support.attachments.download', $attachment) }}" class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-[9px] font-black {{ $message->author_user_id !== $selectedTicket->requester_user_id && !$message->is_internal ? 'bg-white/10 text-white hover:bg-white/20' : 'bg-gray-50 text-Alumco-blue hover:bg-Alumco-blue/5' }}">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                        Imagen
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                {{-- Sticky Reply Box --}}
                <footer class="shrink-0 p-4 border-t border-gray-100 bg-white">
                    <form wire:submit="reply" class="space-y-3">
                        {{-- Tabs --}}
                        <div class="flex items-center gap-1 p-1 bg-gray-50 rounded-xl w-fit">
                            <button type="button" wire:click="$set('isInternalReply', false)" class="px-4 py-1.5 text-[11px] font-black rounded-lg transition-all {{ !$isInternalReply ? 'bg-white shadow-sm text-Alumco-blue' : 'text-Alumco-gray/50 hover:text-Alumco-gray' }}">
                                Respuesta Pública
                            </button>
                            <button type="button" wire:click="$set('isInternalReply', true)" class="px-4 py-1.5 text-[11px] font-black rounded-lg transition-all {{ $isInternalReply ? 'bg-amber-100 text-amber-700 shadow-sm' : 'text-Alumco-gray/50 hover:text-Alumco-gray' }}">
                                Nota Interna (Privada)
                            </button>
                        </div>

                        <div class="relative group">
                            <textarea wire:model="replyBody" rows="3" 
                                class="worker-focus w-full rounded-2xl border bg-white px-4 py-3 text-sm font-semibold text-Alumco-gray outline-none transition-all {{ $isInternalReply ? 'border-amber-200 focus:border-amber-400 bg-amber-50/30' : 'border-gray-200 focus:border-Alumco-blue' }}" 
                                placeholder="{{ $isInternalReply ? 'Escribe una nota que solo el equipo podrá ver...' : 'Escribe tu respuesta al usuario...' }}"></textarea>
                            
                            <div class="absolute bottom-3 right-3 flex items-center gap-2">
                                <label class="cursor-pointer group/file">
                                    <input type="file" wire:model="replyAttachments" multiple class="hidden">
                                    <div class="p-2 rounded-full bg-gray-50 group-hover/file:bg-Alumco-blue/10 text-Alumco-gray/40 group-hover/file:text-Alumco-blue transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    </div>
                                </label>
                            </div>
                        </div>

                        @error('replyBody') <p class="text-[10px] font-bold text-Alumco-coral-accessible">{{ $message }}</p> @enderror
                        @if($replyAttachments)
                            <div class="flex flex-wrap gap-2">
                                @foreach($replyAttachments as $index => $file)
                                    <div class="text-[9px] font-black bg-Alumco-blue/5 text-Alumco-blue px-2 py-1 rounded-md flex items-center gap-2">
                                        <span class="truncate max-w-[100px]">{{ $file->getClientOriginalName() }}</span>
                                        <button type="button" wire:click="$pull('replyAttachments.{{ $index }}')" class="text-Alumco-coral-accessible hover:scale-110">×</button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <button type="submit" class="worker-focus rounded-full px-6 py-2.5 text-xs font-black text-white transition-all shadow-sm hover:shadow-md {{ $isInternalReply ? 'bg-amber-600' : 'bg-Alumco-blue' }}">
                                    {{ $isInternalReply ? 'Guardar Nota' : 'Enviar Respuesta' }}
                                </button>
                                
                                @if(!$isInternalReply)
                                    <button type="button" wire:click="replyAndResolve" class="worker-focus rounded-full border border-Alumco-green-accessible px-4 py-2.5 text-xs font-black text-Alumco-green-accessible hover:bg-Alumco-green-accessible hover:text-white transition-all">
                                        Enviar y Resolver
                                    </button>
                                @endif
                            </div>

                            @if($selectedTicket->status !== \App\Models\SupportTicket::StatusClosed)
                                <button type="button" wire:click="closeTicket" class="text-xs font-black text-Alumco-coral-accessible/60 hover:text-Alumco-coral-accessible transition-colors">
                                    Cerrar Ticket
                                </button>
                            @endif
                        </div>
                    </form>
                </footer>
            @else
                <div class="flex-1 flex flex-col items-center justify-center p-12 text-center opacity-40">
                    <div class="h-20 w-20 rounded-full border-4 border-dashed border-Alumco-blue/20 flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-Alumco-blue/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </div>
                    <h3 class="font-display text-lg font-black text-Alumco-gray">Bandeja de Entrada</h3>
                    <p class="mt-1 text-sm font-semibold max-w-xs">Selecciona una incidencia de la lista para ver el historial y tomar acciones.</p>
                </div>
            @endif
        </div>
    </section>
</div>
