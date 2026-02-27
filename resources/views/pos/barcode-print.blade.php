<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>WD8220 — {{ $product->name }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── SCREEN ── */
        @media screen {
            body {
                font-family: Arial, sans-serif;
                background: #0f172a;
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 24px 16px;
                gap: 10px;
            }
            #info {
                background: #1e293b;
                border: 2px solid #f59e0b;
                border-radius: 10px;
                padding: 14px 20px;
                width: 100%;
                max-width: 440px;
                color: #fff;
                font-size: 12px;
                font-family: Arial, sans-serif;
            }
            #info h2 { color: #f59e0b; font-size: 13px; margin-bottom: 8px; }
            .p-ok  { background:#14532d; border:1px solid #22c55e; color:#bbf7d0; padding:7px 10px; border-radius:5px; margin-bottom:5px; font-weight:700; }
            .p-bad { background:#450a0a; border:1px solid #ef4444; color:#fca5a5; padding:7px 10px; border-radius:5px; font-weight:700; }
            #btn-print {
                background:#2563eb; color:#fff; border:none; border-radius:8px;
                padding:10px 0; font-size:14px; font-weight:700; cursor:pointer;
                width:100%; max-width:440px;
            }
            #labels-container { display:flex; flex-direction:column; align-items:center; gap:6px; }
            .label {
                background:#fff;
                width: 50mm; height: 30mm;
                border: 1px dashed #475569;
                display: flex; flex-direction: column;
                align-items: center; justify-content: center;
                padding: 1.5mm 2mm; gap: 0.8mm;
            }
        }

        /* ── Shared content styles (screen + print) ── */
        .label-name {
            font-family: Arial, sans-serif;
            font-size: 7pt; font-weight: bold;
            text-align: center; color: #000;
            direction: rtl; word-break: break-word;
            line-height: 1.2; width: 100%;
            max-height: 7mm; overflow: hidden;
        }
        .label-barcode { display:flex; justify-content:center; width:100%; }
        .label-barcode svg { width: 44mm; height: 14mm; display: block; }
        .label-code {
            font-family: 'Courier New', monospace;
            font-size: 6.5pt; text-align: center; color: #000;
            letter-spacing: 0.5px;
        }

        /* ── PRINT ── */
        @media print {
            @page {
                size: 50mm 30mm;   /* Exact physical label size — no tricks, no transforms */
                margin: 0;
            }

            html, body {
                background: #fff !important;
                width: 50mm !important;
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
            }

            #info, #btn-print { display: none !important; }
            #labels-container { display: block !important; }

            .label {
                width: 30mm;
                height: 20mm;
                padding: 1.5mm 3mm;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 0.8mm;
                page-break-after: always;
                break-after: page;
                overflow: hidden;
                transform: rotate(-90deg);
                transform-origin: center center;
            }

            .label:last-child {
                page-break-after: avoid;
                break-after: avoid;
            }
        }
    </style>
</head>
<body>

    <div id="info">
        <h2>⚠️ Sélectionner la bonne imprimante</h2>
        <div class="p-ok">✅ WD8220 — Imprimante ÉTIQUETTES</div>
        <div class="p-bad">❌ WD8260 — Imprimante TICKET (ne pas sélectionner)</div>
    </div>

    <button id="btn-print" onclick="window.print()">
        🖨️ Imprimer {{ $quantity }} étiquette(s) sur WD8220
    </button>

    <div id="labels-container">
        @for ($i = 0; $i < $quantity; $i++)
            <div class="label">
                <div class="label-name">{{ $product->name }}</div>
                <div class="label-barcode">{!! $svg !!}</div>
                <div class="label-code">{{ $product->barcode }}</div>
            </div>
        @endfor
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 500);
        });
    </script>
</body>
</html>
