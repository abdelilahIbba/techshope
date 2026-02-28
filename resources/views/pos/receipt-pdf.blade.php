<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu #{{ $receipt->receipt_number }}</title>
    {{-- خط Tajawal: يدعم العربية والفرنسية ويُجبر الطابعة على Graphics Mode --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>
        /* إعدادات عامة تتوافق مع محركات الـ PDF */
        body { 
            /* Tajawal: يدعم العربية + الفرنسية — يُجبر محرك PDF على تضمين الخط */
            font-family: 'Tajawal', Arial, sans-serif; 
            font-size: 12px; 
            color: #000000; 
            margin: 0; 
            padding: 10px;
            line-height: 1.4;
        }

        /* رأس الفاتورة (Header) */
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 24px;
            color: #0f172a;
            letter-spacing: 1px;
        }
        .header p {
            margin: 2px 0;
            color: #64748b;
            font-size: 11px;
        }

        /* الخط الفاصل */
        .divider {
            border-bottom: 1px dashed #cbd5e1;
            margin: 15px 0;
        }

        /* معلومات الفاتورة */
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .meta-table td {
            border: none;
            padding: 0;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* جدول المنتجات */
        .items-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        .items-table th { 
            background-color: #f8fafc;
            color: #475569;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 2px solid #e2e8f0;
            padding: 8px 4px;
        }
        .items-table td { 
            border-bottom: 1px solid #f1f5f9; 
            padding: 8px 4px; 
            vertical-align: top;
        }
        
        .product-name {
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 3px;
        }
        .barcode-text {
            font-size: 9px;
            color: #94a3b8;
        }
        .barcode-img { 
            margin-top: 4px; 
            max-width: 120px; 
            height: auto;
        }

        /* الإجماليات */
        .totals-container {
            width: 100%;
            margin-top: 20px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px 0;
            border: none;
        }
        .grand-total {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            border-top: 2px solid #1e293b;
            padding-top: 10px;
        }

        /* تذييل الفاتورة */
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 11px;
            color: #64748b;
        }
        .qr-placeholder {
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Droguerie S</h1>
        <p>Quincaillerie & Articles de bricolage</p>
        <p>123 Rue de la République, Ville</p>
        <p>Tél: +213 XX XX XX XX</p>
    </div>

    <div class="divider"></div>

    <table class="meta-table">
        <tr>
            <td>
                <strong>Ticket N° :</strong> {{ $receipt->receipt_number }}<br>
                <strong>Caisse :</strong> Principal
            </td>
            <td class="text-right">
                <strong>Date :</strong> {{ optional($receipt->printed_at)->format('d/m/Y') }}<br>
                <strong>Heure :</strong> {{ optional($receipt->printed_at)->format('H:i') }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="text-align: left;">Article</th>
                <th class="text-center">Qté</th>
                <th class="text-right">Prix U.</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($receipt->sale->items as $item)
                <tr>
                    <td>
                        <div class="product-name">{{ $item->product->name }}</div>
                        <div class="barcode-text">Code: {{ $item->product->barcode }}</div>
                        @if (isset($barcodeImages[$item->product_id]))
                            <div>
                                <img class="barcode-img" src="data:image/svg+xml;base64,{{ $barcodeImages[$item->product_id] }}" alt="barcode">
                            </div>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-container">
        <table class="totals-table">
            <tr>
                <td style="text-align: right; width: 70%;">Sous-total :</td>
                <td class="text-right">{{ number_format($receipt->sale->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td style="text-align: right; width: 70%; padding-bottom: 10px;">TVA (0%) :</td>
                <td class="text-right" style="padding-bottom: 10px;">0.00</td>
            </tr>
            <tr>
                <td style="text-align: right; width: 70%;" class="grand-total">NET À PAYER :</td>
                <td class="text-right grand-total">{{ number_format($receipt->sale->total_amount, 2) }} DZD</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Merci pour votre visite !</p>
        <p>Les articles vendus ne sont ni repris ni échangés après 7 jours.</p>
        <p style="font-size: 9px; margin-top: 10px; color: #cbd5e1;">Propulsé par Droguerie POS System</p>
    </div>

</body>
</html>