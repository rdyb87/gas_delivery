@extends('layouts.app')

@section('title', 'QR Code Scanner')
@section('page_title', 'QR Code Scanner')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">QR Code Scanner</h1>
            <p class="text-gray-600 mt-1">Scan customer QR code to view and complete deliveries</p>
        </div>
        <i class="fas fa-qrcode text-5xl text-blue-600"></i>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-4">
        <div class="flex items-center justify-between">
            <span class="font-medium text-gray-700">Scan Mode:</span>
            <button id="toggleMode" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                📱 Switch to Camera
            </button>
        </div>
    </div>

    <div id="manualMode" class="bg-white rounded-xl shadow-lg p-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Manual Input <span class="text-gray-500">(For hand terminal scanner)</span>
        </label>
        <div class="flex gap-3">
            <input id="qrInput" type="text" placeholder="GASDELIVERY|CUSTXXXX|Name" autocomplete="off"
                   class="flex-1 px-4 py-3 text-lg border-2 border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <button id="scanBtn" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 font-medium">
                Scan
            </button>
        </div>
        <div id="manualError" class="hidden mt-4 p-4 bg-red-50 text-red-700 rounded-lg flex items-center gap-2"></div>
    </div>

    <div id="cameraMode" class="hidden bg-white rounded-xl shadow-lg p-6">
        <div class="text-center">
            <button id="startCamera" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                📷 Start Camera Scanner
            </button>
            <p class="text-sm text-gray-500 mt-2">Click to activate camera and scan QR codes</p>
        </div>
        <div id="camera-reader" class="w-full rounded-lg overflow-hidden mt-4 hidden"></div>
        <div id="stopCameraWrap" class="text-center mt-4 hidden">
            <button id="stopCamera" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">⏹️ Stop Camera</button>
        </div>
    </div>

    <div id="scanResult" class="hidden space-y-4">
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl shadow-lg p-6 border-2 border-blue-300">
            <div class="flex items-start justify-between">
                <div>
                    <h2 id="resultName" class="text-2xl font-bold text-gray-900"></h2>
                    <p id="resultCode" class="text-blue-600 font-medium mt-1"></p>
                </div>
                <i class="fas fa-check-circle text-green-600 text-3xl"></i>
            </div>
            <div id="resultDetails" class="mt-4 grid grid-cols-2 gap-4"></div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 id="pendingTitle" class="text-xl font-bold text-gray-900 mb-4"></h3>
            <div id="pendingList" class="space-y-3"></div>
        </div>
    </div>
</div>
@endsection

@push('head')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endpush

@push('scripts')
<script>
    let scanMode = 'manual';
    let scanner = null;
    const BASE = '{{ route('scanner.scan') }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const manualMode = document.getElementById('manualMode');
    const cameraMode = document.getElementById('cameraMode');
    const scanResult = document.getElementById('scanResult');
    const toggleMode = document.getElementById('toggleMode');

    toggleMode.addEventListener('click', () => {
        if (scanMode === 'manual') {
            scanMode = 'camera';
            manualMode.classList.add('hidden');
            cameraMode.classList.remove('hidden');
            toggleMode.textContent = '⌨️ Switch to Manual Input';
        } else {
            scanMode = 'manual';
            cameraMode.classList.add('hidden');
            manualMode.classList.remove('hidden');
            toggleMode.textContent = '📱 Switch to Camera';
            stopScanner();
        }
    });

    document.getElementById('scanBtn').addEventListener('click', () => {
        const value = document.getElementById('qrInput').value.trim();
        if (!value) return;
        doScan(value);
    });

    document.getElementById('qrInput').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('scanBtn').click();
        }
    });

    function showManualError(msg) {
        const el = document.getElementById('manualError');
        el.textContent = msg;
        el.classList.remove('hidden');
    }

    async function doScan(qrData) {
        try {
            const res = await fetch(BASE, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ qr_data: qrData }),
            });
            const data = await res.json();
            if (!data.success) {
                showManualError(data.message || 'Scan failed');
                return;
            }
            showManualError('');
            renderResult(data);
        } catch (err) {
            showManualError('Error scanning QR code');
        }
    }

    function renderResult(data) {
        const customer = data.customer;
        scanResult.classList.remove('hidden');

        document.getElementById('resultName').textContent = customer.name;
        document.getElementById('resultCode').textContent = customer.customer_code;
        document.getElementById('resultDetails').innerHTML = `
            <div><p class="text-sm text-gray-600">Phone</p><p class="font-medium">${customer.phone || 'N/A'}</p></div>
            <div><p class="text-sm text-gray-600">Type</p><p class="font-medium">${customer.dealer_type || 'N/A'}</p></div>
            <div class="col-span-2"><p class="text-sm text-gray-600">Address</p><p class="font-medium">${customer.address || 'N/A'}</p></div>`;

        const deliveries = data.deliveries || [];
        document.getElementById('pendingTitle').textContent = `Pending Deliveries (${deliveries.length})`;
        const list = document.getElementById('pendingList');

        if (deliveries.length === 0) {
            list.innerHTML = `<div class="text-center py-8 text-gray-500"><i class="fas fa-box text-4xl mb-2 text-gray-400"></i><p>No pending deliveries for this customer</p></div>`;
        } else {
            list.innerHTML = deliveries.map(d => {
                const items = (d.items || []).map(i => `<div>• ${i.quantity}x ${i.cylinder_type}</div>`).join('');
                const badge = d.status === 'assigned' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800';
                return `
                <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="font-bold text-gray-900">${d.delivery_code}</span>
                                <span class="px-2 py-1 text-xs font-medium rounded-full ${badge}">${d.status.replace('_', ' ').toUpperCase()}</span>
                            </div>
                            <div class="space-y-1 mb-3">
                                <p class="text-sm text-gray-600"><strong>Date:</strong> ${d.delivery_date || 'N/A'}</p>
                                <div class="text-sm text-gray-600"><strong>Items:</strong><div class="ml-2">${items}</div></div>
                                ${d.special_instructions ? `<p class="text-sm text-gray-600"><strong>Instructions:</strong> ${d.special_instructions}</p>` : ''}
                            </div>
                        </div>
                        <button onclick="completeDelivery(${d.id})" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium flex items-center gap-2 whitespace-nowrap">
                            <i class="fas fa-check-circle"></i> Complete
                        </button>
                    </div>
                </div>`;
            }).join('');
        }
    }

    async function completeDelivery(id) {
        try {
            const res = await fetch(`/deliveries/${id}/complete`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.success) {
                alert('Delivery completed successfully!');
                // refresh
                const code = document.getElementById('resultCode').textContent;
                const name = document.getElementById('resultName').textContent;
                doScan(`GASDELIVERY|${code}|${name}`);
            } else {
                alert(data.message || 'Error completing delivery');
            }
        } catch (err) {
            alert('Error completing delivery');
        }
    }

    async function startScanner() {
        try {
            document.getElementById('startCamera').classList.add('hidden');
            const reader = document.getElementById('camera-reader');
            reader.classList.remove('hidden');
            scanner = new Html5Qrcode('camera-reader');
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 },
                async (decodedText) => {
                    await stopScanner();
                    doScan(decodedText.trim());
                },
                () => {}
            );
            document.getElementById('stopCameraWrap').classList.remove('hidden');
        } catch (err) {
            alert('Failed to start camera. Please check camera permissions.');
            document.getElementById('startCamera').classList.remove('hidden');
        }
    }

    async function stopScanner() {
        if (scanner) {
            try { await scanner.stop(); scanner.clear(); } catch (e) {}
            scanner = null;
        }
        document.getElementById('startCamera').classList.remove('hidden');
        document.getElementById('stopCameraWrap').classList.add('hidden');
        document.getElementById('camera-reader').classList.add('hidden');
    }

    document.getElementById('startCamera').addEventListener('click', startScanner);
    document.getElementById('stopCamera').addEventListener('click', stopScanner);
</script>
@endpush