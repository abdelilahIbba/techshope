<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $receipt->receipt_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2, p { margin: 0 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #111; padding: 6px; text-align: left; }
        .right { text-align: right; }
        .barcode { margin-top: 4px; max-width: 180px; }
        .total { margin-top: 12px; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Customer Receipt</h2>
    <p>Receipt #: {{ $receipt->receipt_number }}</p>
    <p>Date: {{ optional($receipt->printed_at)->format('Y-m-d H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Barcode</th>
                <th class="right">Qty</th>
                <th class="right">Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($receipt->sale->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>
                        {{ $item->product->barcode }}
                        @if (isset($barcodeImages[$item->product_id]))
                            <div>
                                <img class="barcode" src="data:image/svg+xml;base64,{{ $barcodeImages[$item->product_id] }}" alt="barcode">
                            </div>
                        @endif
                    </td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        Grand Total: {{ number_format($receipt->sale->total_amount, 2) }}
    </div>
</body>
</html>