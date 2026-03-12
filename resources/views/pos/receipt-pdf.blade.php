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
            color: #000;
            letter-spacing: 1px;
        }
        .header p {
            margin: 2px 0;
            color: #000000ff;
            font-size: 11px;
        }

        /* الخط الفاصل */
        .divider {
            border-bottom: 1px dashed #000;
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
        .text-rightt { text-align: left; }

        /* جدول المنتجات */
        .items-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        .items-table th { 
            background-color: #fff;
            color: #000000ff;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
            padding: 8px 4px;
        }
        .items-table td { 
            border-bottom: 1px solid #ccc; 
            padding: 8px 4px; 
            vertical-align: top;
        }
        
        .product-name {
            font-weight: bold;
            color: #000;
            margin-bottom: 3px;
        }
        .barcode-text {
            font-size: 9px;
            color: #000000ff;
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
            color: #000;
            border-top: 2px solid #000;
            padding-top: 10px;
        }

        /* تذييل الفاتورة */
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 11px;
            color: #000000ff;
        }
        .qr-placeholder {
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>D3 ALLOUACHE</h1>
        <p>Tél: +212 0669112486</p>
         <p>rue AHMED AL MOKRI</p>
    </div>

    <div class="divider"></div>

    <table class="meta-table">
        <tr>
            <td colspan="2"><strong>Ticket N° :</strong> {{ $receipt->receipt_number }}</td>
        </tr>
        <tr>
            <td class="text-rightt"><strong>Date :</strong> {{ optional($receipt->printed_at)->format('d/m/Y') }}</td>
            <td class="text-right"><strong>Heure :</strong> {{ optional($receipt->printed_at)->format('H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Caisse :</strong> Principal</td>
            <td class="text-right"><strong>Caissier :</strong> {{ $receipt->sale->cashier->name ?? 'Caissier' }}</td>
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
                    <td class="text-right">{{ number_format($item->unit_price, 2) }} {{ $currencySymbol }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($item->subtotal, 2) }} {{ $currencySymbol }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-container">
        <table class="totals-table">
            <tr>
                <td style="text-align: right; width: 70%;">Sous-total :</td>
                <td class="text-right">{{ number_format($receipt->sale->total_amount, 2) }} {{ $currencySymbol }}</td>
            </tr>
            <tr>
                <td style="text-align: right; width: 70%; padding-bottom: 10px;">TVA (0%) :</td>
                <td class="text-right" style="padding-bottom: 10px;">0.00 {{ $currencySymbol }}</td>
            </tr>
            <tr>
                <td style="text-align: right; width: 70%;" class="grand-total">NET À PAYER :</td>
                <td class="text-right grand-total">{{ number_format($receipt->sale->total_amount, 2) }} {{ $currencySymbol }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Merci</p>
       
        
    </div>

</body>
</html>