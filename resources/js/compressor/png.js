const HISTOGRAM_BITS = [5, 5, 5, 4];
const MAX_SAMPLES = 1_000_000;

let crcTable;

function crc32(bytes) {
    if (!crcTable) {
        crcTable = new Uint32Array(256);

        for (let n = 0; n < 256; n++) {
            let c = n;
            for (let k = 0; k < 8; k++) {
                c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1;
            }
            crcTable[n] = c >>> 0;
        }
    }

    let crc = 0xffffffff;
    for (let i = 0; i < bytes.length; i++) {
        crc = crcTable[(crc ^ bytes[i]) & 0xff] ^ (crc >>> 8);
    }

    return (crc ^ 0xffffffff) >>> 0;
}

function chunk(type, data) {
    const out = new Uint8Array(12 + data.length);
    const view = new DataView(out.buffer);
    view.setUint32(0, data.length);
    out.set([...type].map((c) => c.charCodeAt(0)), 4);
    out.set(data, 8);
    view.setUint32(8 + data.length, crc32(out.subarray(4, 8 + data.length)));

    return out;
}

async function deflate(bytes) {
    const stream = new Blob([bytes]).stream().pipeThrough(new CompressionStream('deflate'));

    return new Uint8Array(await new Response(stream).arrayBuffer());
}

export function supportsBrowserPng() {
    return typeof CompressionStream === 'function';
}

export function paletteSize(quality) {
    const ratio = Math.max(0, Math.min(1, (quality - 10) / 80));

    return Math.round(Math.min(256, Math.max(16, 2 ** (4 + 4 * ratio))));
}

function bucketKey(r, g, b, a) {
    return (r >> 3) | ((g >> 3) << 5) | ((b >> 3) << 10) | ((a >> 4) << 15);
}

function buildHistogram(pixels) {
    const size = 1 << HISTOGRAM_BITS.reduce((sum, bits) => sum + bits, 0);
    const count = new Uint32Array(size);
    const sums = [new Float64Array(size), new Float64Array(size), new Float64Array(size), new Float64Array(size)];
    const total = pixels.length / 4;
    const step = Math.max(1, Math.floor(total / MAX_SAMPLES));

    for (let p = 0; p < total; p += step) {
        const i = p * 4;
        const a = pixels[i + 3];

        if (a === 0) {
            continue;
        }

        const key = bucketKey(pixels[i], pixels[i + 1], pixels[i + 2], a);
        count[key]++;
        sums[0][key] += pixels[i];
        sums[1][key] += pixels[i + 1];
        sums[2][key] += pixels[i + 2];
        sums[3][key] += a;
    }

    const colors = [];
    for (let key = 0; key < size; key++) {
        if (count[key]) {
            const n = count[key];
            colors.push({ c: [sums[0][key] / n, sums[1][key] / n, sums[2][key] / n, sums[3][key] / n], n });
        }
    }

    return colors;
}

function boxStats(box) {
    let n = 0;
    const mean = [0, 0, 0, 0];
    for (const color of box) {
        n += color.n;
        for (let d = 0; d < 4; d++) mean[d] += color.c[d] * color.n;
    }
    for (let d = 0; d < 4; d++) mean[d] /= n || 1;

    const variance = [0, 0, 0, 0];
    for (const color of box) {
        for (let d = 0; d < 4; d++) variance[d] += color.n * (color.c[d] - mean[d]) ** 2;
    }

    return { n, mean, variance };
}

function medianCut(colors, target) {
    let boxes = [{ colors, stats: boxStats(colors) }];

    while (boxes.length < target) {
        let index = -1;
        let best = 0;
        boxes.forEach((box, i) => {
            const score = Math.max(...box.stats.variance);
            if (box.colors.length > 1 && score > best) {
                best = score;
                index = i;
            }
        });

        if (index === -1) {
            break;
        }

        const box = boxes[index];
        const dim = box.stats.variance.indexOf(Math.max(...box.stats.variance));
        const sorted = [...box.colors].sort((x, y) => x.c[dim] - y.c[dim]);
        let half = box.stats.n / 2;
        let split = 0;
        while (split < sorted.length - 1 && half > 0) {
            half -= sorted[split].n;
            split++;
        }
        split = Math.max(1, Math.min(sorted.length - 1, split));

        const left = sorted.slice(0, split);
        const right = sorted.slice(split);
        boxes.splice(index, 1, { colors: left, stats: boxStats(left) }, { colors: right, stats: boxStats(right) });
    }

    return boxes.map((box) => box.stats.mean);
}

function distance(a, b) {
    const wa = a[3] / 255;
    const wb = b[3] / 255;
    return (a[0] * wa - b[0] * wb) ** 2 + (a[1] * wa - b[1] * wb) ** 2 + (a[2] * wa - b[2] * wb) ** 2 + (a[3] - b[3]) ** 2;
}

function nearest(palette, color) {
    let best = 0;
    let bestDistance = Infinity;
    for (let i = 0; i < palette.length; i++) {
        const d = distance(palette[i], color);
        if (d < bestDistance) {
            bestDistance = d;
            best = i;
        }
    }
    return best;
}

function refine(palette, colors) {
    const iterations = Math.min(3, Math.floor(30_000_000 / (colors.length * palette.length)));

    for (let iteration = 0; iteration < iterations; iteration++) {
        const sums = palette.map(() => [0, 0, 0, 0, 0]);
        for (const color of colors) {
            const s = sums[nearest(palette, color.c)];
            for (let d = 0; d < 4; d++) s[d] += color.c[d] * color.n;
            s[4] += color.n;
        }
        palette = palette.map((entry, i) => (sums[i][4] ? sums[i].slice(0, 4).map((v) => v / sums[i][4]) : entry));
    }
    return palette;
}

/**
 * Reduces RGBA pixels to an indexed palette (median cut + k-means refinement) and encodes an indexed PNG,
 * keeping full and partial transparency via the tRNS chunk.
 */
export async function encodeIndexedPng(imageData, maxColors) {
    const { width, height, data } = imageData;
    const colors = buildHistogram(data);
    const hasTransparentPixels = (() => {
        for (let i = 3; i < data.length; i += 4) if (data[i] === 0) return true;
        return false;
    })();

    const budget = Math.max(2, maxColors - (hasTransparentPixels ? 1 : 0));
    let palette = colors.length <= budget ? colors.map((color) => color.c) : refine(medianCut(colors, budget), colors);
    palette = palette.map((entry) => entry.map((v) => Math.round(Math.max(0, Math.min(255, v)))));

    if (hasTransparentPixels) {
        palette.push([0, 0, 0, 0]);
    }

    const order = palette.map((entry, i) => i).sort((x, y) => palette[x][3] - palette[y][3]);
    palette = order.map((i) => palette[i]);
    const transparentIndex = hasTransparentPixels ? palette.findIndex((entry) => entry[3] === 0) : -1;

    const searchPalette = palette.map((entry, j) => (j === transparentIndex ? [1e9, 1e9, 1e9, 1e9] : entry));
    const cache = new Int16Array(1 << 19).fill(-1);
    const indices = new Uint8Array(width * height);
    const probe = [0, 0, 0, 0];

    for (let p = 0, i = 0; p < indices.length; p++, i += 4) {
        const a = data[i + 3];
        if (a === 0 && transparentIndex !== -1) {
            indices[p] = transparentIndex;
            continue;
        }
        const key = bucketKey(data[i], data[i + 1], data[i + 2], a);
        let index = cache[key];
        if (index === -1) {
            probe[0] = data[i];
            probe[1] = data[i + 1];
            probe[2] = data[i + 2];
            probe[3] = a;
            index = nearest(searchPalette, probe);
            cache[key] = index;
        }
        indices[p] = index;
    }

    const bitDepth = palette.length <= 2 ? 1 : palette.length <= 4 ? 2 : palette.length <= 16 ? 4 : 8;
    const rowBytes = Math.ceil((width * bitDepth) / 8);
    const raw = new Uint8Array((rowBytes + 1) * height);

    for (let y = 0; y < height; y++) {
        const rowStart = y * (rowBytes + 1);
        raw[rowStart] = 0;
        if (bitDepth === 8) {
            raw.set(indices.subarray(y * width, (y + 1) * width), rowStart + 1);
            continue;
        }
        for (let x = 0; x < width; x++) {
            const bit = x * bitDepth;
            const shift = 8 - bitDepth - (bit % 8);
            raw[rowStart + 1 + (bit >> 3)] |= indices[y * width + x] << shift;
        }
    }

    const header = new Uint8Array(13);
    const headerView = new DataView(header.buffer);
    headerView.setUint32(0, width);
    headerView.setUint32(4, height);
    header.set([bitDepth, 3, 0, 0, 0], 8);

    const plte = new Uint8Array(palette.length * 3);
    palette.forEach((entry, i) => plte.set(entry.slice(0, 3), i * 3));

    const lastTranslucent = palette.findLastIndex((entry) => entry[3] < 255);
    const parts = [
        new Uint8Array([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]),
        chunk('IHDR', header),
        chunk('PLTE', plte),
    ];

    if (lastTranslucent !== -1) {
        parts.push(chunk('tRNS', new Uint8Array(palette.slice(0, lastTranslucent + 1).map((entry) => entry[3]))));
    }

    parts.push(chunk('IDAT', await deflate(raw)), chunk('IEND', new Uint8Array(0)));

    return new Blob(parts, { type: 'image/png' });
}
