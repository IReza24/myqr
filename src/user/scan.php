<?php
$page_title = "Scan QR Code";
require_once __DIR__ . '/../layouts/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
?>

<style>
    #reader {
        width: 100%;
        border-radius: 0.5rem; /* 8px */
        overflow: hidden;
        border: 0;
    }
    #reader video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    #reader__dashboard_section_swaplink { display: none !important; }

    .viewfinder-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        z-index: 10;
    }
    .viewfinder-corner {
        position: absolute;
        width: 40px;
        height: 40px;
        border: 6px solid white;
        border-radius: 4px;
        box-shadow: 0 0 15px rgba(0,0,0,0.5);
    }
    .top-left { top: 20px; left: 20px; border-right: none; border-bottom: none; }
    .top-right { top: 20px; right: 20px; border-left: none; border-bottom: none; }
    .bottom-left { bottom: 20px; left: 20px; border-right: none; border-top: none; }
    .bottom-right { bottom: 20px; right: 20px; border-left: none; border-top: none; }

    .scanner-laser {
        position: absolute;
        left: 5%;
        width: 90%;
        height: 3px;
        background: #3b82f6;
        box-shadow: 0 0 10px #3b82f6, 0 0 20px #3b82f6;
        animation: scan 3s linear infinite;
        display: none; /* Initially hidden */
        z-index: 20;
        border-radius: 2px;
    }
    @keyframes scan {
        0% { top: 10%; }
        50% { top: 90%; }
        100% { top: 10%; }
    }
</style>

<div class="max-w-xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-slate-800">Pindai Kode QR Absensi</h1>
            <p class="text-slate-500 mt-1">Absen sebagai: <span class="font-semibold"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></span></p>
        </div>

        <div id="reader-container" class="relative bg-gray-900">
            <div id="reader"></div>
            <div class="viewfinder-overlay">
                <div class="viewfinder-corner top-left"></div>
                <div class="viewfinder-corner top-right"></div>
                <div class="viewfinder-corner bottom-left"></div>
                <div class="viewfinder-corner bottom-right"></div>
                <div class="scanner-laser"></div>
            </div>
        </div>

        <div id="manual-form-container" class="hidden p-6">
            <form action="proses_absensi_manual.php" method="POST">
                <label for="token-manual" class="block text-sm font-medium text-slate-700">Kode Token</label>
                <input type="text" name="token" id="token-manual" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Masukkan kode dari QR...">
                <button type="submit" class="mt-4 w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Kirim Absensi</button>
            </form>
        </div>

        <div id="status-container" class="p-6 text-center bg-gray-50 border-t border-gray-200">
            <p id="status-text" class="text-slate-600 font-medium">Arahkan QR code ke dalam area pindai</p>
        </div>
    </div>

    <div class="mt-6 text-center">
        <p id="toggle-text" class="text-slate-600">Kamera tidak berfungsi? 
            <a href="#" id="manual-toggle" class="text-blue-600 hover:underline font-medium">Masukkan kode manual</a>
        </p>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const statusText = document.getElementById('status-text');
        const laser = document.querySelector('.scanner-laser');
        const manualToggle = document.getElementById('manual-toggle');
        const toggleText = document.getElementById('toggle-text');
        
        const readerContainer = document.getElementById('reader-container');
        const manualFormContainer = document.getElementById('manual-form-container');
        const statusContainer = document.getElementById('status-container');

        let isProcessing = false;
        let isManualMode = false;
        let html5QrcodeScanner;

        function showNotification(message, type = 'success') {
            const bgColor = type === 'success' ? 'linear-gradient(to right, #00b09b, #96c93d)' : 'linear-gradient(to right, #ff5f6d, #ffc371)';
            Toastify({ text: message, duration: 3000, close: true, gravity: "bottom", position: "right", stopOnFocus: true, style: { background: bgColor, borderRadius: '8px' } }).showToast();
        }

        async function processQRCode(token) {
            // (Logic proses ini sama seperti sebelumnya, tidak perlu diubah)
            if (isProcessing) return;
            isProcessing = true;
            statusText.textContent = 'Memproses kode...';
            statusText.classList.add('text-blue-600');

            try {
                const response = await fetch('proses_absensi.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ token: token }) });
                const result = await response.json();
                
                if (result.success) {
                    statusText.textContent = 'Absensi berhasil!';
                    statusText.classList.add('text-green-600');
                    showNotification('Absensi berhasil dicatat! Anda akan dialihkan.', 'success');
                    setTimeout(() => { window.location.href = 'index.php'; }, 2000);
                } else {
                    statusText.textContent = result.message || 'Kode tidak valid.';
                    statusText.classList.add('text-red-600');
                    showNotification(result.message || 'Kode tidak valid.', 'error');
                    setTimeout(() => {
                        isProcessing = false;
                        statusText.textContent = 'Arahkan QR code ke dalam area pindai';
                        statusText.classList.remove('text-red-600');
                        if (!isManualMode) { try { if(html5QrcodeScanner.getState() === Html5QrcodeScannerState.PAUSED) html5QrcodeScanner.resume(); } catch(e){} }
                    }, 3000);
                }
            } catch (error) {
                showNotification('Terjadi kesalahan jaringan. Coba lagi.', 'error');
                setTimeout(() => { isProcessing = false; }, 3000);
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return;
            html5QrcodeScanner.pause();
            laser.style.display = 'none';
            processQRCode(decodedText);
        }

        function onScanFailure(error) { /* Abaikan */ }

        function startScanner() {
            if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
                html5QrcodeScanner.clear();
            }
            
            html5QrcodeScanner = new Html5QrcodeScanner("reader", {
                fps: 10,
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                    return { width: Math.floor(minEdgeSize * 0.85), height: Math.floor(minEdgeSize * 0.85) };
                },
                rememberLastUsedCamera: true,
            }, false);

            statusText.textContent = 'Mempersiapkan kamera...';
            readerContainer.style.display = 'block';
            manualFormContainer.style.display = 'none';
            statusContainer.style.display = 'block';
            isManualMode = false;

            html5QrcodeScanner.render(onScanSuccess, onScanFailure);

            const videoCheckInterval = setInterval(() => {
                const videoElement = document.querySelector('#reader video');
                if (videoElement && videoElement.readyState >= 3) {
                    laser.style.display = 'block';
                    statusText.textContent = 'Arahkan QR code ke dalam area pindai';
                    clearInterval(videoCheckInterval);
                }
            }, 500);
        }

        function stopScanner() {
            if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
                html5QrcodeScanner.clear().catch(err => console.error("Gagal membersihkan scanner:", err));
            }
            readerContainer.style.display = 'none';
            manualFormContainer.style.display = 'block';
            statusContainer.style.display = 'none';
            isManualMode = true;
        }

        manualToggle.addEventListener('click', function(e) {
            e.preventDefault();
            if (isManualMode) {
                startScanner();
                toggleText.innerHTML = 'Kamera tidak berfungsi? <a href="#" id="manual-toggle" class="text-blue-600 hover:underline font-medium">Masukkan kode manual</a>';
            } else {
                stopScanner();
                toggleText.innerHTML = 'Tidak jadi? <a href="#" id="manual-toggle" class="text-blue-600 hover:underline font-medium">Gunakan kamera untuk scan</a>';
            }
            // Re-bind event listener to the new link
            document.getElementById('manual-toggle').addEventListener('click', arguments.callee);
        });

        // Mulai scanner saat halaman dimuat
        startScanner();
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>