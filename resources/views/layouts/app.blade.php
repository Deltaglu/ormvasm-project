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

    {{-- Dark Mode Prevention Script --}}
    <script>
        (function() {
            const savedTheme = localStorage.getItem('ormvasm-theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    {{-- Third Party CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    @stack('styles')
    <style>
        /* ================================================================
           ORANGE FRUIT LOADER (Agriculture)
           ================================================================ */
        .ormsa-loader-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: transparent !important;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            pointer-events: none;
        }
        .ormsa-loader-overlay.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .orange-wrapper {
            position: relative;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .leaf-loader {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #4ade80 0%, #16a34a 100%);
            border-radius: 2px 50px; /* Leaf shape */
            position: relative;
            box-shadow: 0 4px 15px rgba(22, 163, 74, 0.3);
            animation: leaf-float 2s infinite ease-in-out;
        }

        .leaf-loader::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to bottom right, transparent 48%, rgba(255,255,255,0.3) 50%, transparent 52%);
        }

        /* ================================================================
           DATATABLES PREMIUM STYLING
           ================================================================ */
        .dataTables_wrapper .dataTables_filter {
            float: none;
            text-align: left;
            margin-bottom: 1.5rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            background-color: var(--bg-surface);
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            width: 320px !important;
            font-size: 0.9rem;
            color: var(--gray-800);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2394a3b8' class='bi bi-search' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 0.8rem center;
            outline: none;
        }

        [data-theme="dark"] .dataTables_wrapper .dataTables_filter input {
            background-color: var(--gray-100);
            border-color: var(--gray-200);
            color: var(--gray-700);
        }

        .dataTables_wrapper .dataTables_filter label {
            font-size: 0; /* Hide the "Search:" text */
        }

        table.dataTable thead .sorting,
        table.dataTable thead .sorting_asc,
        table.dataTable thead .sorting_desc {
            cursor: pointer;
            position: relative;
            padding-right: 30px !important;
        }

        table.dataTable thead .sorting:after,
        table.dataTable thead .sorting_asc:after,
        table.dataTable thead .sorting_desc:after {
            position: absolute;
            right: 10px;
            font-family: "bootstrap-icons";
            opacity: 0.3;
            font-style: normal;
        }

        table.dataTable thead .sorting:after { content: "\F145"; } /* bi-arrow-down-up */
        table.dataTable thead .sorting_asc:after { content: "\F124"; opacity: 1; color: var(--c-primary); } /* bi-arrow-up */
        table.dataTable thead .sorting_desc:after { content: "\F128"; opacity: 1; color: var(--c-primary); } /* bi-arrow-down */

        .dataTables_info, .dataTables_paginate {
            font-size: 0.85rem;
            color: var(--gray-500);
        }

        @keyframes leaf-float {
            0% { transform: translateY(0) rotate(-10deg) scale(1); }
            50% { transform: translateY(-15px) rotate(15deg) scale(1.1); }
            100% { transform: translateY(0) rotate(-10deg) scale(1); }
        }
    </style>
</head>
<body class="@auth ormsa-app @else ormsa-auth @endauth">

{{-- Leaf Loader (Agriculture) --}}
<div id="pageLoader" class="ormsa-loader-overlay">
    <div class="loader-inner">
        <div class="leaf-loader"></div>
    </div>
</div>

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
        </div>

        {{-- Navigation --}}
        <nav class="ormsa-nav">
            <div class="ormsa-nav-section">Menu principal</div>
            <a class="ormsa-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
            <a class="ormsa-nav-link {{ request()->routeIs('agriculteurs.*') ? 'active' : '' }}" href="{{ route('agriculteurs.index') }}">
                <i class="bi bi-people"></i> Clients
            </a>
            <a href="{{ route('prestations.index') }}" class="ormsa-nav-link {{ request()->routeIs('prestations.*') ? 'active' : '' }}">
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

            <div class="ormsa-nav-section">Administration & Sécurité</div>
            <a href="{{ route('activity-logs.index') }}" class="ormsa-nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i> Journal d'Activité
            </a>
            <a href="{{ route('trash.index') }}" class="ormsa-nav-link {{ request()->routeIs('trash.*') ? 'active' : '' }}">
                <i class="bi bi-trash"></i> Corbeille
            </a>

            <div class="ormsa-nav-section">Configuration</div>
            <a class="ormsa-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.edit') }}">
                <i class="bi bi-percent"></i> Pénalités
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
                <button type="button" id="themeToggle" class="btn btn-sm btn-outline-secondary px-2" title="Changer de thème">
                    <i class="bi bi-moon-stars d-none-dark"></i>
                    <i class="bi bi-sun d-none-light"></i>
                </button>
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

        <!-- Universal PDF Preview Modal -->
    <div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="height: 90vh;">
            <div class="modal-content h-100 border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden;">
                <div class="modal-header border-0 bg-dark text-white py-2 px-3">
                    <h6 class="modal-title mb-0 d-flex align-items-center">
                        <i class="bi bi-file-earmark-pdf me-2 text-danger"></i> Aperçu du document
                    </h6>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 bg-secondary">
                    <iframe id="pdfPreviewFrame" src="" width="100%" height="100%" style="border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Floating Chat -->

        {{-- Content --}}
        <main class="ormsa-main">
            <div class="ormsa-content">

                {{-- Flash success --}}
                @if(session('status'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: "{{ session('status') }}",
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        });
                    </script>
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

{{-- Third Party JS --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Global Flatpickr init
    flatpickr(".datepicker", {
        locale: "fr",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        allowInput: true
    });

    // Global Choices.js init
    const selects = document.querySelectorAll('.searchable-select');
    selects.forEach(select => {
        new Choices(select, {
            searchEnabled: true,
            itemSelectText: '',
            noResultsText: 'Aucun résultat trouvé',
            noChoicesText: 'Pas de choix disponibles',
            placeholderValue: 'Sélectionnez une option',
        });
    });

    // Global Delete Confirmation
    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.btn-delete-confirm');
        if (deleteBtn) {
            e.preventDefault();
            const form = deleteBtn.closest('form');
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "L'élément sera déplacé dans la corbeille et pourra être restauré.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Oui, supprimer !',
                cancelButtonText: 'Annuler',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });

    // Force Delete Confirmation - Permanent Delete (from trash)
    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.btn-force-delete-confirm');
        if (deleteBtn) {
            e.preventDefault();
            const form = deleteBtn.closest('form');
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cette action est irréversible. L'élément sera définitivement supprimé.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Oui, supprimer définitivement',
                cancelButtonText: 'Annuler',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });

    // Theme Toggle Logic
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('ormvasm-theme', newTheme);
            
            if (window.ApexCharts) {
                window.dispatchEvent(new Event('resize')); 
            }
        });
    }

    // Global Loader Control
    const loader = document.getElementById('pageLoader');
    
    // Hide on load
    window.addEventListener('load', function() {
        setTimeout(() => {
            if (loader) loader.classList.add('hidden');
        }, 300);
    });

    // Show on navigation
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (link && 
            link.href && 
            link.href.startsWith(window.location.origin) && 
            !link.href.includes('#') && 
            !link.target && 
            !link.hasAttribute('download')) {
            if (loader) loader.classList.remove('hidden');
        }
    });

    // Show on form submit
    document.addEventListener('submit', function(e) {
        if (!e.defaultPrevented) {
            if (loader) loader.classList.remove('hidden');
        }
    });

    // Global DataTables Initialization helper (can be called manually when needed)
    window.initDataTable = (selector) => {
        if (typeof jQuery !== 'undefined' && $(selector).length > 0 && !$.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
                },
                pageLength: 10,
                order: [[0, 'desc']],
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            });
        }
    };
});
</script>

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
    {{-- Command Center (Global Search Overlay) --}}
    <div id="commandCenter" class="command-center-overlay" style="display:none;">
        <div class="command-center-modal shadow-lg">
            <div class="command-center-header">
                <i class="bi bi-search"></i>
                <input type="text" id="commandCenterInput" placeholder="Rechercher n'importe quoi (Agriculteur, Paiement, TR...)" autocomplete="off">
                <div class="command-center-shortcut">ESC pour fermer</div>
            </div>
            <div id="commandCenterResults" class="command-center-results">
                <div class="p-4 text-center text-muted small">Commencez à taper pour rechercher...</div>
            </div>
            <div class="command-center-footer">
                <span><kbd>↑↓</kbd> Naviguer</span>
                <span><kbd>Enter</kbd> Sélectionner</span>
                <span class="ms-auto"><kbd>Ctrl+K</kbd> pour ouvrir</span>
            </div>
        </div>
    </div>

    <style>
        .command-center-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
            z-index: 10000;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-top: 10vh;
        }
        .command-center-modal {
            width: 100%;
            max-width: 650px;
            background: var(--bg-surface);
            border: 1px solid var(--gray-200);
            border-radius: 1rem;
            overflow: hidden;
            animation: command-center-in 0.2s ease-out;
        }
        @keyframes command-center-in {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .command-center-header {
            display: flex;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-100);
            gap: 1rem;
        }
        .command-center-header i { font-size: 1.25rem; color: var(--c-primary); }
        .command-center-header input {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 1.1rem;
            color: var(--gray-800);
            outline: none;
        }
        .command-center-shortcut {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--gray-400);
            letter-spacing: 0.5px;
            background: var(--gray-50);
            padding: 2px 6px;
            border-radius: 4px;
        }
        .command-center-results {
            max-height: 400px;
            overflow-y: auto;
            padding: 0.5rem;
        }
        .search-result-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
            color: inherit;
        }
        .search-result-item:hover, .search-result-item.active {
            background: var(--gray-50);
            transform: translateX(5px);
        }
        .search-result-icon {
            width: 40px;
            height: 40px;
            background: var(--gray-100);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--gray-600);
        }
        .search-result-info { flex: 1; }
        .search-result-title { font-weight: 600; font-size: 0.95rem; margin-bottom: 0; color: var(--gray-800); }
        .search-result-subtitle { font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0; }
        .search-result-badge { font-size: 0.7rem; background: var(--gray-100); color: var(--gray-600); padding: 2px 8px; border-radius: 20px; }
        
        .command-center-footer {
            padding: 0.75rem 1.5rem;
            background: var(--gray-50);
            border-top: 1px solid var(--gray-100);
            display: flex;
            gap: 1.5rem;
            font-size: 0.75rem;
            color: var(--gray-500);
        }
        .command-center-footer kbd {
            background: #fff;
            border: 1px solid var(--gray-300);
            border-bottom-width: 2px;
            color: var(--gray-700);
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 0.7rem;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const commandCenter = document.getElementById('commandCenter');
            const input = document.getElementById('commandCenterInput');
            const results = document.getElementById('commandCenterResults');
            let debounceTimer;

            // Shortcut listener (Ctrl + K or Cmd + K)
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    commandCenter.style.display = 'flex';
                    input.value = '';
                    results.innerHTML = '<div class="p-4 text-center text-muted small">Commencez à taper pour rechercher...</div>';
                    input.focus();
                }
                if (e.key === 'Escape') {
                    commandCenter.style.display = 'none';
                }
            });

            // Close on click outside
            commandCenter.addEventListener('click', function(e) {
                if (e.target === commandCenter) commandCenter.style.display = 'none';
            });

            // Ajax Search
            input.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                const q = this.value;
                if (q.length < 2) {
                    results.innerHTML = '<div class="p-4 text-center text-muted small">Commencez à taper pour rechercher...</div>';
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetch(`{{ route('global-search') }}?q=${encodeURIComponent(q)}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.length === 0) {
                                results.innerHTML = '<div class="p-4 text-center text-muted small">Aucun résultat trouvé.</div>';
                                return;
                            }
                            results.innerHTML = data.map((item, idx) => `
                                <a href="${item.url}" class="search-result-item ${idx === 0 ? 'active' : ''}">
                                    <div class="search-result-icon">
                                        <i class="bi ${item.icon}"></i>
                                    </div>
                                    <div class="search-result-info">
                                        <div class="search-result-title">${item.title}</div>
                                        <div class="search-result-subtitle">${item.subtitle}</div>
                                    </div>
                                    <div class="search-result-badge">${item.type}</div>
                                </a>
                            `).join('');
                        });
                }, 300);
            });

            // Keyboard navigation
            input.addEventListener('keydown', function(e) {
                const items = results.querySelectorAll('.search-result-item');
                let activeIdx = Array.from(items).findIndex(i => i.classList.contains('active'));

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (activeIdx < items.length - 1) {
                        if (activeIdx !== -1) items[activeIdx].classList.remove('active');
                        items[activeIdx + 1].classList.add('active');
                        items[activeIdx + 1].scrollIntoView({ block: 'nearest' });
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (activeIdx > 0) {
                        items[activeIdx].classList.remove('active');
                        items[activeIdx - 1].classList.add('active');
                        items[activeIdx - 1].scrollIntoView({ block: 'nearest' });
                    }
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (activeIdx !== -1) {
                        window.location.href = items[activeIdx].getAttribute('href');
                    }
                }
            });
            
            // Global Loader Link Check
            const links = document.querySelectorAll('a');
            const loader = document.getElementById('loader');
            links.forEach(link => {
                link.addEventListener('click', (e) => {
                    // Do not show loader for PDF previews or hash links
                    if (link.classList.contains('btn-preview-pdf') || (link.getAttribute('href') && link.getAttribute('href').startsWith('#'))) {
                        return;
                    }
                    if (link.hostname === window.location.hostname && !link.getAttribute('target')) {
                        if (loader) loader.classList.add('active');
                    }
                });
            });
        });
    </script>

    {{-- ORMSA AI Assistant --}}
    <div id="aiChat" class="ai-chat-container">
        <div id="aiChatWindow" class="ai-chat-window shadow-lg" style="display:none;">
            <div class="ai-chat-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="ai-avatar"><i class="bi bi-robot"></i></div>
                    <div>
                        <div class="fw-bold small">ORMSA AI</div>
                        <div class="ai-status">En ligne</div>
                    </div>
                </div>
                <button type="button" id="closeAiChat" class="btn-close btn-close-white"></button>
            </div>
            <div id="aiChatBody" class="ai-chat-body">
                <div class="ai-message ai-message-bot">
                    Bonjour ! Je suis votre assistant ORMSA. Posez-moi n'importe quelle question sur vos finances ou vos agriculteurs !
                </div>
            </div>
            <div class="ai-chat-footer">
                <input type="text" id="aiChatInput" placeholder="Demandez quelque chose..." autocomplete="off">
                <button id="sendAiMessage"><i class="bi bi-send-fill"></i></button>
            </div>
        </div>
        <button id="aiChatToggle" class="ai-chat-toggle shadow-lg">
            <i class="bi bi-chat-dots-fill"></i>
            <span class="ai-badge">AI</span>
        </button>
    </div>

    <style>
        .ai-chat-container { position: fixed; bottom: 30px; right: 30px; z-index: 9999; font-family: 'Inter', sans-serif; }
        .ai-chat-toggle {
            width: 60px; height: 60px; border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            border: none; color: white; font-size: 1.5rem;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }
        .ai-chat-toggle:hover { transform: scale(1.1) rotate(5deg); }
        .ai-badge {
            position: absolute; top: -5px; right: -5px;
            background: #ef4444; color: white; font-size: 0.6rem;
            font-weight: 800; padding: 2px 5px; border-radius: 10px;
            border: 2px solid white;
        }
        .ai-chat-window {
            position: absolute; bottom: 80px; right: 0;
            width: 350px; height: 480px;
            background: #ffffff; /* Solid white background */
            border-radius: 1.25rem;
            display: flex; flex-direction: column;
            overflow: hidden; animation: ai-slide-in 0.3s ease-out;
            border: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2) !important;
        }
        @keyframes ai-slide-in { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .ai-chat-header {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white; padding: 1.25rem;
            display: flex; align-items: center; justify-content: space-between;
            backdrop-filter: blur(10px);
        }
        .ai-avatar { width: 35px; height: 35px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
        .ai-status { font-size: 0.7rem; opacity: 0.8; display: flex; align-items: center; gap: 4px; }
        .ai-status::before { content: ''; width: 6px; height: 6px; background: #4ade80; border-radius: 50%; display: inline-block; }
        .ai-chat-body { flex: 1; padding: 1.25rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1rem; background: #fdfdfd; }
        .ai-message { max-width: 85%; padding: 0.75rem 1rem; border-radius: 1rem; font-size: 0.85rem; line-height: 1.4; position: relative; }
        .ai-message-bot { background: #f1f5f9; color: var(--gray-800); border-bottom-left-radius: 2px; }
        .ai-message-user { background: #10b981; color: white; align-self: flex-end; border-bottom-right-radius: 2px; }
        .ai-chat-footer { padding: 1rem; border-top: 1px solid var(--gray-100); display: flex; gap: 0.5rem; background: #ffffff; }
        .ai-chat-footer input { 
            flex: 1; 
            border: 1px solid var(--gray-200); 
            border-radius: 2rem; 
            padding: 0.6rem 1rem; 
            font-size: 0.85rem; 
            outline: none; 
            background: #ffffff !important; /* Force solid background */
            color: #1e293b;
        }
        .ai-chat-footer input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }
        .ai-chat-footer button { width: 38px; height: 38px; border-radius: 50%; border: none; background: #10b981; color: white; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
        .ai-chat-footer button:hover { background: #059669; }
        .ai-typing { font-style: italic; font-size: 0.75rem; color: var(--gray-400); }

        /* Prevent loader from showing when a modal is open */
        body.modal-open #pageLoader { display: none !important; opacity: 0 !important; visibility: hidden !important; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Global PDF Preview Logic
            document.querySelectorAll('.btn-preview-pdf').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Force hide the loader (using correct ID and class)
                    const loader = document.getElementById('pageLoader');
                    if (loader) loader.classList.add('hidden');
                    
                    const url = this.getAttribute('href');
                    const modal = new bootstrap.Modal(document.getElementById('pdfPreviewModal'));
                    document.getElementById('pdfPreviewFrame').src = url;
                    modal.show();
                });
            });

            const toggle = document.getElementById('aiChatToggle');
            const windowEl = document.getElementById('aiChatWindow');
            const close = document.getElementById('closeAiChat');
            const input = document.getElementById('aiChatInput');
            const send = document.getElementById('sendAiMessage');
            const body = document.getElementById('aiChatBody');

            if (!toggle) return;

            toggle.addEventListener('click', () => {
                const isHidden = windowEl.style.display === 'none';
                windowEl.style.display = isHidden ? 'flex' : 'none';
                if (isHidden) input.focus();
            });

            close.addEventListener('click', () => windowEl.style.display = 'none');

            function addMessage(text, isUser = false) {
                const msg = document.createElement('div');
                msg.className = `ai-message ${isUser ? 'ai-message-user' : 'ai-message-bot'}`;
                msg.innerHTML = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
                body.appendChild(msg);
                body.scrollTop = body.scrollHeight;
            }

            function handleAsk() {
                const q = input.value.trim();
                if (!q) return;

                addMessage(q, true);
                input.value = '';

                const typing = document.createElement('div');
                typing.className = 'ai-typing mb-2';
                typing.innerText = 'ORMSA AI réfléchit...';
                body.appendChild(typing);
                body.scrollTop = body.scrollHeight;

                fetch(`{{ route('ai.ask') }}?q=${encodeURIComponent(q)}`)
                    .then(res => res.json())
                    .then(data => {
                        typing.remove();
                        addMessage(data.message);
                    })
                    .catch(() => {
                        typing.remove();
                        addMessage("Désolé, j'ai rencontré une erreur. Réessayez plus tard !");
                    });
            }

            if (send) send.addEventListener('click', handleAsk);
            if (input) input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') handleAsk();
            });
        });
    </script>
</body>
</html>