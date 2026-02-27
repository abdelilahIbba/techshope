<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $receipt->receipt_number }}</title>
    <style>
        /* ── Screen preview styles ── */
        @media screen {
            body {
                background: #f1f5f9;
                display: flex;
                justify-content: center;
                align-items: flex-start;
                padding: 30px 10px;
                min-height: 100vh;
                margin: 0;
            }
            .receipt-wrapper {
                background: #fff;
                width: 80mm;
                padding: 10mm;
                border-radius: 6px;
                box-shadow: 0 4px 24px rgba(0,0,0,0.13);
            }
            .print-hint {
                display: block;
                text-align: center;
                margin-top: 18px;
                font-size: 11px;
                color: #94a3b8;
                font-family: sans-serif;
            }
        }

        /* ── Print styles — 80mm thermal receipt ── */
        @media print {
            @page {
                size: 80mm auto;
                margin: 4mm 3mm;
            }
            html, body {
                width: 80mm;
                margin: 0;
                padding: 0;
            }
            .receipt-wrapper {
                width: 100%;
                padding: 0;
            }
            .print-hint {
                display: none !important;
            }
        }

        /* ── Shared receipt content styles ── */
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #1e293b;
        }

        .receipt-wrapper {
            box-sizing: border-box;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 8px;
        }
        .header .shop-name {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 0 0 2px 0;
        }
        .header .shop-sub {
            font-size: 10px;
            color: #475569;
            margin: 1px 0;
        }

        /* Divider */
        .dashed { border: none; border-top: 1px dashed #94a3b8; margin: 6px 0; }

        /* Meta info */
        .meta { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 4px; }
        .meta td { padding: 1px 0; vertical-align: top; }
        .meta td:last-child { text-align: right; }

        /* Items table */
        .items { width: 100%; border-collapse: collapse; font-size: 10px; margin: 4px 0; }
        .items thead tr { border-bottom: 1px solid #cbd5e1; }
        .items th {
            padding: 3px 2px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            color: #475569;
        }
        .items th.right, .items td.right { text-align: right; }
        .items th.center, .items td.center { text-align: center; }
        .items td {
            padding: 3px 2px;
            border-bottom: 1px dotted #e2e8f0;
            vertical-align: top;
        }
        .items tr:last-child td { border-bottom: none; }
        .item-name { font-weight: bold; font-size: 10px; }
        .item-barcode { font-size: 8px; color: #94a3b8; }

        /* Totals */
        .totals { width: 100%; border-collapse: collapse; font-size: 10px; margin-top: 6px; }
        .totals td { padding: 2px 0; }
        .totals td:last-child { text-align: right; }
        .totals .grand { font-size: 13px; font-weight: bold; border-top: 1px solid #1e293b; padding-top: 4px; }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 9px;
            color: #64748b;
        }
        .footer p { margin: 2px 0; }
    </style>
</head>
<body>

<div class="receipt-wrapper">

    <div class="header">
        <p class="shop-name">DROGUERIE S</p>
        <p class="shop-sub">Quincaillerie &amp; Articles de bricolage</p>
        <p class="shop-sub">123 Rue de la République</p>
        <p class="shop-sub">Tél : +213 XX XX XX XX</p>
    </div>

    <hr class="dashed">

    <table class="meta">
        <tr>
            <td><strong>Ticket N° :</strong> {{ $receipt->receipt_number }}</td>
            <td><strong>Date :</strong> {{ optional($receipt->printed_at)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td><strong>Caisse :</strong> Principal</td>
            <td><strong>Heure :</strong> {{ optional($receipt->printed_at)->format('H:i') }}</td>
        </tr>
    </table>

    <hr class="dashed">

    <table class="items">
        <thead>
            <tr>
                <th>Article</th>
                <th class="center">Qté</th>
                <th class="right">P.U</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($receipt->sale->items as $item)
            <tr>
                <td>
                    <div class="item-name">{{ $item->product->name }}</div>
                    <div class="item-barcode">{{ $item->product->barcode }}</div>
                </td>
                <td class="center">{{ $item->quantity }}</td>
                <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="right"><strong>{{ number_format($item->subtotal, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <hr class="dashed">

    <table class="totals">
        <tr>
            <td>Sous-total :</td>
            <td>{{ number_format($receipt->sale->total_amount, 2) }} DZD</td>
        </tr>
        <tr>
            <td>TVA (0%) :</td>
            <td>0.00 DZD</td>
        </tr>
        <tr class="grand">
            <td>NET À PAYER :</td>
            <td>{{ number_format($receipt->sale->total_amount, 2) }} DZD</td>
        </tr>
    </table>

    <hr class="dashed">

    <div class="footer">
        <p>Merci pour votre visite !</p>
        <p>Les articles vendus ne sont ni repris</p>
        <p>ni échangés après 7 jours.</p>
        <p style="margin-top:6px; font-size:8px; color:#cbd5e1;">Droguerie POS System</p>
    </div>

</div>

<span class="print-hint">Impression en cours... La fenêtre se fermera automatiquement.</span>

<script>
    // Auto-print when page loads, then close the window/tab after printing.
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 300); // short delay to ensure styles are applied
    });

    window.onafterprint = function () {
        // Return to POS after printing
        window.location.href = '{{ route("pos.index") }}';
    };
</script>

</body>
</html>
