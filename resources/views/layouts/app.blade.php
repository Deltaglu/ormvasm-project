<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{-- App CSS (with cache-bust) --}}
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">

    @stack('styles')
</head>
<body class="@auth ormsa-app @else ormsa-auth @endauth">

@auth
{{-- ─── Authenticated shell ─── --}}
<div style="display:flex; min-height:100vh;">

    {{-- Sidebar --}}
    <aside class="ormsa-sidebar">
        {{-- Brand --}}
        <div class="ormsa-brand">
            <div class="d-flex align-items-center gap-3">
                <div class="ormsa-brand-mark">
                    <img src="{{ asset('images/logo.png') }}" alt="ORMVA" style="height:2rem; width:auto;">
                </div>
                <div>
                    <a class="ormsa-brand-title d-block" href="{{ route('dashboard') }}">ORMVASM</a>
                    <span class="ormsa-brand-sub">Recettes & recouvrement</span>
                </div>
            </div>
            @if(session('company_code'))
                <div class="mt-3 pt-2" style="border-top:1px solid rgba(255,255,255,0.08);">
                    <div class="ormsa-brand-sub mb-2">Société active</div>
                    <span class="badge" style="background:rgba(255,255,255,0.15); color:#fff; font-size:.75rem; padding:.35em .65em; font-weight:500; letter-spacing:0.5px;">{{ session('company_code') }}</span>
                </div>
            @endif
        </div>

        {{-- Navigation --}}
        <nav class="ormsa-nav">
            <div class="ormsa-nav-section">Menu principal</div>
            <a class="ormsa-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
            <a class="ormsa-nav-link {{ request()->routeIs('agriculteurs.*') ? 'active' : '' }}" href="{{ route('agriculteurs.index') }}">
                <i class="bi bi-people"></i> Agriculteurs
            </a>
            <a class="ormsa-nav-link {{ request()->routeIs('prestations.*') ? 'active' : '' }}" href="{{ route('prestations.index') }}">
                <i class="bi bi-list-ul"></i> Prestations
            </a>
            <a class="ormsa-nav-link {{ request()->routeIs('titres-recettes.*') ? 'active' : '' }}" href="{{ route('titres-recettes.index') }}">
                <i class="bi bi-receipt"></i> Titres de recette
            </a>
            <a class="ormsa-nav-link {{ request()->routeIs('paiements.*') ? 'active' : '' }}" href="{{ route('paiements.index') }}">
                <i class="bi bi-cash-stack"></i> Paiements
            </a>
            <a class="ormsa-nav-link {{ request()->routeIs('quittances.*') ? 'active' : '' }}" href="{{ route('quittances.index') }}">
                <i class="bi bi-file-earmark-text"></i> Quittances
            </a>

            <div class="ormsa-nav-section">Configuration</div>
            <a class="ormsa-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.edit') }}">
                <i class="bi bi-percent"></i> Pénalités
            </a>
            <a class="ormsa-nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.create') }}">
                <i class="bi bi-building-add"></i> Nouvelle société
            </a>
        </nav>

        {{-- Footer --}}
        <div class="ormsa-sidebar-footer">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-check"></i>
                <span>Version 1.0.0</span>
            </div>
        </div>
    </aside>

    {{-- Main column --}}
    <div style="flex:1; min-width:0; display:flex; flex-direction:column;">

        {{-- Topbar --}}
        <header class="ormsa-topbar">
            <div>
                <div class="ormsa-topbar-title">Espace connecté</div>
                <div class="ormsa-topbar-app">{{ config('app.name') }}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="ormsa-user-pill">
                    <span class="ormsa-user-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span>{{ auth()->user()->name }}</span>
                </div>
                <form method="post" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Déconnexion">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </header>

        {{-- Content --}}
        <main class="ormsa-main">
            <div class="ormsa-content">

                {{-- Flash success --}}
                @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>{{ session('status') }}</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                @endif

                {{-- Validation errors --}}
                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <strong>Veuillez corriger les erreurs suivantes :</strong>
                        </div>
                        <ul class="mb-0 ps-3 small">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</div>

@else
{{-- ─── Guest pages ─── --}}
<main>
    @if(session('status'))
        <div class="container pt-4">
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="container pt-4">
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    @yield('content')
</main>
@endauth

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')

{{-- PDF Preview Modal --}}
<div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="height:85vh; border-radius:var(--r-lg); overflow:hidden; border-color:var(--border);">
            <div class="modal-header py-2 px-3" style="background:var(--gray-50); border-bottom:1px solid var(--border);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-pdf text-danger"></i>
                    <span class="fw-semibold small" id="pdfPreviewTitle">Aperçu PDF</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="pdfDownloadBtn" class="btn btn-sm btn-primary" download>
                        <i class="bi bi-download"></i> Télécharger
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
            </div>
            <div class="modal-body p-0" style="flex:1; overflow:hidden;">
                <iframe id="pdfPreviewFrame" src="" style="width:100%; height:100%; border:none;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('pdfPreviewModal');
    if (!modal) return;
    const iframe = document.getElementById('pdfPreviewFrame');
    const downloadBtn = document.getElementById('pdfDownloadBtn');
    const titleEl = document.getElementById('pdfPreviewTitle');
    const bsModal = new bootstrap.Modal(modal);

    document.addEventListener('click', function (e) {
        const link = e.target.closest('a[href*="/pdf"]');
        if (!link) return;
        e.preventDefault();
        const href = link.getAttribute('href');
        iframe.src = href;
        downloadBtn.href = href.replace('/pdf', '/pdf/download');
        titleEl.textContent = 'Aperçu — ' + href.split('/').pop();
        bsModal.show();
    });

    modal.addEventListener('hidden.bs.modal', function () { iframe.src = ''; });
});
</script>
</body>
</html>