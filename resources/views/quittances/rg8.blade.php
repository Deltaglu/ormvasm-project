<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>RG8 - Quittances</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px double #1a3c6e;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 18px;
            color: #1a3c6e;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 14px;
            color: #555;
            font-weight: normal;
        }
        .period {
            background: #f0f5ff;
            border: 1px solid #1a3c6e;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
        }
        .period strong {
            color: #1a3c6e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        thead {
            display: table-header-group;
        }
        th {
            background: #1a3c6e;
            color: white;
            font-weight: bold;
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }
        td {
            padding: 6px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background: #f8fafc;
        }
        .numero {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #1a3c6e;
        }
        .montant {
            text-align: right;
            font-weight: bold;
            color: #16a34a;
        }
        .client {
            font-size: 9px;
        }
        .client-type {
            font-size: 7px;
            color: #666;
            text-transform: uppercase;
        }
        .total-row {
            background: #1a3c6e !important;
            color: white;
        }
        .total-row td {
            padding: 10px 6px;
            font-weight: bold;
            font-size: 11px;
            border: none;
        }
        .total-row .montant {
            color: white;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .date-col { width: 12%; }
        .numero-col { width: 15%; }
        .client-col { width: 35%; }
        .montant-col { width: 18%; }
        .ref-col { width: 20%; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RG8 - Registre des Quittances</h1>
        <h2>Rapport des encaissements</h2>
    </div>
    
    <div class="period">
        <strong>Période du {{ $periodStart }} au {{ $periodEnd }} (10 derniers jours)</strong><br>
        Nombre de quittances: <strong>{{ $quittances->count() }}</strong>
    </div>
    
    <table>
        <thead>
            <tr>
                <th class="numero-col">N° Quittance</th>
                <th class="date-col">Date Paiement</th>
                <th class="ref-col">Référence</th>
                <th class="client-col">Client</th>
                <th class="montant-col" style="text-align: right;">Montant (DH)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quittances as $q)
                @php $p = $q->paiement; $client = $p->titreRecette->agriculteur; @endphp
                <tr>
                    <td class="numero">{{ $q->numero }}</td>
                    <td>{{ $p->date_paiement->format('d/m/Y') }}</td>
                    <td>{{ $p->reference }}</td>
                    <td class="client">
                        <span class="client-type">[{{ $client->type === 'society' ? 'Société' : 'Particulier' }}]</span><br>
                        {{ $client->type === 'society' ? $client->nom : ($client->prenom . ' ' . $client->nom) }}
                    </td>
                    <td class="montant">{{ number_format($p->montant, 2, ',', ' ') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">TOTAL GÉNÉRAL</td>
                <td class="montant">{{ number_format($total, 2, ',', ' ') }} DH</td>
            </tr>
        </tbody>
    </table>
    
    <div class="footer">
        Document généré le {{ now()->format('d/m/Y à H:i') }} | Page 1/1
    </div>
</body>
</html>
