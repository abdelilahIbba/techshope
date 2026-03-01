<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $receipt->receipt_number }}</title>
    <style>
        /* ── Screen preview ── */
        @media screen {
            body {
                background: #e5e7eb;
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 30px 10px;
                min-height: 100vh;
                margin: 0;
            }
            .receipt-wrapper {
                background: #fff;
                width: 80mm;
                padding: 8mm 6mm;
                border-radius: 4px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.18);
            }
            .print-hint {
                display: block;
                margin-top: 14px;
                font-size: 11px;
                color: #6b7280;
                font-family: Arial, sans-serif;
                text-align: center;
            }
        }

        /* ── Print styles — 80mm thermal ── */
        @media print {
            @page { 
                size: 80mm auto;
                margin: 3mm 2mm;
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
            .print-hint { display: none !important; }
        }

        /* ── Base ── */
        * { box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            font-weight: bold;
            direction: rtl;
        }

        .receipt-wrapper { width: 100%; }

        /* ── Dashed separator ── */
        .dashed {
            border: none;
            border-top: 1.5px dashed #000;
            margin: 6px 0;
        }

        /* ── Header ── */
        .header {
            text-align: center;
            margin-bottom: 6px;
        }
        .header .shop-name {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 1px;
            margin: 0 0 3px 0;
            text-transform: uppercase;
        }
        .header .shop-tagline {
            font-size: 10px;
            font-weight: bold;
            margin: 2px 0;
        }
        .header .shop-phone {
            font-size: 12px;
            font-weight: 900;
            margin: 3px 0 0 0;
        }

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
            direction: rtl;
        }
        .items thead tr {
            border-bottom: 1.5px dashed #000;
        }
        .items th {
            padding: 4px 2px;
            font-size: 10px;
            font-weight: 900;
            color: #000;
            text-align: right;
        }
        .items th.num { text-align: center; }
        .items th.prix { text-align: left; direction: ltr; }

        .items td {
            padding: 4px 2px;
            border-bottom: 1px dashed #000;
            vertical-align: middle;
            color: #000;
            text-align: right;
        }
        .items td.num { text-align: center; }
        .items td.prix { text-align: left; direction: ltr; }
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
        .totals td.lbl { text-align: right; }
        .totals td.val { text-align: left; direction: ltr; }

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
            direction: ltr;
        }

        /* ── Footer ── */
        .footer {
            text-align: center;
            margin-top: 8px;
            font-size: 10px;
            font-weight: bold;
            color: #000;
            direction: rtl;
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
        <p class="shop-name">DROGUERIE ALOUCHE</p>
        <p class="shop-tagline">دروڤري علوش — قسنطينة</p>
        <p class="shop-tagline">Quincaillerie & Articles de bricolage</p>
        <p class="shop-phone">☎ 0XX XX XX XX XX</p>
    </div>

    <hr class="dashed">

    {{-- ══ META ══ --}}
    <table class="meta">
        <tr>
            <td class="rtl-label">: رقم الفاتورة</td>
            <td class="ltr"><strong>{{ $receipt->receipt_number }}</strong></td>
            <td class="rtl-label">: التاريخ</td>
            <td class="ltr">{{ optional($receipt->printed_at)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="rtl-label">: الصندوق</td>
            <td class="ltr">Principal</td>
            <td class="rtl-label">: الوقت</td>
            <td class="ltr">{{ optional($receipt->printed_at)->format('H:i') }}</td>
        </tr>
    </table>

    <hr class="dashed">

    {{-- ══ ITEMS TABLE ══ --}}
    <table class="items">
        <thead>
            <tr>
                <th>المنتج / Article</th>
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
                        {{ number_format($item->unit_price, 2) }} DH × {{ $item->quantity }}
                    </div>
                </td>
                <td class="num">{{ $item->quantity }}</td>
                <td class="prix"><strong>{{ number_format($item->subtotal, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <hr class="dashed">

    {{-- ══ SUB TOTALS ══ --}}
    <table class="totals">
        <tr>
            <td class="lbl">Sous-total :</td>
            <td class="val">{{ number_format($receipt->sale->total_amount, 2) }} DH</td>
        </tr>
        <tr>
            <td class="lbl">TVA (0%) :</td>
            <td class="val">0.00 DH</td>
        </tr>
    </table>

    {{-- ══ BIG TOTAL BOX ══ --}}
    <div class="total-box">
        <span class="total-label">المجموع = Total</span>
        <span class="total-amount">{{ number_format($receipt->sale->total_amount, 2) }} DH</span>
    </div>

    {{-- ══ FOOTER ══ --}}
    <div class="footer">
        <p class="amount-words">
            {{-- Montant en toutes lettres: you can add a PHP helper here --}}
            Arrêté à la somme de : <strong>{{ number_format($receipt->sale->total_amount, 2) }} Dinars Algériens</strong>
        </p>
        <p class="thank-you">شكراً لزيارتكم! — Merci pour votre visite !</p>
        <p class="policy">البضاعة المباعة لا ترد ولا تستبدل بعد 7 أيام</p>
        <p class="policy">Articles vendus ni repris, ni échangés après 7 jours.</p>
        <p class="system-tag">Droguerie POS System</p>
    </div>

</div>

<span class="print-hint">🖨️ Impression automatique en cours…</span>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 350);
    });

    window.onafterprint = function () {
        window.location.href = '{{ route("pos.index") }}';
    };
</script>

</body>
</html>
