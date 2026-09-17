import { encodeIndexedPng } from './png.js';

/**
 * Runs palette quantization and PNG encoding off the main thread so the page stays responsive.
 */
self.addEventListener('message', async ({ data: { id, width, height, pixels, maxColors } }) => {
    try {
        const blob = await encodeIndexedPng({ width, height, data: pixels }, maxColors);
        self.postMessage({ id, blob });
    } catch (error) {
        self.postMessage({ id, error: String(error?.message ?? error) });
    }
});
