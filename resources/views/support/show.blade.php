@extends('layouts.user')

@section('title', 'Ticket de soporte')

@section('content')
    <div class="space-y-6">
        <a href="{{ route('support.index') }}" wire:navigate.hover class="worker-focus inline-flex items-center text-sm font-black text-Alumco-blue hover:text-Alumco-coral">Volver a soporte</a>

        <article class="worker-card p-5 lg:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-Alumco-blue/55">Ticket #{{ $ticket->id }}</p>
                    <h1 class="mt-2 font-display text-2xl font-black text-Alumco-gray">{{ $ticket->subject }}</h1>
                    <p class="mt-1 text-sm font-semibold text-Alumco-gray/55">{{ \App\Models\SupportTicket::categoryLabel($ticket->category) }} · {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <span class="rounded-full bg-Alumco-blue/10 px-3 py-1 text-xs font-black text-Alumco-blue">{{ \App\Models\SupportTicket::statusLabel($ticket->status) }}</span>
            </div>

            <div class="mt-6 rounded-2xl border border-gray-100 bg-white p-4">
                <p class="whitespace-pre-line text-sm font-semibold leading-relaxed text-Alumco-gray/75">{{ $ticket->description }}</p>
            </div>

            @if ($ticket->attachments->isNotEmpty())
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($ticket->attachments as $attachment)
                        <a href="{{ route('support.attachments.download', $attachment) }}" class="worker-focus rounded-full border border-Alumco-blue/15 px-3 py-1.5 text-xs font-black text-Alumco-blue hover:bg-Alumco-blue/5">{{ $attachment->original_name }}</a>
                    @endforeach
                </div>
            @endif
        </article>

        <section class="worker-card p-5 lg:p-6">
            <h2 class="font-display text-xl font-black text-Alumco-gray">Conversación</h2>
            <div class="mt-5 space-y-4">
                @forelse ($ticket->messages->where('is_internal', false) as $message)
                    <article class="rounded-2xl border border-gray-100 bg-white p-4">
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-black text-Alumco-gray">{{ $message->author?->name ?? 'Soporte Alumco' }}</p>
                            <p class="text-xs font-bold text-Alumco-gray/45">{{ $message->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <p class="whitespace-pre-line text-sm font-semibold leading-relaxed text-Alumco-gray/75">{{ $message->body }}</p>
                    </article>
                @empty
                    <p class="rounded-2xl border border-dashed border-gray-200 px-4 py-8 text-center text-sm font-bold text-Alumco-gray/45">El equipo técnico aún no ha respondido.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
