@extends('layouts.admin')

@section('title', 'Escáner QR y Códigos de Barras')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Encabezado -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-2">
                <span>Consulta Rápida de Inventario</span>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Escáner de Laptops & Periféricos</h2>
            <p class="text-slate-500 text-sm mt-1">
                Compatible con lectores USB de código de barras, cámara de celular / laptop o búsqueda manual.
            </p>
        </div>

        <a href="{{ route('admin.inventory.index') }}" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Volver al Stock</span>
        </a>
    </div>

    <!-- Banner Informativo de Regla de Negocio -->
    <div class="p-4 rounded-2xl bg-emerald-950 text-emerald-300 border border-emerald-800/40 text-xs flex items-center gap-3 shadow-lg">
        <div class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="leading-relaxed">
            <strong class="text-white">Regla de Sistema:</strong> El escaneo de un código <u>nunca descuenta ni altera el stock automáticamente</u>. Solo consulta los balances reales del catálogo y almacenes para evitar salidas accidentales.
        </div>
    </div>

    <!-- Selector de Método: USB/Manual vs Cámara -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Lector USB / Entrada Manual -->
        <div class="card-surface p-6 border border-slate-100 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </span>
                    <h3 class="font-bold text-slate-900 text-sm">Lector de Códigos USB o Manual</h3>
                </div>
                <p class="text-slate-500 text-xs mb-4">
                    Coloque el cursor en el recuadro y dispare con su pistola láser, o escriba el SKU/código y presione Enter.
                </p>

                <form id="scanForm" onsubmit="handleManualScan(event)" class="space-y-3">
                    <div class="relative">
                        <input
                            type="text"
                            id="barcodeInput"
                            placeholder="Escanee o escriba SKU (Ej: LAP-GAM-001)..."
                            autofocus
                            autocomplete="off"
                            class="w-full px-4 py-3 rounded-2xl border-2 border-emerald-500/60 bg-emerald-50/20 text-slate-900 font-mono text-base font-bold placeholder-slate-400 focus:outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-500/10 transition-all"
                        >
                    </div>
                    <button type="submit" class="w-full py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Consultar Producto</span>
                    </button>
                </form>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <span>Auto-foco activo para pistolas</span>
                <span class="font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">Enter para buscar</span>
            </div>
        </div>

        <!-- Cámara Web / Dispositivo Móvil -->
        <div class="card-surface p-6 border border-slate-100 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="p-2 rounded-xl bg-slate-100 text-slate-700">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </span>
                    <h3 class="font-bold text-slate-900 text-sm">Escáner con Cámara</h3>
                </div>
                <p class="text-slate-500 text-xs mb-4">
                    Active la cámara web para leer etiquetas QR impresas directamente en el empaque de la laptop.
                </p>

                <!-- Visor de la cámara -->
                <div class="relative bg-slate-900 rounded-2xl overflow-hidden aspect-video flex items-center justify-center border border-slate-800">
                    <video id="cameraVideo" class="w-full h-full object-cover hidden" playsinline></video>
                    <canvas id="cameraCanvas" class="hidden"></canvas>
                    <div id="cameraPlaceholder" class="text-center p-4">
                        <svg class="w-10 h-10 mx-auto text-slate-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <p class="text-xs text-slate-400 font-medium">Cámara inactiva</p>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                <button type="button" id="startCamBtn" onclick="toggleCamera()" class="flex-1 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    </svg>
                    <span id="camBtnText">Iniciar Cámara</span>
                </button>
            </div>
        </div>

    </div>

    <!-- Panel de Resultados AJAX -->
    <div id="scanResultContainer" class="hidden">
        <div class="card-surface p-6 sm:p-8 border-2 border-emerald-500/40 shadow-xl space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h3 class="text-base font-black text-slate-900 uppercase tracking-wide">Producto Identificado</h3>
                </div>
                <span id="resultTime" class="text-xs text-slate-400 font-mono"></span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <div class="text-center md:text-left">
                    <img id="resImage" src="" alt="Laptop" class="w-36 h-36 object-cover rounded-2xl border border-slate-200 mx-auto md:mx-0 shadow-sm">
                </div>

                <div class="md:col-span-2 space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span id="resSku" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-mono text-xs font-bold"></span>
                        <span id="resBrand" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 text-xs font-bold"></span>
                        <span id="resStatus" class="px-2.5 py-1 rounded-full text-xs font-bold"></span>
                    </div>

                    <h4 id="resName" class="text-xl font-black text-slate-900 leading-tight"></h4>
                    <p id="resPrice" class="text-2xl font-black text-emerald-600"></p>
                </div>
            </div>

            <!-- Balances de Stock por Almacén -->
            <div class="pt-4 border-t border-slate-100">
                <h5 class="text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Disponibilidad en Almacenes</h5>
                <div id="resWarehousesList" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Inyectado vía JS -->
                </div>
            </div>

            <!-- Acciones -->
            <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <a id="resKardexBtn" href="#" class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Ver Kardex Completo</span>
                    </a>
                    <a id="resQrBtn" href="#" target="_blank" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <span>Etiqueta QR</span>
                    </a>
                </div>

                <button type="button" onclick="resetScanner()" class="text-xs font-semibold text-slate-400 hover:text-slate-600">
                    Limpiar consulta
                </button>
            </div>
        </div>
    </div>

    <!-- Error / No encontrado -->
    <div id="scanErrorContainer" class="hidden p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-3">
        <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="scanErrorMessage">Producto no encontrado con el código escaneado.</span>
    </div>

</div>

<!-- Script de lectura y manejo de escaneo -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
let videoStream = null;
let scanningActive = false;

function handleManualScan(e) {
    e.preventDefault();
    const code = document.getElementById('barcodeInput').value.trim();
    if (!code) return;
    performLookup(code);
}

function performLookup(code) {
    const errorContainer = document.getElementById('scanErrorContainer');
    const resultContainer = document.getElementById('scanResultContainer');
    errorContainer.classList.add('hidden');

    fetch(`{{ route('admin.inventory.scan-lookup') }}?code=${encodeURIComponent(code)}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok || !data.found) {
            throw new Error(data.message || 'Código no encontrado');
        }
        renderResult(data);
    })
    .catch(err => {
        resultContainer.classList.add('hidden');
        errorContainer.classList.remove('hidden');
        document.getElementById('scanErrorMessage').textContent = err.message || 'Error al buscar producto';
    })
    .finally(() => {
        const input = document.getElementById('barcodeInput');
        input.value = '';
        input.focus();
    });
}

function renderResult(data) {
    const p = data.product;
    const stock = data.stock;

    document.getElementById('resImage').src = p.main_image;
    document.getElementById('resName').textContent = p.name;
    document.getElementById('resSku').textContent = 'SKU: ' + p.sku;
    document.getElementById('resBrand').textContent = p.brand || 'Portátiles Perú';
    document.getElementById('resPrice').textContent = p.price;
    document.getElementById('resultTime').textContent = 'Consultado: ' + new Date().toLocaleTimeString();

    const statusEl = document.getElementById('resStatus');
    statusEl.textContent = p.status === 'published' ? 'Publicado' : p.status;
    statusEl.className = 'px-2.5 py-1 rounded-full text-xs font-bold ' +
        (p.status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700');

    document.getElementById('resKardexBtn').href = p.detail_url;
    document.getElementById('resQrBtn').href = `{{ url('admin/qr/label') }}/${p.id}`;

    // Renderizar almacenes
    const whList = document.getElementById('resWarehousesList');
    whList.innerHTML = '';

    if (stock.by_warehouse && stock.by_warehouse.length > 0) {
        stock.by_warehouse.forEach(wh => {
            const card = document.createElement('div');
            card.className = 'p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-xs';
            card.innerHTML = `
                <div class="font-bold text-slate-800">${wh.warehouse_name}</div>
                <div class="text-[10px] text-slate-400 font-mono mb-2">${wh.warehouse_code}</div>
                <div class="flex justify-between items-center text-[11px]">
                    <span class="text-slate-500">Físico: <strong>${wh.qty_physical}</strong></span>
                    <span class="text-emerald-600 font-bold">Disp: ${wh.qty_available}</span>
                </div>
            `;
            whList.appendChild(card);
        });
    } else {
        whList.innerHTML = '<p class="col-span-3 text-xs text-slate-400">Sin stock registrado en almacenes.</p>';
    }

    document.getElementById('scanResultContainer').classList.remove('hidden');
    document.getElementById('scanErrorContainer').classList.add('hidden');
}

function resetScanner() {
    document.getElementById('scanResultContainer').classList.add('hidden');
    document.getElementById('scanErrorContainer').classList.add('hidden');
    document.getElementById('barcodeInput').focus();
}

// Lógica de Cámara Web
async function toggleCamera() {
    const video = document.getElementById('cameraVideo');
    const placeholder = document.getElementById('cameraPlaceholder');
    const btnText = document.getElementById('camBtnText');

    if (scanningActive) {
        stopCamera();
        return;
    }

    try {
        videoStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        video.srcObject = videoStream;
        video.setAttribute('playsinline', true);
        await video.play();

        video.classList.remove('hidden');
        placeholder.classList.add('hidden');
        scanningActive = true;
        btnText.textContent = 'Detener Cámara';

        requestAnimationFrame(tickScan);
    } catch (err) {
        alert('No se pudo acceder a la cámara. Compruebe los permisos del navegador.');
    }
}

function stopCamera() {
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
        videoStream = null;
    }
    const video = document.getElementById('cameraVideo');
    const placeholder = document.getElementById('cameraPlaceholder');
    const btnText = document.getElementById('camBtnText');

    video.classList.add('hidden');
    placeholder.classList.remove('hidden');
    scanningActive = false;
    btnText.textContent = 'Iniciar Cámara';
}

function tickScan() {
    if (!scanningActive) return;

    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const ctx = canvas.getContext('2d');

    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.height = video.videoHeight;
        canvas.width = video.videoWidth;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

        if (window.jsQR) {
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: 'dontInvert',
            });

            if (code && code.data) {
                stopCamera();
                performLookup(code.data);
                return;
            }
        }
    }

    if (scanningActive) {
        requestAnimationFrame(tickScan);
    }
}
</script>
@endsection
