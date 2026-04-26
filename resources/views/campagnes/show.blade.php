@extends('layouts.app')

@section('title', 'Campagne '.$campagne->annee)

@section('content')
<x-page-header title="Campagne {{ $campagne->annee }}" subtitle="Paiements rattachés à cette campagne.">
    <a href="{{ route('campagnes.edit', $campagne) }}" class="btn btn-outline-primary">
        <i class="bi bi-pencil"></i>
    </a>
    <a href="{{ route('campagnes.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-list"></i>
    </a>
</x-page-header>

<p class="text-secondary mb-4">{{ $campagne->description ?? 'Pas de description.' }}</p>

<div class="ormsa-surface ormsa-table-wrap">
    <div class="ormsa-surface-header">Paiements liés</div>
    <div>
        <table class="table table-hover mb-0 align-middle">
            <thead>
            <tr>
                <th>Date</th>
                <th>Agriculteur</th>
                <th>Montant</th>
                <th>Type</th>
                <th>Quittance</th>
            </tr>
            </thead>
            <tbody>
            @forelse($campagne->paiements as $p)
                <tr>
                    <td>{{ $p->date_paiement->format('d/m/Y') }}</td>
                    <td>{{ $p->agriculteur->prenom }} {{ $p->agriculteur->nom }}</td>
                    <td>{{ number_format($p->montant, 2, ',', ' ') }} DH</td>
                    <td><span class="badge text-bg-light text-dark border">{{ $p->type_paiement }}</span></td>
                    <td>
                        @if($p->quittance)
                            <a href="{{ route('quittances.pdf', $p->quittance) }}" class="btn btn-primary">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                        @else
                            <span class="text-secondary">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-secondary py-4">Aucun paiement pour cette campagne.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
