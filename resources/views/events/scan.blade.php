@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold">Imbas Tiket</h1>
            <p class="text-xs text-slate-500">{{ $event->title }}</p>
        </div>
        <a href="/dashboard" class="text-sm text-slate-600 border px-3 py-1 rounded bg-white">Tutup</a>
    </div>
    
    <div id="scan-container" class="bg-black p-1 rounded-xl overflow-hidden shadow-lg mb-6 relative" style="min-height: 320px;">
        <div id="reader" class="w-full h-full bg-black"></div>
        
        <!-- Overlay for visual guidance -->
        <div id="scan-overlay" class="absolute inset-0 pointer-events-none flex items-center justify-center hidden">
            <div class="w-64 h-64 border-2 border-white/50 rounded-lg relative">
                <div class="absolute top-0 left-0 w-4 h-4 border-t-4 border-l-4 border-blue-500 -mt-1 -ml-1"></div>
                <div class="absolute top-0 right-0 w-4 h-4 border-t-4 border-r-4 border-blue-500 -mt-1 -mr-1"></div>
                <div class="absolute bottom-0 left-0 w-4 h-4 border-b-4 border-l-4 border-blue-500 -mb-1 -ml-1"></div>
                <div class="absolute bottom-0 right-0 w-4 h-4 border-b-4 border-r-4 border-blue-500 -mb-1 -mr-1"></div>
            </div>
        </div>

        <!-- Loading/Error State -->
        <div id="scan-status" class="absolute inset-0 flex flex-col items-center justify-center text-white p-4 text-center bg-black/80 z-10">
            <div id="loading-spinner" class="animate-spin rounded-full h-10 w-10 border-b-2 border-white mb-3"></div>
            <p id="status-text">Sedang memulakan kamera...</p>
            <button id="retry-btn" class="mt-4 px-4 py-2 bg-blue-600 rounded text-sm hidden" onclick="startScanner()">Cuba Lagi</button>
        </div>
    </div>

    <div class="text-center">
        <p class="text-sm text-slate-600 mb-2">Halakan kamera ke QR Code tiket peserta.</p>
        
        <div class="mt-4 flex justify-center gap-2">
            <select id="camera-select" class="text-sm border rounded px-2 py-1 hidden"></select>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    let html5QrCode;
    let isScanning = false;

    function onScanSuccess(decodedText, decodedResult) {
        if (!isScanning) return; // Prevent multiple triggers
        
        // Play beep sound (optional)
        // const audio = new Audio('/sounds/beep.mp3');
        // audio.play().catch(e => {});

        stopScanner().then(() => {
            let code = decodedText;
            if (code.includes('/checkin/')) {
                const parts = code.split('/checkin/');
                code = parts[1];
            }
            // Redirect
            window.location.href = "/checkin/" + code + "?return_to=" + encodeURIComponent(window.location.href);
        });
    }

    function onScanFailure(error) {
        // Ignore frame read errors
    }

    async function stopScanner() {
        if (html5QrCode && isScanning) {
            isScanning = false;
            try {
                await html5QrCode.stop();
            } catch (e) {
                console.error("Failed to stop", e);
            }
        }
    }

    async function startScanner(cameraId = null) {
        const statusEl = document.getElementById('scan-status');
        const statusText = document.getElementById('status-text');
        const retryBtn = document.getElementById('retry-btn');
        const spinner = document.getElementById('loading-spinner');
        const overlay = document.getElementById('scan-overlay');

        statusEl.classList.remove('hidden');
        spinner.classList.remove('hidden');
        retryBtn.classList.add('hidden');
        statusText.innerText = "Memulakan kamera...";
        
        try {
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("reader");
            }

            const config = { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0,
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true
                }
            };

            // If specific camera selected
            if (cameraId) {
                await html5QrCode.start(cameraId, config, onScanSuccess, onScanFailure);
            } else {
                // Try environment facing camera first
                try {
                    await html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, onScanFailure);
                } catch (err) {
                    console.warn("Environment camera failed, trying user facing or any available", err);
                    // Fallback to any camera
                    await html5QrCode.start({ facingMode: "user" }, config, onScanSuccess, onScanFailure);
                }
            }

            // Success
            isScanning = true;
            statusEl.classList.add('hidden');
            overlay.classList.remove('hidden');
            
            // Populate camera list if empty
            const cameraSelect = document.getElementById('camera-select');
            if (cameraSelect.options.length === 0) {
                const devices = await Html5Qrcode.getCameras();
                if (devices && devices.length > 0) {
                    cameraSelect.classList.remove('hidden');
                    // Add default option
                    const defaultOpt = document.createElement('option');
                    defaultOpt.value = "";
                    defaultOpt.text = "Tukar Kamera";
                    cameraSelect.appendChild(defaultOpt);

                    devices.forEach(device => {
                        const option = document.createElement('option');
                        option.value = device.id;
                        option.text = device.label || `Camera ${device.id}`;
                        cameraSelect.appendChild(option);
                    });

                    cameraSelect.onchange = (e) => {
                        if (e.target.value) {
                            stopScanner().then(() => startScanner(e.target.value));
                        }
                    };
                }
            }

        } catch (err) {
            console.error("Camera start error", err);
            isScanning = false;
            statusText.innerText = "Gagal memulakan kamera. Sila pastikan anda memberi kebenaran kamera.";
            if (err.name === 'NotAllowedError') {
                statusText.innerText = "Akses kamera ditolak. Sila benarkan akses di tetapan pelayar anda.";
            } else if (err.name === 'NotFoundError') {
                statusText.innerText = "Tiada kamera dikesan pada peranti ini.";
            } else if (String(err).includes("Timeout")) {
                 statusText.innerText = "Masa tamat. Sila muat semula halaman atau cuba lagi.";
            }
            
            spinner.classList.add('hidden');
            retryBtn.classList.remove('hidden');
        }
    }

    // Start on load
    document.addEventListener('DOMContentLoaded', () => {
        // Slight delay to ensure DOM is ready and layout is stable
        setTimeout(() => startScanner(), 500);
    });
    
    // Clean up
    window.addEventListener('beforeunload', () => {
        stopScanner();
    });
</script>
@endsection
