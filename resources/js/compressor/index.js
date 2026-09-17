import { track } from '../analytics.js';
import { initComparison } from './comparison.js';
import { CompressionError, compressInBrowser, compressOnServer, setEngineMessages, supportsWebpEncoding, usesServer } from './engine.js';
import { detectMime, formatBytes, formatDimensions, formatPercent, FORMAT_LABELS, loadImage, outputFilename } from './utils.js';

/**
 * Fallback text for messages; the page passes the admin-managed versions in settings.messages.
 * :tokens are replaced with runtime values.
 */
const DEFAULT_MESSAGES = {
    unsupported: 'Please upload a JPG, PNG, or WebP image.',
    too_large: 'Your image exceeds the maximum allowed file size of :mb MB.',
    invalid: "We couldn't process this image. Please try another file.",
    too_many_pixels: 'This image has very large dimensions and cannot be processed. Please resize it and try again.',
    server_too_many_pixels: 'This image is too large to process on our server. Choose JPG output, or resize the image first.',
    failed: 'Something went wrong while compressing your image. Please try again.',
    custom_target: 'Enter a target between 5 and :max KB.',
    pasted_image: 'Pasted image',
    loaded: ':name loaded. :dimensions pixels, :size. Choose settings and compress.',
    target_enter: 'Enter a custom target size in KB.',
    target_summary: 'Target size: approximately :size. Quality is lowered first, and dimensions are reduced only if needed. The exact result can vary.',
    no_target: 'No target size. The image is compressed at the selected quality.',
    quality_target: 'With a target size, this is the highest quality that will be used.',
    quality_png: 'For PNG, lower quality uses fewer colours. 100% keeps the PNG lossless.',
    quality_default: 'Lower quality produces smaller files. 70–85% is a good balance for most photos.',
    same_format: 'Same (:format)',
    png_help: 'PNG output reduces colours to a palette of up to 256 and keeps transparency. For photos, JPG or WebP is usually much smaller.',
    png_server: 'Your browser cannot run PNG compression, so PNG output is processed on our server.',
    webp_help: 'WebP supports transparency and is often smaller than JPG or PNG.',
    webp_server: 'Your browser cannot create WebP files, so WebP output is processed on our server.',
    jpg_transparency: 'JPG does not support transparency. Transparent areas become white.',
    jpg_help: 'JPG is ideal for photos. Compression is done in your browser.',
    uploading: 'Uploading and compressing…',
    compressing_browser: 'Compressing in your browser…',
    compressing: 'Compressing…',
    saved: 'You saved :percent',
    unchanged: 'The file size is unchanged',
    larger: 'The result is :percent larger',
    detail: 'Original: :original · Compressed: :compressed · Saved: :saved',
    target_reached: 'Target size reached: approximately :target requested, :produced produced.',
    target_missed: 'This image could not be reduced to approximately :target. This is the smallest result we could produce.',
    resized: 'Dimensions were reduced from :from to :to to get close to the target size.',
    already_optimized: 'The original (:size) is already well optimized for these settings. Try a lower quality or a different output format, or keep your original file.',
    processed_server: 'Compressed on our server in memory. The image was not stored.',
    processed_browser: 'Compressed in your browser. The image was not uploaded.',
    ready: 'Ready for a new image.',
};

const fillTokens = (template, values = {}) => template.replace(/:([a-z_]+)/g, (token, name) => (name in values ? String(values[name]) : token));

class Compressor {
    constructor(root) {
        this.root = root;
        this.settings = JSON.parse(root.dataset.settings);
        this.messages = { ...DEFAULT_MESSAGES, ...(this.settings.messages ?? {}) };
        setEngineMessages(this.messages);
        this.state = { file: null, mime: null, image: null, sourceUrl: null, resultUrl: null, result: null };
        this.busy = false;
        this.job = 0;
        this.abortController = null;

        this.el = {
            stages: [...root.querySelectorAll('[data-stage]')],
            errorRegion: root.querySelector('[data-error-region]'),
            errorMessage: root.querySelector('[data-error-message]'),
            live: root.querySelector('[data-live-region]'),
            dropzone: root.querySelector('[data-dropzone]'),
            fileInput: root.querySelector('[data-file-input]'),
            preview: {
                image: root.querySelector('[data-preview-image]'),
                name: root.querySelector('[data-preview-name]'),
                dimensions: root.querySelector('[data-preview-dimensions]'),
                size: root.querySelector('[data-preview-size]'),
                format: root.querySelector('[data-preview-format]'),
            },
            form: root.querySelector('[data-controls]'),
            qualityInput: root.querySelector('[data-quality-input]'),
            qualityValue: root.querySelector('[data-quality-value]'),
            qualityHelp: root.querySelector('[data-quality-help]'),
            customTarget: root.querySelector('[data-custom-target]'),
            customTargetInput: root.querySelector('[data-custom-target-input]'),
            customTargetError: root.querySelector('[data-custom-target-error]'),
            targetSummary: root.querySelector('[data-target-summary]'),
            originalFormatLabel: root.querySelector('[data-original-format-label]'),
            outputHelp: root.querySelector('[data-output-help]'),
            compressButton: root.querySelector('[data-compress-button]'),
            compressLabel: root.querySelector('[data-compress-label]'),
            spinner: root.querySelector('[data-spinner]'),
            status: root.querySelector('[data-status]'),
            result: {
                icon: root.querySelector('[data-savings-icon]'),
                headline: root.querySelector('[data-savings-headline]'),
                detail: root.querySelector('[data-savings-detail]'),
                notices: root.querySelector('[data-notices]'),
                download: root.querySelector('[data-download]'),
                compareBefore: root.querySelector('[data-compare-before]'),
                compareAfter: root.querySelector('[data-compare-after]'),
                sideBefore: root.querySelector('[data-side-before]'),
                sideAfter: root.querySelector('[data-side-after]'),
                sideBeforeSize: root.querySelector('[data-side-before-size]'),
                sideAfterSize: root.querySelector('[data-side-after-size]'),
            },
        };

        this.originalFormatText = this.el.originalFormatLabel.textContent.trim();
        this.compressButtonText = this.el.compressLabel.textContent.trim();
        this.comparison = initComparison(root);
        this.bindEvents();
        this.syncControls();
        track('compressor_opened');
    }

    bindEvents() {
        const { dropzone, fileInput, form, qualityInput, customTargetInput, result } = this.el;

        fileInput.addEventListener('change', () => {
            if (fileInput.files?.[0]) {
                this.handleFile(fileInput.files[0], 'picker');
            }
        });

        dropzone.addEventListener('click', (event) => {
            if (!event.target.closest('label')) {
                fileInput.click();
            }
        });

        ['dragenter', 'dragover'].forEach((type) =>
            dropzone.addEventListener(type, (event) => {
                event.preventDefault();
                dropzone.dataset.dragging = '';
            }),
        );

        ['dragleave', 'dragend'].forEach((type) =>
            dropzone.addEventListener(type, (event) => {
                if (!dropzone.contains(event.relatedTarget)) {
                    delete dropzone.dataset.dragging;
                }
            }),
        );

        dropzone.addEventListener('drop', (event) => {
            event.preventDefault();
            delete dropzone.dataset.dragging;
            const file = event.dataTransfer?.files?.[0];

            if (file) {
                this.handleFile(file, 'drop');
            }
        });

        ['dragover', 'drop'].forEach((type) =>
            window.addEventListener(type, (event) => {
                if (event.dataTransfer?.types?.includes('Files') && !dropzone.contains(event.target)) {
                    event.preventDefault();
                }
            }),
        );

        document.addEventListener('paste', (event) => {
            const file = [...(event.clipboardData?.files ?? [])].find((item) => item.type.startsWith('image/'));
            const target = event.target;

            if (!file || this.busy || target.closest?.('input, textarea, [contenteditable="true"]')) {
                return;
            }

            event.preventDefault();
            this.handleFile(file, 'paste');
        });

        form.addEventListener('input', () => this.syncControls());
        form.addEventListener('change', () => this.syncControls());
        qualityInput.addEventListener('input', () => this.syncControls());
        customTargetInput.addEventListener('input', () => this.setCustomTargetError(null));

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            this.compress();
        });

        this.root.querySelectorAll('[data-reset]').forEach((button) => button.addEventListener('click', () => this.reset()));

        this.root.querySelector('[data-adjust]').addEventListener('click', () => {
            this.showStage('configure');
            this.el.compressButton.focus();
        });

        result.download.addEventListener('click', () => {
            track('image_downloaded', { format: this.state.result?.blob.type, processed_on: this.state.result?.processedOn });
        });
    }

    async handleFile(file, source) {
        if (this.busy) {
            return;
        }

        this.clearError();

        if (file.size > this.settings.maxBytes) {
            return this.fail(this.t('too_large', { mb: Math.round(this.settings.maxBytes / 1048576) }), 'too_large');
        }

        const mime = file.size > 0 ? await detectMime(file) : null;

        if (!mime) {
            return this.fail(this.t('unsupported'), 'unsupported_format');
        }

        const sourceUrl = URL.createObjectURL(file);
        let image;

        try {
            image = await loadImage(sourceUrl);
        } catch {
            URL.revokeObjectURL(sourceUrl);

            return this.fail(this.t('invalid'), 'invalid_image');
        }

        if (!image.naturalWidth || !image.naturalHeight) {
            URL.revokeObjectURL(sourceUrl);

            return this.fail(this.t('invalid'), 'invalid_image');
        }

        if (image.naturalWidth * image.naturalHeight > this.settings.maxPixels) {
            URL.revokeObjectURL(sourceUrl);

            return this.fail(this.t('too_many_pixels'), 'too_many_pixels');
        }

        this.releaseUrls();
        this.state = { file, mime, image, sourceUrl, resultUrl: null, result: null };

        const { preview } = this.el;
        preview.image.src = sourceUrl;
        preview.name.textContent = file.name || this.t('pasted_image');
        preview.name.title = file.name || '';
        preview.dimensions.textContent = formatDimensions(image.naturalWidth, image.naturalHeight);
        preview.size.textContent = formatBytes(file.size);
        preview.format.textContent = FORMAT_LABELS[mime];

        this.syncControls();
        this.showStage('configure');
        this.announce(this.t('loaded', { name: preview.name.textContent, dimensions: preview.dimensions.textContent, size: preview.size.textContent }));
        this.el.compressButton.focus({ preventScroll: true });

        track('image_uploaded', { format: FORMAT_LABELS[mime], size_kb: Math.round(file.size / 1024), source });
    }

    readSettings() {
        const data = new FormData(this.el.form);
        const target = data.get('target');
        const output = data.get('output');
        let targetKb = null;

        if (target === 'custom') {
            const value = Number(this.el.customTargetInput.value);
            targetKb = Number.isInteger(value) && value >= 5 && value <= this.settings.maxCustomTargetKb ? value : NaN;
        } else if (target) {
            targetKb = Number(target);
        }

        return {
            quality: Number(data.get('quality')),
            targetKb,
            targetMode: target,
            outputMime: output === 'original' || !output ? this.state.mime : output,
        };
    }

    syncControls() {
        const { quality, targetKb, targetMode, outputMime } = this.readSettings();
        const el = this.el;

        el.qualityValue.textContent = String(quality);
        el.customTarget.hidden = targetMode !== 'custom';

        if (targetMode === 'custom' && Number.isNaN(targetKb)) {
            el.targetSummary.textContent = this.t('target_enter');
        } else if (targetKb) {
            el.targetSummary.textContent = this.t('target_summary', { size: formatBytes(targetKb * 1024) });
        } else {
            el.targetSummary.textContent = this.t('no_target');
        }

        const isPng = outputMime === 'image/png';

        el.qualityHelp.textContent = targetKb
            ? this.t('quality_target')
            : isPng
              ? this.t('quality_png')
              : this.t('quality_default');

        el.originalFormatLabel.textContent = this.state.mime ? this.t('same_format', { format: FORMAT_LABELS[this.state.mime] }) : this.originalFormatText;

        const help = [];

        if (isPng) {
            help.push(this.t('png_help'));

            if (usesServer(outputMime)) {
                help.push(this.t('png_server'));
            }
        } else if (outputMime === 'image/webp') {
            help.push(this.t('webp_help'));

            if (!supportsWebpEncoding()) {
                help.push(this.t('webp_server'));
            }
        } else if (outputMime === 'image/jpeg' && this.state.mime && this.state.mime !== 'image/jpeg') {
            help.push(this.t('jpg_transparency'));
        } else if (outputMime === 'image/jpeg') {
            help.push(this.t('jpg_help'));
        }

        el.outputHelp.textContent = help.join(' ');
    }

    setCustomTargetError(message) {
        const { customTargetInput, customTargetError } = this.el;
        customTargetError.hidden = !message;
        customTargetError.textContent = message ?? '';
        customTargetInput.setAttribute('aria-invalid', message ? 'true' : 'false');
    }

    async compress() {
        if (this.busy || !this.state.file) {
            return;
        }

        this.clearError();
        const { quality, targetKb, outputMime } = this.readSettings();

        if (Number.isNaN(targetKb)) {
            this.setCustomTargetError(this.t('custom_target', { max: this.settings.maxCustomTargetKb }));
            this.el.customTargetInput.focus();

            return;
        }

        const { file, image } = this.state;
        const onServer = usesServer(outputMime);

        if (onServer && image.naturalWidth * image.naturalHeight > this.settings.serverMaxPixels) {
            return this.fail(this.t('server_too_many_pixels'), 'too_many_pixels');
        }

        const job = ++this.job;
        const targetBytes = targetKb ? targetKb * 1024 : null;
        this.setBusy(true, onServer ? this.t('uploading') : this.t('compressing_browser'));
        track('compression_started', { output: FORMAT_LABELS[outputMime], quality, target_kb: targetKb ?? 0, processed_on: onServer ? 'server' : 'browser' });

        try {
            let result;

            if (onServer) {
                this.abortController = new AbortController();
                result = await compressOnServer(file, {
                    endpoint: this.settings.endpoint,
                    mime: outputMime,
                    quality,
                    targetBytes,
                    signal: this.abortController.signal,
                });
            } else {
                result = await compressInBrowser(image, {
                    mime: outputMime,
                    quality,
                    targetBytes,
                    onProgress: (message) => {
                        if (job === this.job) {
                            this.el.status.textContent = `${message}…`;
                        }
                    },
                });
            }

            if (job !== this.job) {
                return;
            }

            this.showResult({ ...result, targetBytes });
        } catch (error) {
            if (job !== this.job || error.name === 'AbortError') {
                return;
            }

            const message = error instanceof CompressionError && error.message.includes(' ') ? error.message : this.t('failed');
            this.fail(message, 'compression_failed');
        } finally {
            if (job === this.job) {
                this.setBusy(false);
                this.abortController = null;
            }
        }
    }

    showResult(result) {
        const { file, image, sourceUrl, mime } = this.state;
        const el = this.el.result;

        if (this.state.resultUrl) {
            URL.revokeObjectURL(this.state.resultUrl);
        }

        const resultUrl = URL.createObjectURL(result.blob);
        this.state.resultUrl = resultUrl;
        this.state.result = result;

        const originalSize = file.size;
        const compressedSize = result.blob.size;
        const saved = ((originalSize - compressedSize) / originalSize) * 100;
        const smaller = compressedSize < originalSize;

        el.headline.textContent = smaller
            ? this.t('saved', { percent: formatPercent(saved) })
            : compressedSize === originalSize
              ? this.t('unchanged')
              : this.t('larger', { percent: formatPercent(saved) });
        el.detail.textContent = this.t('detail', { original: formatBytes(originalSize), compressed: formatBytes(compressedSize), saved: smaller ? formatPercent(saved) : '0%' });
        el.icon.className = smaller
            ? 'flex size-11 shrink-0 items-center justify-center rounded-full bg-success-50 text-success-700 ring-1 ring-success-600/20'
            : 'flex size-11 shrink-0 items-center justify-center rounded-full bg-warning-50 text-warning-800 ring-1 ring-warning-200';

        el.download.href = resultUrl;
        el.download.download = outputFilename(file.name, result.blob.type);

        el.compareBefore.src = sourceUrl;
        el.compareAfter.src = resultUrl;
        el.sideBefore.src = sourceUrl;
        el.sideAfter.src = resultUrl;
        el.sideBeforeSize.textContent = formatBytes(originalSize);
        el.sideAfterSize.textContent = formatBytes(compressedSize);

        const stats = {
            'original-size': formatBytes(originalSize),
            'original-dimensions': formatDimensions(image.naturalWidth, image.naturalHeight),
            'original-format': FORMAT_LABELS[mime],
            'compressed-size': formatBytes(compressedSize),
            'compressed-dimensions': formatDimensions(result.width, result.height),
            'compressed-format': FORMAT_LABELS[result.blob.type] ?? FORMAT_LABELS[mime],
        };

        Object.entries(stats).forEach(([key, value]) => {
            this.root.querySelector(`[data-stat="${key}"]`).textContent = value;
        });

        this.renderNotices(result, { smaller, originalSize, sourceWidth: image.naturalWidth, sourceHeight: image.naturalHeight });
        this.comparison.reset();
        this.showStage('result');

        el.headline.focus({ preventScroll: true });
        this.root.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
        this.announce(`${el.headline.textContent}. ${el.detail.textContent}`);

        track('compression_completed', {
            output: FORMAT_LABELS[result.blob.type],
            saved_percent: Math.round(saved * 10) / 10,
            target_met: result.targetMet,
            processed_on: result.processedOn,
        });
    }

    renderNotices(result, { smaller, originalSize, sourceWidth, sourceHeight }) {
        const notices = [];

        if (result.targetBytes && result.targetMet) {
            notices.push(['success', this.t('target_reached', { target: formatBytes(result.targetBytes), produced: formatBytes(result.blob.size) })]);
        } else if (result.targetBytes && result.targetMet === false) {
            notices.push(['warning', this.t('target_missed', { target: formatBytes(result.targetBytes) })]);
        }

        if (result.resized) {
            notices.push(['info', this.t('resized', { from: formatDimensions(sourceWidth, sourceHeight), to: formatDimensions(result.width, result.height) })]);
        }

        if (!smaller) {
            notices.push(['warning', this.t('already_optimized', { size: formatBytes(originalSize) })]);
        }

        notices.push(['info', result.processedOn === 'server'
            ? this.t('processed_server')
            : this.t('processed_browser')]);

        const styles = {
            success: 'border-success-600/25 bg-success-50 text-success-700',
            warning: 'border-warning-200 bg-warning-50 text-warning-800',
            info: 'border-line bg-canvas text-body',
        };

        this.el.result.notices.replaceChildren(
            ...notices.map(([type, text]) => {
                const item = document.createElement('li');
                item.className = `rounded-lg border px-3.5 py-2.5 text-sm ${styles[type]}`;
                item.textContent = text;

                return item;
            }),
        );
    }

    reset() {
        this.job++;
        this.abortController?.abort();
        this.abortController = null;
        this.setBusy(false);
        this.releaseUrls();
        this.state = { file: null, mime: null, image: null, sourceUrl: null, resultUrl: null, result: null };

        const { preview, result, fileInput, form } = this.el;
        [preview.image, result.compareBefore, result.compareAfter, result.sideBefore, result.sideAfter].forEach((img) => img.removeAttribute('src'));
        result.download.setAttribute('href', '#');
        result.notices.replaceChildren();
        this.root.querySelectorAll('[data-stat]').forEach((node) => (node.textContent = ''));

        fileInput.value = '';
        form.reset();
        this.setCustomTargetError(null);
        this.clearError();
        this.syncControls();
        this.comparison.reset();
        this.showStage('upload');
        this.announce(this.t('ready'));
        fileInput.focus({ preventScroll: true });
    }

    releaseUrls() {
        [this.state.sourceUrl, this.state.resultUrl].filter(Boolean).forEach((url) => URL.revokeObjectURL(url));
    }

    showStage(name) {
        this.el.stages.forEach((stage) => {
            stage.hidden = stage.dataset.stage !== name;
        });
    }

    setBusy(busy, message = '') {
        this.busy = busy;
        const { compressButton, compressLabel, spinner, status, form } = this.el;
        compressButton.disabled = busy;
        compressButton.setAttribute('aria-busy', String(busy));
        compressLabel.textContent = busy ? this.t('compressing') : this.compressButtonText;
        spinner.classList.toggle('hidden', !busy);
        status.textContent = message;
        form.querySelectorAll('input').forEach((input) => {
            input.disabled = busy;
        });
    }

    fail(message, code) {
        this.el.errorMessage.textContent = message;
        this.el.errorRegion.hidden = false;
        this.el.fileInput.value = '';
        track('compression_error', { error: code });
    }

    clearError() {
        this.el.errorRegion.hidden = true;
        this.el.errorMessage.textContent = '';
    }

    t(key, values) {
        return fillTokens(this.messages[key] ?? '', values);
    }

    announce(message) {
        this.el.live.textContent = message;
    }
}

document.querySelectorAll('[data-compressor]').forEach((root) => new Compressor(root));
