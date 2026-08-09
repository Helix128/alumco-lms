import './bootstrap';
import Chart from 'chart.js/auto';

window.Chart = Chart;

const chartRegistry = new Map();

window.AlumcoCharts = {
    render(canvasId, config) {
        const canvas = document.getElementById(canvasId);

        if (! canvas) {
            return null;
        }

        const existingChart = chartRegistry.get(canvasId);
        if (existingChart) {
            existingChart.destroy();
        }

        const context = canvas.getContext('2d');
        if (! context) {
            return null;
        }

        const chart = new Chart(context, config);
        chartRegistry.set(canvasId, chart);

        return chart;
    },

    destroy(canvasId) {
        const existingChart = chartRegistry.get(canvasId);
        if (! existingChart) {
            return;
        }

        existingChart.destroy();
        chartRegistry.delete(canvasId);
    },

    destroyAll() {
        chartRegistry.forEach((chart) => chart.destroy());
        chartRegistry.clear();
    },
};

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

class ModulePdfViewer {
    constructor(element, pdfjsLib) {
        this.element = element;
        this.pdfjsLib = pdfjsLib;
        this.url = element.dataset.pdfUrl;
        this.canvas = element.querySelector('[data-pdf-canvas]');
        this.context = this.canvas?.getContext('2d');
        this.status = element.querySelector('[data-pdf-status]');
        this.currentPageOutput = element.querySelector('[data-pdf-current-page]');
        this.totalPagesOutput = element.querySelector('[data-pdf-total-pages]');
        this.previousButton = element.querySelector('[data-pdf-previous]');
        this.nextButton = element.querySelector('[data-pdf-next]');
        this.zoomInButton = element.querySelector('[data-pdf-zoom-in]');
        this.zoomOutButton = element.querySelector('[data-pdf-zoom-out]');
        this.scale = 1.1;
        this.pageNumber = 1;
        this.pageCount = 0;
        this.renderTask = null;
        this.pendingRender = false;
        this.document = null;

        if (! this.url || ! this.canvas || ! this.context) {
            return;
        }

        this.bindControls();
        this.load();
    }

    bindControls() {
        this.previousButton?.addEventListener('click', () => this.goToPage(this.pageNumber - 1));
        this.nextButton?.addEventListener('click', () => this.goToPage(this.pageNumber + 1));
        this.zoomOutButton?.addEventListener('click', () => this.setScale(this.scale - 0.15));
        this.zoomInButton?.addEventListener('click', () => this.setScale(this.scale + 0.15));
        window.addEventListener('resize', () => this.renderPage(), { passive: true });
    }

    async load() {
        this.setStatus('Cargando documento...');

        try {
            this.document = await this.pdfjsLib.getDocument({
                url: this.url,
                withCredentials: true,
            }).promise;

            this.pageCount = this.document.numPages;
            this.totalPagesOutput.textContent = String(this.pageCount);
            this.setStatus('');
            await this.renderPage();
        } catch (error) {
            console.error('No se pudo cargar el PDF del módulo.', error);
            this.setStatus('No se pudo mostrar el PDF en el visor.');
            this.element.dataset.pdfState = 'error';
        }
    }

    async renderPage() {
        if (! this.document) {
            return;
        }

        if (this.renderTask) {
            this.pendingRender = true;
            return;
        }

        const page = await this.document.getPage(this.pageNumber);
        const scaledViewport = page.getViewport({ scale: this.scale });
        const pixelRatio = window.devicePixelRatio || 1;

        this.canvas.width = Math.floor(scaledViewport.width * pixelRatio);
        this.canvas.height = Math.floor(scaledViewport.height * pixelRatio);
        this.canvas.style.width = `${Math.floor(scaledViewport.width)}px`;
        this.canvas.style.height = `${Math.floor(scaledViewport.height)}px`;

        this.context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);

        this.renderTask = page.render({
            canvasContext: this.context,
            viewport: scaledViewport,
        });

        await this.renderTask.promise;
        this.renderTask = null;
        this.updateControls();

        if (this.pendingRender) {
            this.pendingRender = false;
            await this.renderPage();
        }
    }

    goToPage(pageNumber) {
        this.pageNumber = clamp(pageNumber, 1, this.pageCount);
        this.renderPage();
    }

    setScale(scale) {
        this.scale = clamp(scale, 0.65, 2.25);
        this.renderPage();
    }

    setStatus(message) {
        if (this.status) {
            this.status.textContent = message;
        }
    }

    updateControls() {
        this.currentPageOutput.textContent = String(this.pageNumber);
        this.previousButton.disabled = this.pageNumber <= 1;
        this.nextButton.disabled = this.pageNumber >= this.pageCount;
    }
}

const initializeModulePdfViewers = async () => {
    const viewers = document.querySelectorAll('[data-module-pdf-viewer]:not([data-pdf-ready])');

    if (! viewers.length) {
        return;
    }

    const [pdfjsLib, { default: pdfWorkerUrl }] = await Promise.all([
        import('pdfjs-dist'),
        import('pdfjs-dist/build/pdf.worker.mjs?url'),
    ]);

    pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;

    document.querySelectorAll('[data-module-pdf-viewer]:not([data-pdf-ready])').forEach((element) => {
        element.dataset.pdfReady = 'true';
        new ModulePdfViewer(element, pdfjsLib);
    });
};

document.addEventListener('DOMContentLoaded', initializeModulePdfViewers);
document.addEventListener('livewire:navigated', initializeModulePdfViewers);
document.addEventListener('livewire:navigating', () => {
    window.AlumcoCharts?.destroyAll();
});

class ChunkedMediaUploader {
    constructor(form) {
        this.form = form;
        this.fileInput = form.querySelector('[data-media-file]');
        this.assetInput = form.querySelector('[data-media-asset]');
        this.submitButton = form.querySelector('button[type="submit"]');
        this.abortController = null;
        if (! this.fileInput || ! this.assetInput) return;
        this.createStatus();
        form.addEventListener('submit', (event) => this.handleSubmit(event));
    }

    createStatus() {
        this.status = document.createElement('div');
        this.status.className = 'hidden mt-3 rounded-xl border border-Alumco-blue/10 bg-Alumco-blue/5 p-3';
        this.status.innerHTML = '<div class="flex items-center justify-between gap-3"><span class="text-xs font-bold text-Alumco-blue" data-upload-status></span><button type="button" class="text-xs font-black uppercase text-Alumco-coral" data-upload-cancel>Cancelar</button></div><div class="mt-2 h-2 overflow-hidden rounded-full bg-white"><div class="h-full bg-Alumco-blue transition-all" data-upload-progress style="width:0%"></div></div>';
        this.fileInput.closest('.group, div')?.after(this.status);
        this.status.querySelector('[data-upload-cancel]').addEventListener('click', () => this.abortController?.abort());
    }

    purpose() {
        if (this.fileInput.dataset.mediaPurpose) return this.fileInput.dataset.mediaPurpose;
        const source = document.querySelector(this.fileInput.dataset.mediaPurposeSource);
        return { video: 'video', documento: 'document', pdf: 'pdf', ppt: 'document', imagen: 'image' }[source?.value] || '';
    }

    async handleSubmit(event) {
        const file = this.fileInput.files?.[0];
        if (! file || this.assetInput.value || this.form.dataset.mediaSubmitting === 'true') return;
        event.preventDefault();
        const purpose = this.purpose();
        if (! purpose) {
            this.setStatus('Selecciona un tipo de contenido válido.', 0, true);
            return;
        }

        this.abortController = new AbortController();
        this.submitButton.disabled = true;
        try {
            const upload = await this.session(file, purpose);
            const received = new Set(upload.received_parts || []);
            const partEtags = { ...(upload.part_etags || {}) };
            for (let part = 1; part <= upload.total_parts; part += 1) {
                if (received.has(part)) continue;
                const start = (part - 1) * upload.chunk_size;
                const blob = file.slice(start, Math.min(start + upload.chunk_size, file.size));
                if (upload.direct) {
                    const response = await this.retry(() => window.axios.put(upload.part_urls[part], blob, {
                        headers: { 'Content-Type': 'application/octet-stream' },
                        signal: this.abortController.signal,
                        withCredentials: false,
                    }));
                    const etag = response.headers.etag;
                    if (! etag) throw new Error('El proveedor no expuso el encabezado ETag. Revisa la política CORS del bucket.');
                    partEtags[part] = etag;
                } else {
                    await this.retry(() => window.axios.put(
                        `/capacitador/media/uploads/${upload.id}/parts/${part}`,
                        blob,
                        { headers: { 'Content-Type': 'application/octet-stream' }, signal: this.abortController.signal },
                    ));
                }
                this.setStatus(`Subiendo bloque ${part} de ${upload.total_parts}…`, Math.round((part / upload.total_parts) * 95));
            }
            this.setStatus('Validando y preparando el recurso…', 97);
            const completeBody = upload.direct ? {
                parts: Object.entries(partEtags).map(([PartNumber, ETag]) => ({ PartNumber: Number(PartNumber), ETag })),
            } : {};
            const completed = await window.axios.post(`/capacitador/media/uploads/${upload.id}/complete`, completeBody, { signal: this.abortController.signal });
            this.assetInput.value = completed.data.asset_id;
            localStorage.removeItem(this.key(file, purpose));
            this.setStatus(completed.data.status === 'ready' ? 'Archivo listo.' : 'Archivo cargado; continuará procesándose.', 100);
            this.fileInput.disabled = true;
            this.form.dataset.mediaSubmitting = 'true';
            this.form.requestSubmit();
        } catch (error) {
            if (error.name === 'CanceledError' || error.code === 'ERR_CANCELED') {
                this.setStatus('Carga cancelada. Puedes reintentarlo.', 0, true);
                const id = localStorage.getItem(this.key(file, purpose));
                if (id) window.axios.delete(`/capacitador/media/uploads/${id}`).catch(() => {});
                localStorage.removeItem(this.key(file, purpose));
            } else {
                const message = error.response?.data?.message || Object.values(error.response?.data?.errors || {})?.flat()?.[0] || 'No se pudo cargar el archivo.';
                this.setStatus(message, 0, true);
            }
            this.submitButton.disabled = false;
        }
    }

    async session(file, purpose) {
        const key = this.key(file, purpose);
        const previous = localStorage.getItem(key);
        if (previous) {
            try {
                const response = await window.axios.get(`/capacitador/media/uploads/${previous}`);
                if (response.data.status === 'uploading') return response.data;
            } catch (_) {
                localStorage.removeItem(key);
            }
        }
        const response = await window.axios.post('/capacitador/media/uploads', {
            purpose, name: file.name, mime_type: file.type, size: file.size,
        });
        localStorage.setItem(key, response.data.id);
        return response.data;
    }

    async retry(callback) {
        let lastError;
        for (let attempt = 1; attempt <= 3; attempt += 1) {
            try { return await callback(); } catch (error) {
                lastError = error;
                if (error.code === 'ERR_CANCELED') throw error;
                await new Promise((resolve) => window.setTimeout(resolve, attempt * 500));
            }
        }
        throw lastError;
    }

    key(file, purpose) {
        return `alumco-media:${purpose}:${file.name}:${file.size}:${file.lastModified}`;
    }

    setStatus(message, percent, error = false) {
        this.status.classList.remove('hidden');
        const label = this.status.querySelector('[data-upload-status]');
        label.textContent = message;
        label.classList.toggle('text-Alumco-coral', error);
        this.status.querySelector('[data-upload-progress]').style.width = `${percent}%`;
    }
}

const initializeMediaUploaders = () => document
    .querySelectorAll('[data-media-upload-form]:not([data-media-ready])')
    .forEach((form) => {
        form.dataset.mediaReady = 'true';
        new ChunkedMediaUploader(form);
    });

document.addEventListener('DOMContentLoaded', initializeMediaUploaders);
document.addEventListener('livewire:navigated', initializeMediaUploaders);

const setupNavigationProgress = () => {
    const bar = document.querySelector('[data-nav-progress]');

    if (! bar) {
        return;
    }

    let timer = null;
    let skeletonTimer = null;
    let progress = 10;
    let navigationStartedAt = null;
    let slowTransitionCount = 0;
    let cachedTransitionCount = 0;

    const setProgress = (value) => {
        progress = clamp(value, 0, 100);
        bar.style.transform = `scaleX(${progress / 100})`;
    };

    const toggleSkeleton = (active) => {
        const content = document.querySelector('[data-nav-content]');
        const skeleton = document.querySelector('[data-nav-skeleton]');

        if (! content || ! skeleton) {
            return;
        }

        content.setAttribute('aria-busy', active ? 'true' : 'false');
        content.dataset.loading = active ? 'true' : 'false';
    };

    const start = (event) => {
        clearInterval(timer);
        clearTimeout(skeletonTimer);

        const isReducedMotion = window.AlumcoAccessibility?.isReducedMotion();
        navigationStartedAt = performance.now();
        const isCachedNavigation = Boolean(event?.detail?.cached);

        if (isCachedNavigation) {
            cachedTransitionCount += 1;
        }

        bar.dataset.active = 'true';
        
        if (isReducedMotion) {
            setProgress(100);
            return;
        }

        setProgress(12);

        skeletonTimer = setTimeout(() => {
            toggleSkeleton(true);
            slowTransitionCount += 1;
        }, isCachedNavigation ? 130 : 70);

        timer = setInterval(() => {
            if (progress < 85) {
                setProgress(progress + (progress < 40 ? 12 : 6));
            }
        }, 120);
    };

    const finish = () => {
        clearInterval(timer);
        clearTimeout(skeletonTimer);
        toggleSkeleton(false);
        setProgress(100);

        const elapsed = navigationStartedAt ? Math.round(performance.now() - navigationStartedAt) : null;
        if (elapsed !== null) {
            console.debug('[nav-perf]', {
                elapsedMs: elapsed,
                slowTransitions: slowTransitionCount,
                cachedTransitions: cachedTransitionCount,
            });
        }

        setTimeout(() => {
            bar.dataset.active = 'false';
            setProgress(0);
        }, 180);
    };

    document.addEventListener('livewire:navigate', start);
    document.addEventListener('livewire:navigated', finish);
};

document.addEventListener('DOMContentLoaded', setupNavigationProgress);
