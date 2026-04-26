@extends('layouts.app')

@section('title', 'Campagnes')

@section('content')
<x-page-header title="Campagnes agricoles" subtitle="Registre des campagnes par année.">
    <a href="{{ route('campagnes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i>
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-table-wrap">
    <div>
        <table class="table table-hover mb-0 align-middle">
            <thead>
            <tr>
                <th>Année</th>
                <th>Description</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($campagnes as $c)
                <tr>
                    <td class="fw-semibold">{{ $c->annee }}</td>
                    <td class="text-secondary">{{ \Illuminate\Support\Str::limit($c->description ?? '—', 80) }}</td>
                    <td class="text-end">
                        <div class="ormsa-actions">
                            <a href="{{ route('campagnes.show', $c) }}" class="btn btn-outline-secondary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('campagnes.edit', $c) }}" class="btn btn-outline-primary" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('campagnes.destroy', $c) }}" method="post" class="d-inline" onsubmit="return confirm('Supprimer cette campagne ?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger" type="submit" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($campagnes->hasPages())
        <div class="card-body border-top ormsa-pagination">{{ $campagnes->links() }}</div>
    @endif
</div>
@endsection
