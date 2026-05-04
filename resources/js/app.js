import './bootstrap';
import Chart from 'chart.js/auto';

window.Chart = Chart;

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

const setupNavigationProgress = () => {
    const bar = document.querySelector('[data-nav-progress]');

    if (! bar) {
        return;
    }

    let timer = null;
    let progress = 10;

    const setProgress = (value) => {
        progress = clamp(value, 0, 100);
        bar.style.transform = `scaleX(${progress / 100})`;
    };

    const start = () => {
        clearInterval(timer);
        bar.dataset.active = 'true';
        setProgress(12);

        timer = setInterval(() => {
            if (progress < 85) {
                setProgress(progress + (progress < 40 ? 12 : 6));
            }
        }, 120);
    };

    const finish = () => {
        clearInterval(timer);
        setProgress(100);
        setTimeout(() => {
            bar.dataset.active = 'false';
            setProgress(0);
        }, 180);
    };

    document.addEventListener('livewire:navigate', start);
    document.addEventListener('livewire:navigated', finish);
};

document.addEventListener('DOMContentLoaded', setupNavigationProgress);
