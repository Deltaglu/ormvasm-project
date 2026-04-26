@extends('layouts.app')

@section('title', 'Connexion — '.config('app.name'))

@section('content')
<div class="row g-0 ormsa-auth-split">
    <div class="col-lg-6 d-none d-lg-flex ormsa-auth-hero">
        <div class="ormsa-auth-hero-inner w-100">
            <div class="ormsa-brand-mark mb-4 bg-transparent border border-light border-opacity-25" style="width: 3rem; height: 3rem; font-size: 1rem;"><img src="{{ asset('images/logo.png') }}" alt="ORMVA" style="height: 2rem;"></div>
            <h2 class="h3 fw-semibold mb-3">ORMVASM</h2>
            <p class="text-white-50 mb-4 lh-lg" style="max-width: 28rem;">
                Plateforme de gestion des recettes et du recouvrement. Suivi des titres, paiements et quittances par société.
            </p>
            <ul class="list-unstyled text-white-50 small mb-0">
                <li class="mb-2"><i class="bi bi-check-circle me-2 text-primary"></i> Multi-tenant par code entreprise</li>
                <li class="mb-2"><i class="bi bi-check-circle me-2 text-primary"></i> Traçabilité des encaissements</li>
                <li><i class="bi bi-check-circle me-2 text-primary"></i> Quittances PDF</li>
            </ul>
        </div>
    </div>
    <div class="col-lg-6 ormsa-auth-panel bg-light">
        <div class="ormsa-auth-card card">
            <div class="card-body">
                <p class="text-secondary small text-uppercase fw-semibold mb-1" style="letter-spacing: 0.06em;">Accès sécurisé</p>
                <h1 class="h4 fw-semibold mb-1">Connexion</h1>
                <p class="text-secondary small mb-4">{{ config('app.name') }}</p>
                <form method="post" action="{{ route('login.attempt') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="company_code">Code entreprise</label>
                        <input type="text" name="company_code" id="company_code" value="{{ old('company_code') }}" class="form-control form-control-lg" required autofocus autocomplete="organization" placeholder="Ex. soc1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Adresse e-mail</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control form-control-lg" required autocomplete="username">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Mot de passe</label>
                        <input type="password" name="password" id="password" class="form-control form-control-lg" required autocomplete="current-password">
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                        <label class="form-check-label small" for="remember">Se souvenir de moi sur cet appareil</label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">Se connecter</button>
                </form>
                <p class="text-muted small mt-4 mb-0 border-top pt-3">
                    <span class="fw-medium">Démo :</span>
                    <code class="user-select-all">soc1</code> ·
                    <code class="user-select-all">admin@test.com</code> ·
                    <code class="user-select-all">password</code>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
