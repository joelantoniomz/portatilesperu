<!-- Modal: Escáner EAN -->
<dialog id="barcode-scanner" class="w-full max-w-lg rounded-3xl border border-slate-200 p-0 shadow-2xl backdrop:bg-slate-950/60">
    <div class="space-y-4 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-base font-black text-slate-900">Escanear código EAN</h3>
                <p class="mt-1 text-xs text-slate-500">Permita el uso de la cámara y enfoque el código de barras. Si el navegador no es compatible, ingrese el EAN manualmente.</p>
            </div>
            <button type="button" data-close-barcode-scanner class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700">Cerrar</button>
        </div>
        <video id="barcode-video" class="hidden aspect-video w-full rounded-2xl bg-slate-950 object-cover" autoplay playsinline></video>
        <p id="barcode-scanner-message" class="rounded-2xl bg-slate-50 p-3 text-xs text-slate-600">Preparando cámara…</p>
    </div>
</dialog>

<!-- Modal: Pegar texto libre para extraer ficha con Gemini AI -->
<dialog id="gemini-paste-modal" class="w-full max-w-xl rounded-3xl border border-slate-200 p-0 shadow-2xl backdrop:bg-slate-950/60">
    <div class="p-6 sm:p-7 space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-slate-950 flex items-center justify-center font-black text-base shadow-md">✨</span>
                <div>
                    <h3 class="text-base font-black text-slate-900">Extraer Ficha con Gemini AI</h3>
                    <p class="text-xs text-slate-400">Pega texto libre, ficha de fabricante, cotización o características técnicas.</p>
                </div>
            </div>
            <button type="button" id="btn-close-paste-modal" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 font-bold text-sm flex items-center justify-center transition-colors">&times;</button>
        </div>

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Pegar Texto o Especificaciones del Producto</label>
                <textarea 
                    id="gemini-paste-textarea" 
                    rows="7" 
                    placeholder="Pega aquí cualquier información del producto, por ejemplo:&#10;&#10;JBL Tune 520BT Auriculares inalámbricos morados con sonido JBL Pure Bass, Bluetooth 5.3, hasta 57 horas de batería y carga rápida, llamadas manos libres con Voice Aware, ligeros y plegables..." 
                    class="w-full rounded-2xl border border-slate-200 p-3.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-mono leading-relaxed"
                ></textarea>
                <span class="text-[11px] text-slate-400 mt-1 block">Gemini detectará y rellenará CPU, RAM, pantalla, conectividad, peso, dimensiones y descripciones en la ficha.</span>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                <button type="button" id="btn-clear-specs-action" class="px-3.5 py-2 rounded-xl text-rose-600 hover:bg-rose-50 font-bold text-xs transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Limpiar Campos</span>
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" id="btn-cancel-paste-modal" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                        Cancelar
                    </button>
                    <button type="button" id="btn-submit-paste-extract" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-black text-xs shadow-md transition-all flex items-center gap-2">
                        <span>✨ Procesar y Aplicar Ficha</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</dialog>

<!-- Modal: Configuración de API Key de Gemini AI -->
<dialog id="gemini-key-modal" class="w-full max-w-md rounded-3xl border border-slate-200 p-0 shadow-2xl backdrop:bg-slate-950/60">
    <div class="p-6 space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-black text-sm">✨</span>
                <div>
                    <h3 class="text-base font-black text-slate-900">Google Gemini API Key</h3>
                    <p class="text-xs text-slate-400">Inteligencia Artificial para autocompletar fichas técnicas.</p>
                </div>
            </div>
            <button type="button" id="btn-close-gemini-modal" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 font-bold text-sm flex items-center justify-center transition-colors">&times;</button>
        </div>

        <div class="rounded-2xl bg-emerald-50/70 border border-emerald-100 p-3.5 text-xs text-emerald-900 space-y-1.5">
            <p class="font-bold flex items-center gap-1.5">
                <span>¿Cómo obtener una API Key gratuita?</span>
            </p>
            <p class="text-[11px] text-emerald-800 leading-relaxed">
                1. Ingrese a <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer" class="underline font-bold text-emerald-900">Google AI Studio (aistudio.google.com)</a>.<br>
                2. Inicie sesión con su cuenta Google y haga clic en <strong>"Create API Key"</strong>.<br>
                3. Copie la clave y péguela aquí abajo.
            </p>
        </div>

        <form id="form-save-gemini-key" class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Clave de API de Gemini *</label>
                <input type="password" id="modal-gemini-key-input" placeholder="AIzaSy..." required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <span class="text-[10px] text-slate-400 mt-1 block">Se almacena de forma segura en la configuración del servidor.</span>
            </div>

            <div id="gemini-modal-status-msg" class="text-xs font-semibold hidden"></div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" id="btn-cancel-gemini-modal" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="btn-submit-gemini-key" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md transition-colors flex items-center gap-1.5">
                    <span>Guardar API Key</span>
                </button>
            </div>
        </form>
    </div>
</dialog>

<script>
    (() => {
        // ──────────────────────────────────────────────────────────────────────────
        // 1. ESCÁNER DE CÓDIGO DE BARRAS EAN
        // ──────────────────────────────────────────────────────────────────────────
        const scanner = document.getElementById('barcode-scanner');
        const scannerButton = document.querySelector('[data-open-barcode-scanner]');
        const closeButton = document.querySelector('[data-close-barcode-scanner]');
        const barcodeInput = document.getElementById('barcode-input');
        const video = document.getElementById('barcode-video');
        const message = document.getElementById('barcode-scanner-message');
        let stream;
        let scanTimer;

        const stopScanner = () => {
            window.clearInterval(scanTimer);
            stream?.getTracks().forEach((track) => track.stop());
            stream = undefined;
            if (video) video.srcObject = null;
        };

        scannerButton?.addEventListener('click', async () => {
            scanner.showModal();

            if (!('BarcodeDetector' in window) || !navigator.mediaDevices?.getUserMedia) {
                message.textContent = 'Este navegador no puede leer EAN con cámara. Puede usar la pantalla de Escáner de Inventario o escribir el código manualmente.';
                return;
            }

            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                video.srcObject = stream;
                video.classList.remove('hidden');
                message.textContent = 'Buscando un código EAN…';
                const detector = new BarcodeDetector({ formats: ['ean_13', 'ean_8', 'code_128', 'upc_a', 'upc_e'] });
                scanTimer = window.setInterval(async () => {
                    const codes = await detector.detect(video);
                    if (codes.length > 0) {
                        barcodeInput.value = codes[0].rawValue;
                        message.textContent = `EAN detectado: ${codes[0].rawValue}`;
                        stopScanner();
                        window.setTimeout(() => scanner.close(), 700);
                    }
                }, 400);
            } catch (error) {
                message.textContent = 'No se pudo abrir la cámara. Revise los permisos o ingrese el EAN manualmente.';
            }
        });

        closeButton?.addEventListener('click', () => scanner.close());
        scanner?.addEventListener('close', stopScanner);

        // ──────────────────────────────────────────────────────────────────────────
        // 2. FILAS DINÁMICAS DE FICHA TÉCNICA EXTRA
        // ──────────────────────────────────────────────────────────────────────────
        const addSpecification = () => {
            const container = document.getElementById('additional-specifications');
            if (!container) return;
            const row = document.createElement('div');
            row.dataset.specificationRow = '';
            row.className = 'space-y-2';
            row.innerHTML = '<div class="flex gap-2"><input type="text" placeholder="Nombre del dato (Ej: Batería, Teclado)" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold" data-specification-key><button type="button" data-remove-specification class="rounded-xl px-2 text-rose-600 hover:bg-rose-50 font-bold">&times;</button></div><input type="text" placeholder="Valor (Ej: 80Wh 4 celdas)" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-specification-value>';
            container.appendChild(row);
        };

        document.querySelector('[data-add-specification]')?.addEventListener('click', addSpecification);
        document.addEventListener('input', (event) => {
            if (!event.target.matches('[data-specification-key]')) return;
            const row = event.target.closest('[data-specification-row]');
            if (!row) return;
            const valueInput = row.querySelector('[data-specification-value]');
            const key = event.target.value.trim().toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
            valueInput.name = key ? `technical_specs[${key}]` : '';
        });
        document.addEventListener('click', (event) => {
            if (event.target.matches('[data-remove-specification]')) {
                event.target.closest('[data-specification-row]')?.remove();
            }
        });

        // ──────────────────────────────────────────────────────────────────────────
        // 3. GALERÍA DE IMÁGENES: AGREGAR URL UNA A UNA CON PREVIEW INMEDIATO
        // ──────────────────────────────────────────────────────────────────────────
        const urlInput = document.getElementById('image-url-input');
        const btnAddUrl = document.getElementById('btn-add-image-url');
        const urlContainer = document.getElementById('url-images-container');
        const localFileInput = document.getElementById('local-file-input');
        const localPreview = document.getElementById('local-files-preview');
        const counterBadge = document.getElementById('gallery-counter-badge');

        const updateGalleryCounter = () => {
            if (!counterBadge) return;
            const urlCount = urlContainer ? urlContainer.querySelectorAll('[data-url-card]').length : 0;
            const fileCount = localFileInput && localFileInput.files ? localFileInput.files.length : 0;
            const total = urlCount + fileCount;
            counterBadge.textContent = `${total} de 8 imágenes agregadas`;
            if (total >= 8) {
                counterBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800';
            } else {
                counterBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700';
            }
        };

        const addImageUrlCard = (url) => {
            if (!urlContainer) return;
            const trimmed = url.trim();
            if (!trimmed || (!trimmed.startsWith('http://') && !trimmed.startsWith('https://'))) {
                alert('Por favor ingrese una URL válida que empiece con http:// o https://');
                return;
            }

            const currentCount = urlContainer.querySelectorAll('[data-url-card]').length;
            if (currentCount >= 8) {
                alert('Ya ha alcanzado el límite máximo de imágenes permitidas.');
                return;
            }

            const card = document.createElement('div');
            card.className = 'relative group rounded-2xl overflow-hidden border border-slate-200 bg-white aspect-square shadow-xs flex flex-col items-center justify-center p-1.5 transition-all hover:border-emerald-400';
            card.dataset.urlCard = '';
            card.innerHTML = `
                <img src="${trimmed}" alt="Imagen agregada" class="w-full h-full object-cover rounded-xl" onerror="this.src='data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'100\\' height=\\'100\\' fill=\\'%23f1f5f9\\'%3E%3Ctext x=\\'50%25\\' y=\\'50%25\\' dominant-baseline=\\'middle\\' text-anchor=\\'middle\\' font-size=\\'10\\' fill=\\'%2394a3b8\\'%3EError Imagen%3C/text%3E%3C/svg%3E'">
                <input type="hidden" name="image_urls[]" value="${trimmed}">
                <button type="button" data-remove-url-image title="Quitar imagen" class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs flex items-center justify-center shadow-md transition-all">&times;</button>
                <span class="absolute bottom-1.5 left-1.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-900/80 text-white backdrop-blur-xs">URL</span>
            `;

            urlContainer.appendChild(card);
            if (urlInput) urlInput.value = '';
            updateGalleryCounter();
        };

        btnAddUrl?.addEventListener('click', () => {
            if (urlInput) addImageUrlCard(urlInput.value);
        });

        urlInput?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addImageUrlCard(urlInput.value);
            }
        });

        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-remove-url-image]')) {
                const card = e.target.closest('[data-url-card]');
                card?.remove();
                updateGalleryCounter();
            }
        });

        // Previsualización de archivos locales seleccionados
        localFileInput?.addEventListener('change', () => {
            if (!localPreview) return;
            localPreview.innerHTML = '';
            const files = Array.from(localFileInput.files || []);

            if (files.length === 0) {
                localPreview.classList.add('hidden');
                updateGalleryCounter();
                return;
            }

            localPreview.classList.remove('hidden');
            files.forEach((file, index) => {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                const previewCard = document.createElement('div');
                previewCard.className = 'relative rounded-2xl overflow-hidden border border-emerald-200 bg-white aspect-square shadow-xs p-1';
                reader.onload = (e) => {
                    previewCard.innerHTML = `
                        <img src="${e.target.result}" alt="${file.name}" class="w-full h-full object-cover rounded-xl">
                        <span class="absolute bottom-1.5 left-1.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-700 text-white shadow">Archivo ${index + 1}</span>
                    `;
                };
                reader.readAsDataURL(file);
                localPreview.appendChild(previewCard);
            });

            updateGalleryCounter();
        });

        updateGalleryCounter();

        // ──────────────────────────────────────────────────────────────────────────
        // 4. ASISTENTE GEMINI AI: MODAL Y AUTOCOMPLETADO
        // ──────────────────────────────────────────────────────────────────────────
        const geminiModal = document.getElementById('gemini-key-modal');
        const openKeyButtons = document.querySelectorAll('[data-open-gemini-modal]');
        const closeKeyButton = document.getElementById('btn-close-gemini-modal');
        const cancelKeyButton = document.getElementById('btn-cancel-gemini-modal');
        const formSaveKey = document.getElementById('form-save-gemini-key');
        const keyInput = document.getElementById('modal-gemini-key-input');
        const keyStatusMsg = document.getElementById('gemini-modal-status-msg');
        const apiKeyBadge = document.getElementById('gemini-api-key-badge');

        const openGeminiModal = () => {
            geminiModal?.showModal();
            keyStatusMsg?.classList.add('hidden');
        };

        openKeyButtons.forEach((btn) => btn.addEventListener('click', openGeminiModal));
        closeKeyButton?.addEventListener('click', () => geminiModal?.close());
        cancelKeyButton?.addEventListener('click', () => geminiModal?.close());

        formSaveKey?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const key = keyInput?.value.trim();
            if (!key) return;

            const submitBtn = document.getElementById('btn-submit-gemini-key');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Guardando…';
            }

            try {
                const response = await fetch('{{ route("admin.products.save-gemini-key") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ api_key: key }),
                });

                const data = await response.json();
                if (data.success) {
                    if (keyStatusMsg) {
                        keyStatusMsg.className = 'text-xs font-bold text-emerald-700 block';
                        keyStatusMsg.textContent = '✓ ' + data.message;
                    }
                    if (apiKeyBadge) {
                        apiKeyBadge.innerHTML = '🟢 API Key Conectada';
                        apiKeyBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 cursor-pointer hover:bg-emerald-200 transition-colors';
                    }
                    window.setTimeout(() => {
                        geminiModal?.close();
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Guardar API Key';
                        }
                    }, 800);
                } else {
                    throw new Error(data.message || 'Error al guardar');
                }
            } catch (err) {
                if (keyStatusMsg) {
                    keyStatusMsg.className = 'text-xs font-bold text-rose-600 block';
                    keyStatusMsg.textContent = 'Error: ' + err.message;
                }
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Guardar API Key';
                }
            }
        });

        // Función para iluminar un campo que fue autocompletado
        const highlightField = (el) => {
            if (!el) return;
            el.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-50/40');
            window.setTimeout(() => {
                el.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-50/40');
            }, 3000);
        };

        // Generador con Gemini AI
        const promptInput = document.getElementById('gemini-prompt-input');
        const btnGenerate = document.getElementById('btn-gemini-generate');
        const checkIdentification = document.getElementById('check-gemini-identification');
        const checkDescriptions = document.getElementById('check-gemini-descriptions');
        const checkSpecs = document.getElementById('check-gemini-specs');

        const executeGeminiGeneration = async (forcedPrompt = null, sectionScope = null) => {
            const prompt = (forcedPrompt || promptInput?.value || document.querySelector('input[name="name"]')?.value || '').trim();
            if (!prompt) {
                alert('Por favor ingrese el nombre del modelo o pegue las especificaciones técnicas en el campo del asistente Gemini.');
                promptInput?.focus();
                return;
            }

            const originalBtnHtml = btnGenerate ? btnGenerate.innerHTML : '';
            if (btnGenerate) {
                btnGenerate.disabled = true;
                btnGenerate.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Analizando con Gemini AI…</span>
                `;
            }

            try {
                const response = await fetch('{{ route("admin.products.gemini-autofill") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ prompt: prompt }),
                });

                const resData = await response.json();
                if (!resData.success || !resData.data) {
                    throw new Error(resData.message || 'No se pudieron extraer datos');
                }

                const d = resData.data;

                // 1. Identificación y Clasificación
                if (!sectionScope || sectionScope === 'identification' || (checkIdentification && checkIdentification.checked)) {
                    if (d.name) {
                        const el = document.querySelector('input[name="name"]');
                        if (el) { el.value = d.name; highlightField(el); }
                    }
                    if (d.sku) {
                        const el = document.querySelector('input[name="sku"]');
                        if (el) { el.value = d.sku; highlightField(el); }
                    }
                    if (d.category_id) {
                        const el = document.getElementById('category_select') || document.querySelector('select[name="category_id"]');
                        if (el) { el.value = d.category_id; highlightField(el); }
                    }
                    if (d.brand_id) {
                        const el = document.getElementById('brand_select') || document.querySelector('select[name="brand_id"]');
                        if (el) { el.value = d.brand_id; highlightField(el); }
                    }
                }

                // 2. Descripciones Comerciales
                if (!sectionScope || sectionScope === 'descriptions' || (checkDescriptions && checkDescriptions.checked)) {
                    if (d.short_description) {
                        const el = document.querySelector('textarea[name="short_description"]');
                        if (el) { el.value = d.short_description; highlightField(el); }
                    }
                    if (d.description) {
                        const el = document.querySelector('textarea[name="description"]');
                        if (el) { el.value = d.description; highlightField(el); }
                    }
                }

                // 3. Ficha Técnica Estructurada
                if (!sectionScope || sectionScope === 'specs' || (checkSpecs && checkSpecs.checked)) {
                    const specs = d.technical_specs || {};
                    const specMap = {
                        'procesador': 'input[name="technical_specs[procesador]"]',
                        'ram': 'input[name="technical_specs[ram]"]',
                        'almacenamiento': 'input[name="technical_specs[almacenamiento]"]',
                        'pantalla': 'input[name="technical_specs[pantalla]"]',
                        'grafica': 'input[name="technical_specs[grafica]"]',
                        'sistema_operativo': 'input[name="technical_specs[sistema_operativo]"]',
                        'conectividad': 'input[name="technical_specs[conectividad]"]',
                        'garantia': 'input[name="technical_specs[garantia]"]',
                    };

                    Object.entries(specMap).forEach(([key, selector]) => {
                        if (specs[key]) {
                            const el = document.querySelector(selector);
                            if (el) { el.value = specs[key]; highlightField(el); }
                        }
                    });

                    if (d.weight) {
                        const el = document.querySelector('input[name="weight"]');
                        if (el) { el.value = d.weight; highlightField(el); }
                    }
                    if (d.dimensions) {
                        const el = document.querySelector('input[name="dimensions"]');
                        if (el) { el.value = d.dimensions; highlightField(el); }
                    }
                }

                // Mensaje Toast de Éxito
                const notice = d.warning ? d.warning : '¡Ficha técnica y clasificación generadas con éxito por Gemini AI!';
                alert(notice);

            } catch (err) {
                alert('No se pudo completar la solicitud con Gemini: ' + err.message);
            } finally {
                if (btnGenerate) {
                    btnGenerate.disabled = false;
                    btnGenerate.innerHTML = originalBtnHtml;
                }
            }
        };

        btnGenerate?.addEventListener('click', () => executeGeminiGeneration());

        // ──────────────────────────────────────────────────────────────────────────
        // 5. MODAL PARA PEGAR TEXTO LIBRE Y EXTRAER FICHA CON GEMINI
        // ──────────────────────────────────────────────────────────────────────────
        const pasteModal = document.getElementById('gemini-paste-modal');
        const btnClosePasteModal = document.getElementById('btn-close-paste-modal');
        const btnCancelPasteModal = document.getElementById('btn-cancel-paste-modal');
        const btnSubmitPasteExtract = document.getElementById('btn-submit-paste-extract');
        const pasteTextarea = document.getElementById('gemini-paste-textarea');
        const btnClearSpecs = document.getElementById('btn-clear-specs-action');

        let currentModalSection = null;

        const openPasteModal = (section) => {
            currentModalSection = section;
            // Pre-llenar con el nombre o prompt si el textarea está vacío
            if (pasteTextarea && !pasteTextarea.value.trim()) {
                const currentName = document.querySelector('input[name="name"]')?.value || promptInput?.value || '';
                if (currentName) {
                    pasteTextarea.value = currentName;
                }
            }
            pasteModal?.showModal();
            pasteTextarea?.focus();
        };

        btnClosePasteModal?.addEventListener('click', () => pasteModal?.close());
        btnCancelPasteModal?.addEventListener('click', () => pasteModal?.close());

        btnClearSpecs?.addEventListener('click', () => {
            if (confirm('¿Desea limpiar todos los campos de especificaciones técnicas?')) {
                const specInputs = document.querySelectorAll('input[name^="technical_specs"], #additional-specifications input');
                specInputs.forEach(i => i.value = '');
                const addContainer = document.getElementById('additional-specifications');
                if (addContainer) addContainer.innerHTML = '';
            }
        });

        btnSubmitPasteExtract?.addEventListener('click', async () => {
            const textToProcess = pasteTextarea?.value.trim();
            if (!textToProcess) {
                alert('Por favor pega texto o especificaciones para procesar.');
                pasteTextarea?.focus();
                return;
            }

            pasteModal?.close();
            await executeGeminiGeneration(textToProcess, currentModalSection);
        });

        // Botones contextuales por sección: abren el modal interactivo para pegar texto libre
        document.querySelectorAll('[data-gemini-section]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const section = btn.dataset.geminiSection;
                openPasteModal(section);
            });
        });

    })();
</script>
