<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ $receipt->receipt_number }}</title>
    <style>
        /* ── RESET & BASE ── */
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            line-height: 1.3;
        }

        /* ── SCREEN PREVIEW ── */
        @media screen {
            body {
                background: #f1f5f9;
                padding: 40px 10px;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .receipt-container {
                background: #fff;
                width: 80mm;
                padding: 10mm 8mm;
                border-radius: 4px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            }
            .no-print-hint {
                margin-top: 20px;
                color: #64748b;
                font-size: 12px;
                font-family: sans-serif;
            }
        }

        /* ── PRINT CONFIG ── */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            body {
                width: 80mm;
                margin: 0;
                padding: 4mm;
            }
            .no-print-hint {
                display: none !important;
            }
        }

        /* ── RECEIPT CONTENT ── */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .header {
            margin-bottom: 8px;
        }
        .header .shop-name {
            font-size: 18px;
            font-weight: 900;
            margin: 0;
        }
        .header .shop-tagline {
            font-size: 10px;
            margin: 2px 0;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table td, table th {
            padding: 2px 0;
            vertical-align: top;
        }

        .meta-table td { font-weight: bold; }

        .items-table thead th {
            border-bottom: 1px dashed #000;
            text-align: left;
            padding-bottom: 4px;
        }
        .items-table td {
            padding: 6px 0;
        }

        .item-name {
            font-weight: 900;
            font-size: 11px;
            display: block;
        }
        .item-code {
            font-size: 8px;
            color: #333;
        }

        .totals-table {
            margin-top: 4px;
        }
        .totals-table td {
            padding: 1px 0;
        }

        .net-total-row {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            font-size: 15px;
            font-weight: 900;
            margin: 8px 0;
            padding: 8px 0;
            display: block;
            width: 100%;
        }

        .footer {
            margin-top: 15px;
            font-size: 10px;
        }
        .footer .thank-you {
            font-size: 12px;
            font-weight: 900;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>

<div class="receipt-container">
    
    <div class="header text-center">
        <div class="shop-name">DROGUERIE S</div>
        <div class="shop-tagline">Quincaillerie & Articles de bricolage</div>
        <div class="shop-tagline">Tél: +213 XX XX XX XX</div>
    </div>

    <div class="divider"></div>

    <table class="meta-table">
        <tr>
            <td>Ticket N° : {{ $receipt->receipt_number }}</td>
            <td class="text-right">Date : {{ optional($receipt->printed_at)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Caisse : Principal</td>
            <td class="text-right">Heure : {{ optional($receipt->printed_at)->format('H:i') }}</td>
        </tr>
        <tr>
            <td colspan="2">Caissier : {{ $receipt->sale->cashier->name ?? 'Caissier' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 45%;">Article</th>
                <th class="text-center" style="width: 10%;">Qté</th>
                <th class="text-right" style="width: 22%;">P.U</th>
                <th class="text-right" style="width: 23%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($receipt->sale->items as $item)
            <tr>
                <td>
                    <span class="item-name">{{ $item->product->name }}</span>
                    <span class="item-code">Code: {{ $item->product->barcode }}</span>
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right font-bold">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table class="totals-table">
        <tr>
            <td>Sous-total :</td>
            <td class="text-right">{{ number_format($receipt->sale->total_amount, 2) }} {{ $currencySymbol }}</td>
        </tr>
        <tr>
            <td>TVA (0.00%) :</td>
            <td class="text-right">0.00 {{ $currencySymbol }}</td>
        </tr>
    </table>

    <div class="net-total-row">
        <table style="font-size: inherit;">
            <tr>
                <td style="font-weight: 900;">NET À PAYER :</td>
                <td class="text-right" style="font-weight: 900;">{{ number_format($receipt->sale->total_amount, 2) }} {{ $currencySymbol }}</td>
            </tr>
        </table>
    </div>

    <div class="footer text-center">
        <div class="thank-you">Merci pour votre visite !</div>
        <div>Les articles vendus ne sont ni pris ni échangés après 7 jours.</div>
        <div style="font-size: 8px; margin-top: 10px; opacity: 0.7;">Droguerie POS System</div>
    </div>

</div>

<div class="no-print-hint">Impression en cours...</div>

<script>
    window.addEventListener('load', function() {
        setTimeout(function() {
            window.print();
        }, 500);
    });

    window.onafterprint = function() {
        window.location.href = "{{ route('pos.index') }}";
    };
</script>

</body>
</html>
