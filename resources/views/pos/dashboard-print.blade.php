<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Journalier</title>
    <style>
        * { box-sizing: border-box; }
        @page { size: 80mm auto; margin: 6mm; }
        body { font-family: Arial, sans-serif; font-size: 16px; margin: 0; padding: 0; color: #000; }
        .ticket { padding: 8px; }
        h1 { font-size: 16px; margin: 0 0 8px 0; text-align: center; }
        .meta { text-align: center; font-size: 12px; color: #000; margin-bottom: 10px; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 0; }
        th { text-align: left; font-size: 12px; text-transform: uppercase; color: #000; font-weight: 700; }
        td.value { text-align: right; font-weight: 600; }
        .total { font-size: 14px; font-weight: 700; }
        .no-print { margin-top: 12px; text-align: center; }
        .btn { background: #000; color: #fff; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; }
        @media print {
            .no-print { display: none; }
            body { width: 80mm; }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <h1>Ticket Journalier</h1>
        <div class="meta">Période: {{ $from }} -> {{ $to }}</div>
        <div class="meta">Imprimé: {{ now()->format('d/m/Y H:i') }}</div>
        <div class="meta">Utilisateur: {{ auth()->user()->name }}</div>

        <div class="divider"></div>

        <table>
            <tbody>
                <tr>
                    <td>Total ventes</td>
                    <td class="value">{{ number_format($totalRevenue, 2) }} {{ $currencySymbol }}</td>
                </tr>
                <tr>
                    <td>Nombre de ventes</td>
                    <td class="value">{{ $salesCount }}</td>
                </tr>
            </tbody>
        </table>

        <div class="divider"></div>

        <table>
            <thead>
                <tr>
                    <th>Paiement</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentBreakdown as $row)
                    <tr>
                        <td>{{ $row->payment_method === 'cash' ? $cashPaymentLabel : ucfirst($row->payment_method) }}</td>
                        <td class="value">{{ number_format($row->total_amount, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                @empty
                    <tr>
                        <td>Aucune vente</td>
                        <td class="value">0.00 {{ $currencySymbol }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="divider"></div>

        <div class="no-print">
            <button class="btn" onclick="window.print()">Imprimer</button>
        </div>
    </div>

    <script>
        window.addEventListener('load', () => {
            window.print();
        });
    </script>
</body>
</html>
