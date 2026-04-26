@extends('layouts.app')

@section('title', 'Prestation '.$prestation->code)

@section('content')
<x-page-header title="{{ $prestation->libelle }}" subtitle="Détail de la prestation {{ $prestation->code }}.">
    <a href="{{ route('prestations.edit', $prestation) }}" class="btn btn-outline-primary">
        <i class="bi bi-pencil"></i>
    </a>
    <a href="{{ route('prestations.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-list"></i>
    </a>
</x-page-header>

<div class="ormsa-surface">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3 text-secondary small">Code</dt>
            <dd class="col-sm-9"><code>{{ $prestation->code }}</code></dd>
            <dt class="col-sm-3 text-secondary small">Tarif</dt>
            <dd class="col-sm-9 fw-semibold">{{ number_format($prestation->tarif, 2, ',', ' ') }} DH</dd>
            <dt class="col-sm-3 text-secondary small">Unité</dt>
            <dd class="col-sm-9">{{ $prestation->unite ?: '—' }}</dd>
            <dt class="col-sm-3 text-secondary small">Description</dt>
            <dd class="col-sm-9">{{ $prestation->description ?: '—' }}</dd>
        </dl>
    </div>
</div>
@endsection
