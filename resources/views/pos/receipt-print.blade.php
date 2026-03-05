<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $receipt->receipt_number }}</title>
    <style>
        /* ── Screen preview ── */
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
            color: #000;
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
        .header .shop-tagline {
            font-size: 10px;
            margin: 1px 0;
        }
        .header .shop-phone {
            font-size: 10px;
            margin: 1px 0;
        }

        /* Divider */
        .dashed { border: none; border-top: 1.5px dashed #000; margin: 6px 0; }

        /* ── Meta info (ticket #, date, time) ── */
        .meta {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            font-weight: bold;
        }
        .meta td {
            padding: 2px 1px;
            vertical-align: top;
            color: #000;
        }
        /* LTR cells for numbers/dates */
        .meta td.ltr {
            direction: ltr;
            text-align: left;
        }
        .meta td.rtl-label {
            text-align: right;
        }

        /* ── Items table ── */
        .items {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            font-weight: bold;
            margin: 4px 0;
        }
        .items thead tr {
            border-bottom: 1.5px dashed #000;
        }
        .items th {
            padding: 4px 2px;
            font-size: 10px;
            font-weight: 900;
            color: #000;
            text-align: left;
        }
        .items th.num { text-align: center; }
        .items th.prix { text-align: right; }

        .items td {
            padding: 4px 2px;
            border-bottom: 1px dashed #000;
            vertical-align: middle;
            color: #000;
            text-align: left;
        }
        .items td.num { text-align: center; }
        .items td.prix { text-align: right; }
        .items tr:last-child td { border-bottom: none; }

        .item-name {
            font-weight: 900;
            font-size: 10.5px;
        }

        /* ── Totals section ── */
        .totals {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            font-weight: bold;
            margin-top: 4px;
        }
        .totals td {
            padding: 2px 1px;
            color: #000;
        }
        .totals td.lbl { text-align: left; }
        .totals td.val { text-align: right; }

        /* Big TOTAL box */
        .total-box {
            border-top: 1.5px dashed #000;
            border-bottom: 1.5px dashed #000;
            padding: 6px 4px;
            margin: 6px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .total-label {
            font-size: 17px;
            font-weight: 900;
            color: #000;
        }
        .total-amount {
            font-size: 17px;
            font-weight: 900;
            color: #000;
        }

        /* ── Footer ── */
        .footer {
            text-align: center;
            margin-top: 8px;
            font-size: 10px;
            font-weight: bold;
            color: #000;
        }
        .footer .amount-words {
            font-size: 9.5px;
            font-weight: bold;
            margin: 3px 0;
            font-style: italic;
        }
        .footer .thank-you {
            font-size: 12px;
            font-weight: 900;
            margin: 5px 0 2px 0;
        }
        .footer .policy {
            font-size: 9px;
            font-weight: bold;
            margin: 2px 0;
        }
        .footer .system-tag {
            font-size: 8px;
            color: #555;
            margin-top: 5px;
            font-weight: normal;
        }
    </style>
</head>
<body>

<div class="receipt-wrapper">

    {{-- ══ HEADER ══ --}}
    <div class="header">
        <p class="shop-name">DROGUERIE S</p>
        <p class="shop-tagline">Quincaillerie & Articles de bricolage</p>
        <p class="shop-phone">☎ 0XX XX XX XX XX</p>
    </div>

    <hr class="dashed">

    {{-- ══ META ══ --}}
    <table class="meta">
        <tr>
            <td><strong>Ticket N° :</strong> {{ $receipt->receipt_number }}</td>
            <td style="text-align: right;"><strong>Date :</strong> {{ optional($receipt->printed_at)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td><strong>Caisse :</strong> Principal</td>
            <td style="text-align: right;"><strong>Heure :</strong> {{ optional($receipt->printed_at)->format('H:i') }}</td>
        </tr>
    </table>

    <hr class="dashed">

    <table class="items">
        <thead>
            <tr>
                <th>Article</th>
                <th class="num">Qté</th>
                <th class="prix">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($receipt->sale->items as $item)
            <tr>
                <td>
                    <div class="item-name">{{ $item->product->name }}</div>
                    <div style="font-size:8.5px; font-weight:normal; color:#333;">
                        {{ number_format($item->unit_price, 2) }} {{ $currencySymbol }} × {{ $item->quantity }}
                    </div>
                </td>
                <td class="num">{{ $item->quantity }}</td>
                <td class="prix"><strong>{{ number_format($item->subtotal, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <hr class="dashed">

    <table class="totals">
        <tr>
            <td class="lbl">Sous-total :</td>
            <td class="val">{{ number_format($receipt->sale->total_amount, 2) }} {{ $currencySymbol }}</td>
        </tr>
        <tr>
            <td class="lbl">TVA (0%) :</td>
            <td class="val">0.00 {{ $currencySymbol }}</td>
        </tr>
    </table>

    {{-- ══ BIG TOTAL BOX ══ --}}
    <div class="total-box">
        <span class="total-label">Total</span>
        <span class="total-amount">{{ number_format($receipt->sale->total_amount, 2) }} {{ $currencySymbol }}</span>
    </div>

    <div class="footer">
        <p class="amount-words">
            Arrêté à la somme de : <strong>{{ number_format($receipt->sale->total_amount, 2) }} {{ $currencySymbol }}</strong>
        </p>
        <p class="thank-you">Merci pour votre visite !</p>
        <p class="policy">Articles vendus ni repris, ni échangés après 7 jours.</p>
        <p class="system-tag">Droguerie POS System</p>
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
