@php
    $key = (string) ($field['key'] ?? '');
    $type = array_key_exists($field['type'] ?? '', $fieldTypes) ? $field['type'] : 'text';
    $value = (string) ($field['value'] ?? '');
    $isMultiline = in_array($type, ['textarea', 'html'], true);
    $keyError = $index !== null ? $errors->first("fields.{$index}.key") : '';
    $valueError = $index !== null ? $errors->first("fields.{$index}.value") : '';
    [$open, $close] = $type === 'html' ? ['{'.'!!', '!!'.'}'] : ['{'.'{', '}'.'}'];
    $usage = "{$open} \$content['{$key}'] ?? '' {$close}";
@endphp

<div class="rounded-xl border border-line bg-canvas/70 p-4" data-field-row>
    <div class="flex flex-wrap items-center gap-2 sm:flex-nowrap sm:gap-3">
        <button type="button" class="flex size-8 shrink-0 cursor-grab items-center justify-center rounded-md text-muted hover:bg-surface hover:text-ink active:cursor-grabbing" aria-label="Drag to reorder" title="Drag to reorder" data-field-handle>
            <x-ui.icon name="grip" class="size-4" />
        </button>
        <span class="w-8 shrink-0 text-sm font-semibold text-muted" data-field-number>#{{ ($index ?? 0) + 1 }}</span>
        <input type="text" aria-label="Key" name="fields[{{ $index ?? '__INDEX__' }}][key]" value="{{ $key }}" placeholder="field_key" required maxlength="100" autocomplete="off" spellcheck="false" class="form-input min-w-0 flex-1 bg-surface py-2 font-mono text-sm sm:max-w-64" @if ($keyError) aria-invalid="true" @endif data-field-key>
        <select aria-label="Type" name="fields[{{ $index ?? '__INDEX__' }}][type]" class="form-input w-auto bg-surface py-2 text-sm" data-field-type>
            @foreach ($fieldTypes as $typeKey => $typeLabel)
                <option value="{{ $typeKey }}" @selected($typeKey === $type)>{{ $typeLabel }}</option>
            @endforeach
        </select>
        <button type="button" class="ml-auto inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-danger-200 bg-surface px-3 py-1.5 text-xs font-semibold text-danger-700 hover:bg-danger-50" data-field-remove>
            <x-ui.icon name="trash" class="size-3.5" /> Remove
        </button>
    </div>

    @if ($keyError)
        <p class="mt-2 text-sm text-danger-700" data-field-error>{{ $keyError }}</p>
    @endif

    <div class="mt-3" data-field-value-wrapper>
        @if ($isMultiline)
            <textarea name="fields[{{ $index ?? '__INDEX__' }}][value]" rows="4" class="form-input bg-surface text-sm @if ($type === 'html') font-mono @endif" aria-label="Value" data-field-value>{{ $value }}</textarea>
        @else
            <input type="text" name="fields[{{ $index ?? '__INDEX__' }}][value]" value="{{ $value }}" class="form-input bg-surface text-sm" aria-label="Value" data-field-value>
        @endif
    </div>

    @if ($valueError)
        <p class="mt-2 text-sm text-danger-700">{{ $valueError }}</p>
    @endif

    <p class="mt-2 text-xs text-muted">
        Use in Blade: <code class="font-mono text-danger-700" data-field-usage>{{ $usage }}</code>
    </p>
</div>
