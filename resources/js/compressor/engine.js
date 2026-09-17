import { encodeIndexedPng, paletteSize, supportsBrowserPng } from './png.js';

const MIN_QUALITY = 10;
const PNG_LEVELS = [70, 50, 30, MIN_QUALITY];
const MAX_RESIZE_ROUNDS = 8;
const MIN_DIMENSION = 16;

export class CompressionError extends Error {}

const MESSAGES = {
    trying_quality: 'Trying quality :quality%',
    trying_colours: 'Trying :colours colours',
    reducing: 'Reducing dimensions to :width × :height',
    network: 'Could not reach the server. Please check your connection and try again.',
    session_expired: 'Your session has expired. Please refresh the page and try again.',
    server_too_large: 'Your image exceeds the maximum allowed file size.',
    failed: 'Something went wrong while compressing your image. Please try again.',
};

/** Use the page's admin-managed message text; :tokens are filled in with runtime values. */
export function setEngineMessages(messages) {
    Object.assign(MESSAGES, messages);
}

const message = (key, values = {}) => (MESSAGES[key] ?? '').replace(/:([a-z_]+)/g, (token, name) => (name in values ? String(values[name]) : token));

let webpEncodingSupported;

export function supportsWebpEncoding() {
    if (webpEncodingSupported === undefined) {
        const canvas = document.createElement('canvas');
        canvas.width = 1;
        canvas.height = 1;
        webpEncodingSupported = canvas.toDataURL('image/webp').startsWith('data:image/webp');
    }

    return webpEncodingSupported;
}

export function usesServer(outputMime) {
    return (outputMime === 'image/png' && !supportsBrowserPng()) || (outputMime === 'image/webp' && !supportsWebpEncoding());
}

function drawToCanvas(image, width, height, mime) {
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;

    const context = canvas.getContext('2d', { willReadFrequently: mime === 'image/png' });

    if (!context) {
        throw new CompressionError('canvas');
    }

    if (mime === 'image/jpeg') {
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, width, height);
    }

    context.imageSmoothingEnabled = true;
    context.imageSmoothingQuality = 'high';
    context.drawImage(image, 0, 0, width, height);

    return canvas;
}

let pngWorker = null;
let pngWorkerUnavailable = false;
let nextJobId = 0;
const pendingJobs = new Map();

function getPngWorker() {
    if (pngWorkerUnavailable || typeof Worker !== 'function') {
        return null;
    }

    if (!pngWorker) {
        try {
            pngWorker = new Worker(new URL('./png-worker.js', import.meta.url), { type: 'module' });
        } catch {
            pngWorkerUnavailable = true;

            return null;
        }

        pngWorker.addEventListener('message', ({ data }) => {
            const job = pendingJobs.get(data.id);
            pendingJobs.delete(data.id);

            if (job) {
                data.error ? job.reject(new Error(data.error)) : job.resolve(data.blob);
            }
        });

        pngWorker.addEventListener('error', () => {
            pngWorkerUnavailable = true;
            pendingJobs.forEach((job) => job.reject(new Error('worker')));
            pendingJobs.clear();
            pngWorker.terminate();
            pngWorker = null;
        });
    }

    return pngWorker;
}

/**
 * Encodes a palette PNG in a Web Worker when possible, falling back to the main thread.
 */
async function encodePalettePng(imageData, maxColors) {
    const worker = getPngWorker();

    if (worker) {
        try {
            return await new Promise((resolve, reject) => {
                const id = ++nextJobId;
                pendingJobs.set(id, { resolve, reject });
                worker.postMessage({ id, width: imageData.width, height: imageData.height, pixels: imageData.data, maxColors });
            });
        } catch {
            // The worker failed (for example, module workers are unsupported): encode on the main thread instead.
        }
    }

    return encodeIndexedPng(imageData, maxColors);
}

async function encode(canvas, mime, quality) {
    if (mime === 'image/png' && quality < 100) {
        const imageData = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height);
        const blob = await encodePalettePng(imageData, paletteSize(quality));

        return { blob, quality, width: canvas.width, height: canvas.height };
    }

    return new Promise((resolve, reject) => {
        canvas.toBlob(
            (blob) => {
                if (!blob || blob.type !== mime) {
                    reject(new CompressionError('encode'));
                    return;
                }

                resolve({ blob, quality, width: canvas.width, height: canvas.height });
            },
            mime,
            quality / 100,
        );
    });
}

async function bestWithinTarget(canvas, mime, maxQuality, targetBytes, onProgress) {
    onProgress?.(message('trying_quality', { quality: maxQuality }));
    const first = await encode(canvas, mime, maxQuality);

    if (first.blob.size <= targetBytes) {
        return { ...first, targetMet: true };
    }

    if (mime === 'image/png') {
        let last = first;

        for (const level of PNG_LEVELS.filter((q) => q < Math.min(maxQuality, 90))) {
            onProgress?.(message('trying_colours', { colours: paletteSize(level) }));
            last = await encode(canvas, mime, level);

            if (last.blob.size <= targetBytes) {
                return { ...last, targetMet: true };
            }
        }

        return { ...last, targetMet: false };
    }

    const lowest = await encode(canvas, mime, MIN_QUALITY);

    if (lowest.blob.size > targetBytes) {
        return { ...lowest, targetMet: false };
    }

    let best = lowest;
    let low = MIN_QUALITY + 1;
    let high = maxQuality - 1;

    while (low <= high) {
        const mid = Math.floor((low + high) / 2);
        onProgress?.(message('trying_quality', { quality: mid }));
        const attempt = await encode(canvas, mime, mid);

        if (attempt.blob.size <= targetBytes) {
            best = attempt;
            low = mid + 1;
        } else {
            high = mid - 1;
        }
    }

    return { ...best, targetMet: true };
}

function releaseCanvas(canvas) {
    canvas.width = 0;
    canvas.height = 0;
}

export async function compressInBrowser(image, { mime, quality, targetBytes, onProgress }) {
    const sourceWidth = image.naturalWidth;
    const sourceHeight = image.naturalHeight;
    let canvas = drawToCanvas(image, sourceWidth, sourceHeight, mime);

    try {
        if (!targetBytes) {
            const result = await encode(canvas, mime, quality);

            return { ...result, resized: false, targetMet: null, processedOn: 'browser' };
        }

        let best = null;

        for (let round = 0; round <= MAX_RESIZE_ROUNDS; round++) {
            const result = await bestWithinTarget(canvas, mime, quality, targetBytes, onProgress);

            if (!best || result.targetMet || result.blob.size < best.blob.size) {
                best = result;
            }

            if (result.targetMet) {
                break;
            }

            const factor = Math.max(0.5, Math.min(0.9, Math.sqrt(targetBytes / result.blob.size) * 0.95));
            const width = Math.floor(canvas.width * factor);
            const height = Math.floor(canvas.height * factor);

            if (width < MIN_DIMENSION || height < MIN_DIMENSION) {
                break;
            }

            onProgress?.(message('reducing', { width, height }));
            releaseCanvas(canvas);
            canvas = drawToCanvas(image, width, height, mime);
        }

        return {
            ...best,
            resized: best.width !== sourceWidth || best.height !== sourceHeight,
            processedOn: 'browser',
        };
    } finally {
        releaseCanvas(canvas);
    }
}

export async function compressOnServer(file, { endpoint, mime, quality, targetBytes, signal }) {
    const body = new FormData();
    body.append('image', file);
    body.append('output', mime);
    body.append('quality', String(quality));

    if (targetBytes) {
        body.append('target_kb', String(Math.round(targetBytes / 1024)));
    }

    let response;

    try {
        response = await fetch(endpoint, {
            method: 'POST',
            body,
            signal,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
        });
    } catch (error) {
        if (error.name === 'AbortError') {
            throw error;
        }

        throw new CompressionError(message('network'));
    }

    if (!response.ok) {
        throw new CompressionError(await serverErrorMessage(response));
    }

    const blob = await response.blob();
    const targetHeader = response.headers.get('X-Target-Met');

    return {
        blob: blob.type ? blob : new Blob([blob], { type: mime }),
        width: Number(response.headers.get('X-Image-Width')),
        height: Number(response.headers.get('X-Image-Height')),
        quality: Number(response.headers.get('X-Image-Quality')),
        resized: response.headers.get('X-Image-Resized') === '1',
        targetMet: targetHeader === '' || targetHeader === null ? null : targetHeader === '1',
        processedOn: 'server',
    };
}

async function serverErrorMessage(response) {
    if (response.status === 419) {
        return message('session_expired');
    }

    if (response.status === 413) {
        return message('server_too_large');
    }

    try {
        const data = await response.json();

        const firstFieldError = data?.errors ? Object.values(data.errors)[0]?.[0] : null;

        if (firstFieldError) {
            return firstFieldError;
        }

        if (data?.message && response.status < 500) {
            return data.message;
        }
    } catch {
        // Non-JSON error responses fall through to the generic message.
    }

    return message('failed');
}
