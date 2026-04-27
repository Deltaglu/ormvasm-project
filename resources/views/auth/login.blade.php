@extends('layouts.app')

@section('title', 'Connexion — '.config('app.name'))

@section('content')
<div class="ormsa-auth-split row g-0" style="min-height:100vh;">

    {{-- Hero panel --}}
    <div class="col-lg-6 d-none d-lg-flex ormsa-auth-hero">
        <div style="max-width:420px;">
            <div class="ormsa-brand-mark mb-4" style="width:3rem;height:3rem;">
                <img src="{{ asset('images/logo.png') }}" alt="ORMVA" style="height:2rem;">
            </div>
            <h2 class="fw-bold text-white mb-3" style="font-size:1.9rem;letter-spacing:-.02em;line-height:1.2;">
                ORMVASM<br><span style="color:rgba(255,255,255,.55);font-size:1rem;font-weight:500;letter-spacing:0;">Système de gestion des recettes</span>
            </h2>
            <p class="mb-4" style="color:rgba(255,255,255,.6);line-height:1.75;font-size:.92rem;">
                Plateforme de gestion des recettes et du recouvrement. Suivi des titres, paiements et quittances.
            </p>
            <ul class="list-unstyled mb-0" style="color:rgba(255,255,255,.65);font-size:.88rem;">
                <li class="mb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-success"></i> Traçabilité des encaissements
                </li>
                <li class="mb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-success"></i> Gestion des agriculteurs
                </li>
                <li class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-success"></i> Quittances PDF automatiques
                </li>
            </ul>
        </div>
    </div>

    {{-- Login panel --}}
    <div class="col-lg-6 ormsa-auth-panel">
        <div class="ormsa-auth-card">
            <div class="card-body p-0">
                {{-- Header --}}
                <div class="mb-5">
                    <p class="mb-1" style="font-size:.7rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--gray-400);">Accès sécurisé</p>
                    <h1 class="mb-0" style="font-size:1.6rem;font-weight:800;letter-spacing:-.02em;color:var(--gray-900);">Connexion</h1>
                    <p class="mt-1 mb-0" style="color:var(--gray-400);font-size:.875rem;">{{ config('app.name') }}</p>
                </div>

                {{-- Errors --}}
                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <div class="d-flex gap-2 align-items-start">
                            <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                            <div>
                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <form method="post" action="{{ route('login.attempt') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="email">Adresse e-mail</label>
                        <input type="email" name="email" id="email"
                               value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror"
                               required autofocus autocomplete="username">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="password">Mot de passe</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               required autocomplete="current-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                        <label class="form-check-label" for="remember" style="font-size:.85rem;color:var(--gray-500);">
                            Se souvenir de moi
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" style="padding:.65rem 1rem;font-size:.95rem;font-weight:600;">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
                    </button>
                </form>

                {{-- Demo hint --}}
                <div class="mt-4 pt-3" style="border-top:1px solid var(--border);">
                    <p class="mb-0" style="font-size:.78rem;color:var(--gray-400);">
                        <strong style="color:var(--gray-500);">Démo :</strong>
                        <code>admin@test.com</code> · <code>password</code>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
