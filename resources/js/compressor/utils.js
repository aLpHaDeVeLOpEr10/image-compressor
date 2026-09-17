export const FORMAT_LABELS = {
    'image/jpeg': 'JPEG',
    'image/png': 'PNG',
    'image/webp': 'WebP',
};

const EXTENSIONS = {
    'image/jpeg': 'jpg',
    'image/png': 'png',
    'image/webp': 'webp',
};

export function formatBytes(bytes) {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    const kb = bytes / 1024;

    if (kb < 1024) {
        return `${kb < 100 ? kb.toFixed(1) : Math.round(kb)} KB`;
    }

    return `${(kb / 1024).toFixed(kb / 1024 < 10 ? 2 : 1)} MB`;
}

export function formatDimensions(width, height) {
    return `${width} × ${height}`;
}

export function formatPercent(value) {
    return `${Math.abs(value).toFixed(1)}%`;
}

/**
 * Identifies the real image type from the file's magic bytes instead of trusting its name or reported MIME type.
 */
export async function detectMime(file) {
    const bytes = new Uint8Array(await file.slice(0, 16).arrayBuffer());
    const ascii = (start, end) => String.fromCharCode(...bytes.slice(start, end));

    if (bytes[0] === 0xff && bytes[1] === 0xd8 && bytes[2] === 0xff) {
        return 'image/jpeg';
    }

    if ([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a].every((byte, i) => bytes[i] === byte)) {
        return 'image/png';
    }

    if (ascii(0, 4) === 'RIFF' && ascii(8, 12) === 'WEBP') {
        return 'image/webp';
    }

    return null;
}

export function outputFilename(originalName, mime) {
    const base = (originalName || 'image')
        .replace(/\.[^.]+$/, '')
        .normalize('NFKD')
        .replace(/[̀-ͯ]/g, '')
        .replace(/[^a-zA-Z0-9_-]+/g, '-')
        .replace(/-{2,}/g, '-')
        .replace(/^[-_]+|[-_]+$/g, '')
        .slice(0, 80) || 'image';

    return `${base}-compressed.${EXTENSIONS[mime]}`;
}

export function loadImage(src) {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.decoding = 'async';
        image.onload = () => resolve(image);
        image.onerror = () => reject(new Error('decode'));
        image.src = src;
    });
}
