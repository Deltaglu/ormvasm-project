<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .info-grid { display: flex; gap: 10px; margin: 8px 0; }
        .info-box { flex: 1; border: 1px solid #ddd; border-radius: 4px; padding: 8px; background: #fafafa; }
        .info-box h3 { margin: 0 0 6px 0; font-size: 11px; color: #1a3c6e; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        .info-box table { width: 100%; border-collapse: collapse; }
        .info-box table td { padding: 2px 0; font-size: 9px; }
        .info-box .label { width: 40%; font-weight: bold; color: #444; }
        /* Amount section */
        .amount-section { border: 2px solid #1a3c6e; border-radius: 6px; padding: 8px; margin: 8px 0; text-align: center; background: #f7f9fc; }
        .amount-section .amount-label { font-size: 10px; color: #555; text-transform: uppercase; }
        .amount-section .amount-value { font-size: 20px; font-weight: bold; color: #1a3c6e; margin-top: 2px; }
        .amount-section .amount-words { font-size: 8px; color: #555; margin-top: 3px; font-style: italic; }
        /* Remaining amount section */
        .remaining-section { border: 2px solid #d9534f; border-radius: 6px; padding: 8px; margin: 8px 0; text-align: center; background: #fff5f5; }
        .remaining-section .remaining-label { font-size: 10px; color: #d9534f; text-transform: uppercase; }
        .remaining-section .remaining-value { font-size: 18px; font-weight: bold; color: #d9534f; margin-top: 2px; }
        .remaining-section .remaining-zero { font-size: 10px; color: #28a745; margin-top: 2px; font-weight: bold; }
        /* Details table */
        table.details { width: 100%; border-collapse: collapse; margin: 8px 0; }
        table.details th { background: #1a3c6e; color: #fff; padding: 4px 6px; text-align: left; font-size: 8px; text-transform: uppercase; }
        table.details td { padding: 3px 6px; border-bottom: 1px solid #ddd; font-size: 9px; }
        table.details tr:nth-child(even) td { background: #f9f9f9; }
        table.details .total-row td { font-weight: bold; border-top: 2px solid #1a3c6e; background: #eef2f7; }
        /* Signature area */
        .signature-area { display: flex; flex-direction: column; }
        .signature-area .sig-title { font-size: 10px; font-weight: bold; color: #1a3c6e; text-align: center; margin-bottom: 2px; }
        .signature-area .sig-box { border: 1px solid #ccc; height: 40px; margin-bottom: 2px; background: #fafafa; }
        .signature-area .sig-line { border-top: 2px solid #333; margin-top: 10px; padding-top: 2px; font-size: 8px; color: #555; text-align: center; }
        .footer { margin-top: 10px; display: flex; justify-content: space-between; gap: 12px; }
        .footer .signature-block { flex: 1; }
        .legal-note { font-size: 7px; color: #888; margin-top: 10px; border-top: 1px solid #ddd; padding-top: 4px; }
    </style>
</head>
<body>
@php
    $p = $quittance->paiement;
    $titre = $p->titreRecette;
    $agri = $titre->agriculteur;
    $logoPath = public_path('images/logo.png');
    $hasLogo = file_exists($logoPath);
    $logoData = '';
    if ($hasLogo) {
        $logoData = base64_encode(file_get_contents($logoPath));
    }
    
    $remaining = (float)$titre->solde_restant;
@endphp

{{-- Header with table layout --}}
<table width="100%" style="border-bottom: 2px solid #2c4a7a; padding-bottom: 10px; margin-bottom: 10px;">
    <tr>
        <!-- LEFT -->
        <td style="width: 70%; vertical-align: top;">
            @if($hasLogo)
                <img src="data:image/png;base64,{{ $logoData }}" style="height: 90px; width: auto;"><br>
            @else
                <div style="height: 90px; width: 180px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #999; font-size: 9px;">LOGO</div><br>
            @endif
            <strong>ORMSA Management System</strong><br>
            <small>Office Régional de Mise en Valeur Agricole</small>
        </td>

        <!-- RIGHT -->
        <td style="width: 30%; text-align: right; vertical-align: top;">
            <h2 style="margin:0; font-size: 16px; color: #1a3c6e;">QUITTANCE</h2>
            <div style="font-size: 9px; color: #555;">N° {{ $quittance->numero }}</div>
            <div style="font-size: 9px; color: #555;">Date : {{ $quittance->date_generation->format('d/m/Y') }}</div>
        </td>
    </tr>
</table>

{{-- Two info boxes side by side --}}
<div class="info-grid">
    <div class="info-box">
        <h3>Informations agriculteur</h3>
        <table>
            <tr><td class="label">Nom complet</td><td>{{ $agri->prenom }} {{ $agri->nom }}</td></tr>
            <tr><td class="label">CIN</td><td>{{ $agri->cin }}</td></tr>
            <tr><td class="label">Adresse</td><td>{{ $agri->adresse ?? '—' }}</td></tr>
            <tr><td class="label">Téléphone</td><td>{{ $agri->telephone ?? '—' }}</td></tr>
        </table>
    </div>
    <div class="info-box">
        <h3>Informations paiement</h3>
        <table>
            <tr><td class="label">Réf. paiement</td><td>{{ $p->reference }}</td></tr>
            <tr><td class="label">Date paiement</td><td>{{ $p->date_paiement->format('d/m/Y') }}</td></tr>
            <tr><td class="label">Type</td><td>{{ $p->type_paiement }}</td></tr>
            <tr><td class="label">Titre de recette</td><td>{{ $titre->numero }}</td></tr>
        </table>
    </div>
</div>

{{-- Amount section --}}
<div class="amount-section">
    <div class="amount-label">Montant payé</div>
    <div class="amount-value">{{ number_format($p->montant, 2, ',', ' ') }} DH</div>
    <div class="amount-words">Arrêtée la présente quittance à la somme de {{ \App\Services\NumberToWords::fr($p->montant) }} dirhams.</div>
</div>

{{-- Remaining amount section --}}
@if($remaining > 0)
<div class="remaining-section">
    <div class="remaining-label">Reste à payer</div>
    <div class="remaining-value">{{ number_format($remaining, 2, ',', ' ') }} DH</div>
</div>
@else
<div class="remaining-section">
    <div class="remaining-label">Reste à payer</div>
    <div class="remaining-zero">PAYÉ EN TOTALITÉ</div>
</div>
@endif

{{-- Details table --}}
<table class="details">
    <thead>
        <tr>
            <th>Désignation</th>
            <th style="text-align:right;">Montant (DH)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Objet : {{ $titre->objet ?? 'Paiement titre de recette' }}</td>
            <td style="text-align:right;">{{ number_format($titre->montant_total, 2, ',', ' ') }}</td>
        </tr>
        <tr>
            <td>Montant total titre</td>
            <td style="text-align:right;">{{ number_format($titre->montant_total, 2, ',', ' ') }}</td>
        </tr>
        <tr>
            <td>Montant déjà payé</td>
            <td style="text-align:right;">{{ number_format($titre->montant_paye, 2, ',', ' ') }}</td>
        </tr>
        <tr>
            <td>Solde restant après paiement</td>
            <td style="text-align:right;">{{ number_format($remaining, 2, ',', ' ') }}</td>
        </tr>
        @if($titre->montant_penalite > 0)
        <tr>
            <td>Pénalité de retard</td>
            <td style="text-align:right;">{{ number_format($titre->montant_penalite, 2, ',', ' ') }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td>Montant payé (présente quittance)</td>
            <td style="text-align:right;">{{ number_format($p->montant, 2, ',', ' ') }}</td>
        </tr>
    </tbody>
</table>

{{-- Signature area --}}
<div class="footer">
    <div class="signature-block">
        <div class="signature-area">
            <div class="sig-title">Signature du contribuable</div>
            <div class="sig-box"></div>
            <div class="sig-line">Lu et approuvé</div>
        </div>
    </div>
    <div class="signature-block">
        <div class="signature-area">
            <div class="sig-title">Cachet et signature de l'office</div>
            <div class="sig-box"></div>
            <div class="sig-line">L'agent recouvreur</div>
        </div>
    </div>
</div>

<div class="legal-note">
    Ce document atteste du paiement indiqué ci-dessus. Conservez-le pour votre dossier.
    — Document généré le {{ now()->format('d/m/Y à H:i') }} par {{ config('app.name') }}.
</div>
</body>
</html>