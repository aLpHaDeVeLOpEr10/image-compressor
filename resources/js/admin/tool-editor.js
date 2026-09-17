const form = document.querySelector('[data-tool-editor]');

/** Character counters and share image preview, used on the tool editor and the create child tool form. */
function initSettings(form) {
    /* Character counters for meta fields. */
    form.querySelectorAll('[data-char-count]').forEach((input) => {
        const counter = document.getElementById(input.dataset.charCount);
        const [min, max] = counter.dataset.recommended.split('-').map(Number);
        const update = () => {
            const length = input.value.length;
            counter.textContent = `${length} characters`;
            counter.classList.toggle('text-success-700', length >= min && length <= max);
            counter.classList.toggle('text-warning-800', length > 0 && (length < min || length > max));
        };
        input.addEventListener('input', update);
        update();
    });

    /* Preview a newly chosen share image. */
    const imageInput = form.querySelector('[data-image-input]');
    const imagePreview = form.querySelector('[data-image-preview]');

    imageInput.addEventListener('change', () => {
        const [file] = imageInput.files;
        if (!file) {
            return;
        }
        const image = document.createElement('img');
        image.src = URL.createObjectURL(file);
        image.alt = 'New page image';
        image.className = 'size-full object-cover';
        imagePreview.replaceChildren(image);
    });
}

if (form) {
    initSettings(form);
}

if (form?.querySelector('[data-field-list]')) {
    const list = form.querySelector('[data-field-list]');
    const template = form.querySelector('[data-field-template]');
    const emptyState = form.querySelector('[data-field-empty]');
    const MULTILINE_TYPES = ['textarea', 'html'];

    const rows = () => [...list.querySelectorAll('[data-field-row]')];

    const usageFor = (key, type) => (type === 'html' ? `{!! $content['${key}'] ?? '' !!}` : `{{ $content['${key}'] ?? '' }}`);

    /** Keep input names sequential so the server receives fields in their on-screen order. */
    const renumber = () => {
        rows().forEach((row, index) => {
            row.querySelector('[data-field-number]').textContent = `#${index + 1}`;
            row.querySelectorAll('[name^="fields["]').forEach((input) => {
                input.name = input.name.replace(/^fields\[[^\]]*\]/, `fields[${index}]`);
            });
        });
        emptyState.classList.toggle('hidden', rows().length > 0);
    };

    const updateUsage = (row) => {
        const key = row.querySelector('[data-field-key]').value.trim() || 'field_key';
        const type = row.querySelector('[data-field-type]').value;
        row.querySelector('[data-field-usage]').textContent = usageFor(key, type);
    };

    const setValueControl = (row, type) => {
        const current = row.querySelector('[data-field-value]');
        const wantsTextarea = MULTILINE_TYPES.includes(type);

        if (wantsTextarea !== (current.tagName === 'TEXTAREA')) {
            const replacement = document.createElement(wantsTextarea ? 'textarea' : 'input');
            if (wantsTextarea) {
                replacement.rows = 4;
            } else {
                replacement.type = 'text';
            }
            replacement.name = current.name;
            replacement.className = current.className;
            replacement.setAttribute('aria-label', 'Value');
            replacement.dataset.fieldValue = '';
            replacement.value = current.value;
            current.replaceWith(replacement);
        }

        row.querySelector('[data-field-value]').classList.toggle('font-mono', type === 'html');
        updateUsage(row);
    };

    const addRow = ({ key = '', value = '', type = 'text' } = {}) => {
        const row = template.content.firstElementChild.cloneNode(true);
        const typeSelect = row.querySelector('[data-field-type]');

        row.querySelector('[data-field-key]').value = key;
        typeSelect.value = [...typeSelect.options].some((option) => option.value === type) ? type : 'text';
        setValueControl(row, typeSelect.value);
        row.querySelector('[data-field-value]').value = value ?? '';
        updateUsage(row);
        list.append(row);
        renumber();

        return row;
    };

    form.querySelector('[data-field-add]').addEventListener('click', () => {
        addRow().querySelector('[data-field-key]').focus();
    });

    list.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-field-remove]');
        if (remove) {
            remove.closest('[data-field-row]').remove();
            renumber();
        }
    });

    list.addEventListener('input', (event) => {
        if (event.target.matches('[data-field-key]')) {
            updateUsage(event.target.closest('[data-field-row]'));
        }
    });

    list.addEventListener('change', (event) => {
        if (event.target.matches('[data-field-type]')) {
            setValueControl(event.target.closest('[data-field-row]'), event.target.value);
        }
    });

    /* Drag to reorder: rows only become draggable while the handle is held, so text inside inputs stays selectable. */
    let draggedRow = null;

    list.addEventListener('pointerdown', (event) => {
        const handle = event.target.closest('[data-field-handle]');
        if (handle) {
            handle.closest('[data-field-row]').draggable = true;
        }
    });

    list.addEventListener('dragstart', (event) => {
        draggedRow = event.target.closest('[data-field-row]');
        event.dataTransfer.effectAllowed = 'move';
        requestAnimationFrame(() => draggedRow?.classList.add('opacity-50'));
    });

    list.addEventListener('dragover', (event) => {
        if (!draggedRow) {
            return;
        }
        event.preventDefault();
        const after = rows()
            .filter((row) => row !== draggedRow)
            .find((row) => {
                const box = row.getBoundingClientRect();
                return event.clientY < box.top + box.height / 2;
            });
        list.insertBefore(draggedRow, after ?? null);
    });

    list.addEventListener('dragend', () => {
        if (draggedRow) {
            draggedRow.classList.remove('opacity-50');
            draggedRow.draggable = false;
            draggedRow = null;
            renumber();
        }
    });

    /* Keyboard reordering on the handle for people who cannot drag. */
    list.addEventListener('keydown', (event) => {
        const handle = event.target.closest('[data-field-handle]');
        if (!handle || !['ArrowUp', 'ArrowDown'].includes(event.key)) {
            return;
        }
        event.preventDefault();
        const row = handle.closest('[data-field-row]');
        if (event.key === 'ArrowUp' && row.previousElementSibling) {
            list.insertBefore(row, row.previousElementSibling);
        } else if (event.key === 'ArrowDown' && row.nextElementSibling) {
            list.insertBefore(row.nextElementSibling, row);
        }
        renumber();
        handle.focus();
    });

    /* Deep Edit: JSON export and import. */
    const deepEdit = form.querySelector('[data-deep-edit]');
    const deepEditToggle = form.querySelector('[data-deep-edit-toggle]');
    const jsonInput = form.querySelector('[data-json-input]');
    const jsonMessage = form.querySelector('[data-json-message]');

    deepEditToggle.addEventListener('click', () => {
        const isOpen = deepEdit.classList.toggle('hidden') === false;
        deepEditToggle.setAttribute('aria-expanded', String(isOpen));
    });

    const currentFields = () =>
        rows().map((row) => ({
            key: row.querySelector('[data-field-key]').value.trim(),
            value: row.querySelector('[data-field-value]').value,
            type: row.querySelector('[data-field-type]').value,
        }));

    const showMessage = (text, isError = false) => {
        jsonMessage.textContent = text;
        jsonMessage.classList.remove('hidden', 'text-danger-700', 'text-success-700');
        jsonMessage.classList.add(isError ? 'text-danger-700' : 'text-success-700');
    };

    form.querySelector('[data-json-download]').addEventListener('click', () => {
        const blob = new Blob([JSON.stringify(currentFields(), null, 2)], { type: 'application/json' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `${form.action.split('/').pop()}-content.json`;
        link.click();
        URL.revokeObjectURL(link.href);
    });

    const parseImport = () => {
        let data;
        try {
            data = JSON.parse(jsonInput.value);
        } catch {
            throw new Error('That is not valid JSON.');
        }

        if (Array.isArray(data)) {
            return data.map((entry) => {
                if (!entry || typeof entry.key !== 'string') {
                    throw new Error('Every array entry needs a "key" string.');
                }
                return { key: entry.key.trim(), value: entry.value == null ? '' : String(entry.value), type: entry.type };
            });
        }

        if (data && typeof data === 'object') {
            return Object.entries(data).map(([key, value]) => {
                const text = value == null ? '' : typeof value === 'object' ? JSON.stringify(value) : String(value);
                return { key: key.trim(), value: text, type: text.includes('\n') ? 'textarea' : undefined };
            });
        }

        throw new Error('Paste a JSON object or an array of {key, value, type} entries.');
    };

    const importJson = (mode) => {
        let entries;
        try {
            entries = parseImport();
        } catch (error) {
            showMessage(error.message, true);
            return;
        }

        if (mode === 'replace') {
            if (rows().length > 0 && !confirm('Replace all content fields with the imported JSON?')) {
                return;
            }
            rows().forEach((row) => row.remove());
        }

        let updated = 0;
        let added = 0;

        entries.forEach((entry) => {
            const existing = rows().find((row) => row.querySelector('[data-field-key]').value.trim() === entry.key);

            if (existing) {
                if (entry.type) {
                    existing.querySelector('[data-field-type]').value = entry.type;
                    setValueControl(existing, existing.querySelector('[data-field-type]').value);
                }
                existing.querySelector('[data-field-value]').value = entry.value;
                updated++;
            } else {
                addRow(entry);
                added++;
            }
        });

        renumber();
        showMessage(`Imported: ${added} added, ${updated} updated. Click Save changes to keep them.`);
    };

    form.querySelector('[data-json-merge]').addEventListener('click', () => importJson('merge'));
    form.querySelector('[data-json-replace]').addEventListener('click', () => importJson('replace'));

    /* Send all fields as one JSON value so pages with hundreds of keys are not cut off by PHP's max_input_vars. */
    form.addEventListener('submit', () => {
        const payload = document.createElement('input');
        payload.type = 'hidden';
        payload.name = 'fields_json';
        payload.value = JSON.stringify(currentFields());
        form.append(payload);
        form.querySelectorAll('[name^="fields["]').forEach((input) => {
            input.disabled = true;
        });
    });

    /* Undo the submit preparation when the page is restored from the back/forward cache. */
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            form.querySelectorAll('[name="fields_json"]').forEach((input) => input.remove());
            form.querySelectorAll('[name^="fields["]').forEach((input) => {
                input.disabled = false;
            });
        }
    });

    rows().forEach(updateUsage);
    renumber();
}
