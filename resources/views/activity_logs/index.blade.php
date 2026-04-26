@extends('layouts.app')

@section('title', 'Journal d\'Activité — ' . config('app.name'))

@section('content')
<x-page-header title="Journal d'Activité" subtitle="Historique complet des actions effectuées sur le système.">
    <div class="text-muted small">
        <i class="bi bi-info-circle me-1"></i>
        Les actions sont enregistrées en temps réel pour l'audit et la sécurité.
    </div>
</x-page-header>

<div class="ormsa-surface p-4">
    <div class="timeline">
        @forelse($logs as $log)
            <div class="timeline-item d-flex gap-3 mb-4">
                <div class="timeline-icon-wrap">
                    @php
                        $iconClass = match($log->action) {
                            'created' => 'bg-success-subtle text-success bi-plus-lg',
                            'updated' => 'bg-primary-subtle text-primary bi-pencil',
                            'deleted' => 'bg-danger-subtle text-danger bi-trash',
                            default => 'bg-secondary-subtle text-secondary bi-activity'
                        };
                    @endphp
                    <div class="timeline-icon {{ $iconClass }} shadow-sm"></div>
                </div>
                <div class="timeline-content flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 fw-bold text-dark">{{ $log->description }}</h6>
                            <p class="mb-0 text-muted small">
                                Par <strong>{{ $log->user?->name ?? 'Système' }}</strong> 
                                <span class="mx-1">•</span> 
                                IP: <code>{{ $log->ip_address }}</code>
                            </p>
                        </div>
                        <span class="badge bg-light text-dark border fw-normal">
                            {{ $log->created_at->diffForHumans() }}
                        </span>
                    </div>
                    
                    @if($log->properties && count($log->properties) > 0)
                        <div class="mt-2 p-2 bg-light rounded border-start border-3 border-primary small">
                            <details>
                                <summary class="text-primary cursor-pointer" style="list-style: none;">
                                    <i class="bi bi-chevron-right me-1"></i> Voir les détails techniques
                                </summary>
                                <div class="mt-2 font-monospace text-dark">
                                    @foreach($log->properties as $key => $value)
                                        @if($key !== 'updated_at')
                                            <div><span class="text-muted">{{ $key }}:</span> {{ is_array($value) ? json_encode($value) : $value }}</div>
                                        @endif
                                    @endforeach
                                </div>
                            </details>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="ormsa-empty">
                <i class="bi bi-journal-text"></i>
                Aucune activité enregistrée pour le moment.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>

<style>
.timeline-item {
    position: relative;
}
.timeline-icon-wrap {
    position: relative;
    z-index: 2;
}
.timeline-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}
.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 20px;
    top: 40px;
    bottom: -25px;
    width: 2px;
    background: var(--gray-200);
    z-index: 1;
}
.timeline-content {
    background: var(--bg-surface);
    padding: 1rem;
    border-radius: 0.75rem;
    border: 1px solid var(--gray-200);
    transition: transform 0.2s;
}
.timeline-content:hover {
    transform: translateX(5px);
    border-color: var(--c-primary);
}
summary::-webkit-details-marker {
    display: none;
}
</style>
@endsection
