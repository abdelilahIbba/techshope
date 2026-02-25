<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4; margin: 0; }
        html, body { margin: 0; padding: 0; font-family: DejaVu Sans, Arial, sans-serif; }
        /* Container keeps consistent top padding for the page */
        .container { padding: 6mm 0; }

        /* Ticket width: typical 80mm label centered on the page */
        .ticket { width: 80mm; margin: 0 auto; page-break-after: always; padding-top: 6mm; }

        /* Align contents horizontally and keep them top-aligned */
        .ticket-inner { text-align: center; }

        /* Barcode image fills ticket width, constrained height for crisp bars */
        .barcode-img img { display: block; margin: 0 auto; width: 100%; height: 64px; object-fit: contain; }

        /* Numeric code style directly under the barcode */
        .code-text { margin-top: 6px; font-size: 16pt; font-weight: 700; letter-spacing: 2px; text-align: center; }
    </style>
</head>
<body>
<div class="container">
@foreach($tickets as $ticket)
    <div class="ticket">
        <div class="ticket-inner">
            <div class="barcode-img">
                <img src="data:image/svg+xml;base64,{{ base64_encode($ticket['svg']) }}" alt="barcode">
            </div>
            <div class="code-text">{{ $ticket['code'] }}</div>
        </div>
    </div>
@endforeach
</div>
</body>
</html>