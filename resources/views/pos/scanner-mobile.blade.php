<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0f172a">
    <title>TechShope Scanner</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #0f172a;
            --surface: #1e293b;
            --surface-alt: #273348;
            --border: #334155;
            --accent: #6366f1;
            --accent-hover: #818cf8;
            --success: #22c55e;
            --error: #ef4444;
            --warning: #f59e0b;
            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --radius: 16px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-x: hidden;
        }

        /* ── Header ── */
        .header {
            width: 100%;
            padding: 16px 20px 12px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .header-logo {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--accent), #a855f7);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .header-info h1 {
            font-size: 15px; font-weight: 700; letter-spacing: -0.3px;
        }
        .header-info p {
            font-size: 11px; color: var(--text-muted); margin-top: 1px;
        }
        .status-dot {
            margin-left: auto;
            width: 10px; height: 10px;
            border-radius: 50%;
            background: var(--warning);
            box-shadow: 0 0 6px var(--warning);
            transition: all 0.3s;
            flex-shrink: 0;
        }
        .status-dot.ready   { background: var(--success); box-shadow: 0 0 8px var(--success); }
        .status-dot.error   { background: var(--error);   box-shadow: 0 0 8px var(--error); }

        /* ── Main ── */
        .main {
            flex: 1;
            width: 100%;
            max-width: 480px;
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* ── Camera Viewfinder ── */
        .viewfinder-wrap {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: var(--radius);
            overflow: hidden;
            background: #000;
            border: 2px solid var(--border);
        }
        #video {
            width: 100%; height: 100%;
            object-fit: cover;
            display: block;
        }
        /* Scanning overlay */
        .scan-overlay {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            pointer-events: none;
        }
        .scan-frame {
            width: 65%; height: 65%;
            position: relative;
        }
        .scan-frame::before, .scan-frame::after,
        .scan-frame .c3, .scan-frame .c4 {
            content: '';
            position: absolute;
            width: 28px; height: 28px;
            border-color: var(--accent);
            border-style: solid;
        }
        .scan-frame::before  { top: 0;    left: 0;  border-width: 3px 0 0 3px; border-radius: 6px 0 0 0; }
        .scan-frame::after   { top: 0;    right: 0; border-width: 3px 3px 0 0; border-radius: 0 6px 0 0; }
        .scan-frame .c3      { bottom: 0; left: 0;  border-width: 0 0 3px 3px; border-radius: 0 0 0 6px; }
        .scan-frame .c4      { bottom: 0; right: 0; border-width: 0 3px 3px 0; border-radius: 0 0 6px 0; }
        /* Scanning laser line */
        .scan-line {
            position: absolute;
            left: 17%;
            right: 17%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            border-radius: 2px;
            animation: scan-sweep 2.2s ease-in-out infinite;
            box-shadow: 0 0 8px var(--accent);
        }
        @keyframes scan-sweep {
            0%   { top: 18%; opacity: 0.6; }
            50%  { top: 82%; opacity: 1;   }
            100% { top: 18%; opacity: 0.6; }
        }
        /* Viewfinder idle state */
        .viewfinder-idle {
            position: absolute; inset: 0;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 12px;
            background: var(--surface);
        }
        .viewfinder-idle .idle-icon {
            font-size: 48px;
            opacity: 0.5;
        }
        .viewfinder-idle p {
            font-size: 13px;
            color: var(--text-muted);
            text-align: center;
            padding: 0 20px;
        }

        /* ── Toast notification ── */
        #toast {
            position: fixed;
            bottom: 24px; left: 50%;
            transform: translateX(-50%) translateY(80px);
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 600;
            z-index: 100;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            max-width: 88vw;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }
        #toast.show { transform: translateX(-50%) translateY(0); }
        #toast.success { border-color: var(--success); color: var(--success); }
        #toast.error   { border-color: var(--error);   color: var(--error); }
        #toast.info    { border-color: var(--accent);  color: var(--accent-hover); }

        /* ── Buttons ── */
        .btn {
            width: 100%;
            padding: 16px 20px;
            border-radius: 14px;
            border: none;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.18s;
            display: flex; align-items: center; justify-content: center;
            gap: 10px;
            letter-spacing: -0.2px;
        }
        .btn:active { transform: scale(0.97); }
        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #7c3aed);
            color: white;
            box-shadow: 0 4px 20px rgba(99,102,241,0.4);
        }
        .btn-primary:hover { box-shadow: 0 6px 28px rgba(99,102,241,0.55); }
        .btn-secondary {
            background: var(--surface-alt);
            color: var(--text);
            border: 1px solid var(--border);
        }
        .btn-danger {
            background: rgba(239,68,68,0.15);
            color: var(--error);
            border: 1px solid rgba(239,68,68,0.3);
        }

        /* ── Manual input ── */
        .manual-section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px;
        }
        .manual-section label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 10px;
        }
        .manual-row {
            display: flex; gap: 8px;
        }
        .manual-input {
            flex: 1;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 16px;
            color: var(--text);
            outline: none;
            transition: border-color 0.2s;
        }
        .manual-input:focus {
            border-color: var(--accent);
        }
        .manual-input::placeholder { color: var(--text-muted); }
        .manual-send {
            padding: 12px 16px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.18s;
            flex-shrink: 0;
        }
        .manual-send:active { transform: scale(0.95); }

        /* ── History ── */
        .history-section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px;
        }
        .history-section h3 {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 10px;
        }
        .history-list {
            display: flex; flex-direction: column; gap: 6px;
            max-height: 160px;
            overflow-y: auto;
        }
        .history-item {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 8px 12px;
            display: flex; align-items: center; gap: 10px;
            font-size: 13px;
        }
        .history-item .hi-barcode { flex: 1; font-family: monospace; font-weight: 600; }
        .history-item .hi-time { font-size: 11px; color: var(--text-muted); flex-shrink: 0; }
        .history-item .hi-status {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--success);
            flex-shrink: 0;
        }
        .history-empty {
            font-size: 13px;
            color: var(--text-muted);
            text-align: center;
            padding: 8px 0;
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="header">
    <div class="header-logo">📷</div>
    <div class="header-info">
        <h1>TechShope Scanner</h1>
        <p id="status-text">Initialisation…</p>
    </div>
    <div class="status-dot" id="status-dot"></div>
</header>

<!-- Main Content -->
<main class="main">

    <!-- Viewfinder -->
    <div class="viewfinder-wrap">
        <video id="video" autoplay playsinline muted></video>
        <!-- Shown when camera is running -->
        <div class="scan-overlay" id="scan-overlay" style="display:none;">
            <div class="scan-frame">
                <span class="c3"></span>
                <span class="c4"></span>
            </div>
            <div class="scan-line"></div>
        </div>
        <!-- Shown when idle -->
        <div class="viewfinder-idle" id="viewfinder-idle">
            <div class="idle-icon">📷</div>
            <p>Appuyez sur "Démarrer" pour activer la caméra et scanner un code-barres.</p>
        </div>
    </div>

    <!-- Camera Controls -->
    <button class="btn btn-primary" id="btn-start">
        <span>▶</span> Démarrer la caméra
    </button>
    <button class="btn btn-danger" id="btn-stop" style="display:none;">
        <span>■</span> Arrêter
    </button>

    <!-- Manual Input -->
    <div class="manual-section">
        <label>Saisie manuelle du code-barres</label>
        <div class="manual-row">
            <input type="text"
                   class="manual-input"
                   id="manual-input"
                   placeholder="ex: 6111245678901"
                   inputmode="numeric"
                   autocomplete="off"
                   spellcheck="false">
            <button class="manual-send" id="btn-manual-send" title="Envoyer">➤</button>
        </div>
    </div>

    <!-- History -->
    <div class="history-section">
        <h3>Derniers scans</h3>
        <div class="history-list" id="history-list">
            <div class="history-empty">Aucun scan pour l'instant</div>
        </div>
    </div>

</main>

<!-- Toast -->
<div id="toast"></div>

<script>
(function () {
    'use strict';

    // ── Config ────────────────────────────────────────────────────────────────
    const TOKEN      = '{{ $token }}';
    const PUSH_URL   = '{{ route("pos.scanner.push") }}';
    const CSRF_TOKEN = ''; // no session needed on mobile push endpoint

    // ── State ─────────────────────────────────────────────────────────────────
    let stream        = null;   // MediaStream
    let scanning      = false;
    let scanInterval  = null;
    let history       = [];
    let lastBarcode   = null;
    let lastScanTime  = 0;
    const DEBOUNCE_MS = 2000;

    // ── DOM refs ──────────────────────────────────────────────────────────────
    const video         = document.getElementById('video');
    const scanOverlay   = document.getElementById('scan-overlay');
    const viewfinderIdle= document.getElementById('viewfinder-idle');
    const btnStart      = document.getElementById('btn-start');
    const btnStop       = document.getElementById('btn-stop');
    const manualInput   = document.getElementById('manual-input');
    const btnManualSend = document.getElementById('btn-manual-send');
    const historyList   = document.getElementById('history-list');
    const statusText    = document.getElementById('status-text');
    const statusDot     = document.getElementById('status-dot');
    const toast         = document.getElementById('toast');

    // ── Toast ─────────────────────────────────────────────────────────────────
    let toastTimer;
    function showToast(msg, type = 'info', duration = 3000) {
        toast.textContent = msg;
        toast.className   = 'show ' + type;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { toast.className = ''; }, duration);
    }

    // ── Status ────────────────────────────────────────────────────────────────
    function setStatus(text, dotClass = '') {
        statusText.textContent = text;
        statusDot.className    = 'status-dot ' + dotClass;
    }

    // ── History ───────────────────────────────────────────────────────────────
    function addToHistory(barcode, ok) {
        history.unshift({ barcode, ok, time: new Date() });
        if (history.length > 10) history.pop();
        renderHistory();
    }
    function renderHistory() {
        if (!history.length) {
            historyList.innerHTML = '<div class="history-empty">Aucun scan pour l\'instant</div>';
            return;
        }
        historyList.innerHTML = history.map(h => {
            const t = h.time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            return `<div class="history-item">
                <div class="hi-status" style="background:${h.ok ? 'var(--success)' : 'var(--error)'}"></div>
                <div class="hi-barcode">${escHtml(h.barcode)}</div>
                <div class="hi-time">${t}</div>
            </div>`;
        }).join('');
    }
    function escHtml(str) {
        return str.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    // ── Push barcode to server ────────────────────────────────────────────────
    async function pushBarcode(barcode) {
        const now = Date.now();
        if (barcode === lastBarcode && (now - lastScanTime) < DEBOUNCE_MS) return;
        lastBarcode  = barcode;
        lastScanTime = now;

        try {
            const res = await fetch(PUSH_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ token: TOKEN, barcode }),
            });
            const ok = res.ok;
            addToHistory(barcode, ok);
            showToast(ok ? '✅ Code envoyé : ' + barcode : '❌ Erreur envoi', ok ? 'success' : 'error');
            if (ok) vibrate();
        } catch (err) {
            addToHistory(barcode, false);
            showToast('❌ Réseau indisponible', 'error');
        }
    }

    function vibrate() {
        if ('vibrate' in navigator) navigator.vibrate([80, 30, 80]);
    }

    // ── Camera & scanning ─────────────────────────────────────────────────────

    /**
     * Try BarcodeDetector (Chrome on Android) then fall back to ZXing WASM.
     */
    async function startCamera() {
        try {
            const constraints = {
                video: {
                    facingMode: { ideal: 'environment' },
                    width:  { ideal: 1280 },
                    height: { ideal: 720 },
                },
            };
            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;
            await video.play();

            scanning = true;
            viewfinderIdle.style.display = 'none';
            scanOverlay.style.display    = 'flex';
            btnStart.style.display       = 'none';
            btnStop.style.display        = 'block';
            setStatus('Caméra active — scannez', 'ready');

            if (typeof BarcodeDetector !== 'undefined') {
                startNativeDetector();
            } else {
                await startZxingDetector();
            }
        } catch (err) {
            setStatus('Erreur caméra', 'error');
            showToast('❌ Accès caméra refusé : ' + err.message, 'error', 5000);
        }
    }

    // Native BarcodeDetector (Chrome Android ≥ 83)
    function startNativeDetector() {
        // eslint-disable-next-line no-undef
        const detector = new BarcodeDetector({
            formats: ['ean_13', 'ean_8', 'code_128', 'code_39', 'qr_code', 'upc_a', 'upc_e', 'itf', 'data_matrix'],
        });

        scanInterval = setInterval(async () => {
            if (!scanning || video.readyState < 2) return;
            try {
                const barcodes = await detector.detect(video);
                if (barcodes.length > 0) {
                    await pushBarcode(barcodes[0].rawValue);
                }
            } catch (_) { /* ignore decode errors */ }
        }, 400);
    }

    // ZXing fallback (loaded from CDN)
    async function startZxingDetector() {
        // Load ZXing dynamically only when needed
        if (!window.ZXing) {
            setStatus('Chargement ZXing…', '');
            await loadScript('https://cdn.jsdelivr.net/npm/@zxing/library@0.21.3/umd/index.min.js');
        }

        // eslint-disable-next-line no-undef
        const hints = new Map();
        // eslint-disable-next-line no-undef
        const formats = [
            ZXing.BarcodeFormat.EAN_13, ZXing.BarcodeFormat.EAN_8,
            ZXing.BarcodeFormat.CODE_128, ZXing.BarcodeFormat.CODE_39,
            ZXing.BarcodeFormat.QR_CODE, ZXing.BarcodeFormat.UPC_A,
            ZXing.BarcodeFormat.DATA_MATRIX, ZXing.BarcodeFormat.ITF,
        ];
        // eslint-disable-next-line no-undef
        hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, formats);

        const canvas  = document.createElement('canvas');
        // eslint-disable-next-line no-undef
        const reader  = new ZXing.MultiFormatReader();
        reader.setHints(hints);

        setStatus('Caméra active (ZXing) — scannez', 'ready');

        scanInterval = setInterval(() => {
            if (!scanning || video.readyState < 2) return;
            try {
                canvas.width  = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                // eslint-disable-next-line no-undef
                const lum   = new ZXing.RGBLuminanceSource(
                    ctx.getImageData(0, 0, canvas.width, canvas.height).data,
                    canvas.width, canvas.height
                );
                // eslint-disable-next-line no-undef
                const bmp   = new ZXing.BinaryBitmap(new ZXing.HybridBinarizer(lum));
                const result = reader.decode(bmp);
                pushBarcode(result.getText());
            } catch (_) { /* NotFound is normal between frames */ }
        }, 350);
    }

    function loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) { resolve(); return; }
            const s = document.createElement('script');
            s.src = src; s.onload = resolve; s.onerror = reject;
            document.head.appendChild(s);
        });
    }

    function stopCamera() {
        scanning = false;
        clearInterval(scanInterval);
        scanInterval = null;

        if (stream) {
            stream.getTracks().forEach(t => t.stop());
            stream = null;
        }
        video.srcObject = null;

        viewfinderIdle.style.display = 'flex';
        scanOverlay.style.display    = 'none';
        btnStart.style.display       = 'block';
        btnStop.style.display        = 'none';
        setStatus('Caméra arrêtée', '');
    }

    // ── Event Listeners ───────────────────────────────────────────────────────
    btnStart.addEventListener('click', startCamera);
    btnStop.addEventListener('click', stopCamera);

    btnManualSend.addEventListener('click', () => {
        const val = manualInput.value.trim();
        if (val) { pushBarcode(val); manualInput.value = ''; }
    });

    manualInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const val = manualInput.value.trim();
            if (val) { pushBarcode(val); manualInput.value = ''; }
        }
    });

    // Stop camera when leaving page to free camera resource
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && scanning) stopCamera();
    });

    // ── Init ──────────────────────────────────────────────────────────────────
    setStatus('Prêt — appuyez sur Démarrer', '');
    renderHistory();
})();
</script>
</body>
</html>
