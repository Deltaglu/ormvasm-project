<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relevé de Compte - {{ $agriculteur->nom }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; font-size: 11px; margin: 0; padding: 0; }
        .header-table { width: 100%; border-bottom: 3px solid #10b981; padding-bottom: 45px; margin-bottom: 40px; }
        .logo-img { height: 85px; width: auto; image-rendering: -webkit-optimize-contrast; margin-bottom: 20px; }
        .title { text-align: right; font-size: 16px; color: #111827; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
        .info-section { width: 100%; margin-bottom: 25px; }
        .info-box { width: 48%; display: inline-block; vertical-align: top; }
        .info-label { color: #888; font-size: 10px; text-transform: uppercase; margin-bottom: 5px; }
        .info-value { font-weight: bold; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background-color: #f9fafb; color: #4b5563; text-align: left; padding: 10px; border-bottom: 1px solid #e5e7eb; text-transform: uppercase; font-size: 10px; }
        td { padding: 10px; border-bottom: 1px solid #f3f4f6; }
        .amount { text-align: right; font-family: 'Courier', monospace; font-weight: bold; }
        .summary-card { background: #f9fafb; border-radius: 8px; padding: 20px; margin-top: 40px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px dashed #e5e7eb; padding-bottom: 5px; }
        .summary-label { color: #6b7280; }
        .summary-value { font-weight: bold; font-size: 16px; float: right; }
        .final-balance { background: #10b981; color: white; padding: 15px; border-radius: 6px; margin-top: 10px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #aaa; padding: 20px 0; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo.png');
        $logoBase64 = '';
        if(file_exists($logoPath)){
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }
    @endphp

    <table class="header-table">
        <tr>
            <td style="border: none; padding: 0;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo-img">
                @else
                    <div style="font-size: 24px; font-weight: bold; color: #10b981;">ORMVASM</div>
                @endif
            </td>
            <td style="border: none; padding: 0; vertical-align: middle;">
                <div class="title">Relevé de Compte</div>
            </td>
        </tr>
    </table>

    <div class="info-section">
        <div class="info-box">
            <div class="info-label">Agriculteur</div>
            <div class="info-value">{{ $agriculteur->prenom }} {{ $agriculteur->nom }}</div>
            <div>CIN: {{ $agriculteur->cin }}</div>
            <div>{{ $agriculteur->telephone }}</div>
        </div>
        <div class="info-box" style="text-align: right;">
            <div class="info-label">Date du Relevé</div>
            <div class="info-value">{{ date('d/m/Y') }}</div>
        </div>
    </div>

    <h3>Historique des Titres de Recette</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>N° Titre</th>
                <th style="text-align: right;">Montant Total</th>
                <th style="text-align: right;">Solde Restant</th>
            </tr>
        </thead>
        <tbody>
            @foreach($agriculteur->titresRecettes as $titre)
            <tr>
                <td>{{ $titre->created_at->format('d/m/Y') }}</td>
                <td>{{ $titre->numero }}</td>
                <td class="amount">{{ number_format($titre->montant_total + $titre->montant_penalite, 2, ',', ' ') }} DH</td>
                <td class="amount" style="color: {{ $titre->solde_restant > 0 ? '#ef4444' : '#10b981' }}">
                    {{ number_format($titre->solde_restant, 2, ',', ' ') }} DH
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Historique des Paiements</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Référence</th>
                <th>Quittance</th>
                <th style="text-align: right;">Montant Payé</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPaid = 0; @endphp
            @foreach($agriculteur->titresRecettes as $titre)
                @foreach($titre->paiements as $p)
                @php $totalPaid += $p->montant; @endphp
                <tr>
                    <td>{{ $p->date_paiement->format('d/m/Y') }}</td>
                    <td>{{ $p->reference }}</td>
                    <td>{{ $p->quittance?->numero ?? '-' }}</td>
                    <td class="amount" style="color: #10b981;">{{ number_format($p->montant, 2, ',', ' ') }} DH</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="summary-card">
        <div class="summary-row">
            <span class="summary-label">Total des Titres Émis (Pénalités incl.)</span>
            <span class="summary-value">{{ number_format($agriculteur->titresRecettes->sum('montant_total') + $agriculteur->titresRecettes->sum('montant_penalite'), 2, ',', ' ') }} DH</span>
        </div>
        <div style="clear: both;"></div>
        <div class="summary-row" style="margin-top: 10px;">
            <span class="summary-label">Total des Montants Encaissés</span>
            <span class="summary-value" style="color: #10b981;">{{ number_format($totalPaid, 2, ',', ' ') }} DH</span>
        </div>
        <div style="clear: both;"></div>
        
        <div class="final-balance">
            <span style="font-size: 14px; text-transform: uppercase;">Solde Restant à Payer :</span>
            <span style="font-size: 20px; font-weight: bold; float: right;">
                {{ number_format($agriculteur->titresRecettes->sum('solde_restant'), 2, ',', ' ') }} DH
            </span>
            <div style="clear: both;"></div>
        </div>
    </div>

    <div class="footer">
        Document généré par ORMSA Management System - Le {{ date('d/m/Y à H:i') }}
    </div>
</body>
</html>
