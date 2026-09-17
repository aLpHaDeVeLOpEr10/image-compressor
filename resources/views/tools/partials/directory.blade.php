@php
    $choices = \App\Tools\ToolContent::list($content, 'choose_row', ['task', 'label', 'url', 'why']);
@endphp

@if ($choices)
    <section class="section" aria-labelledby="choose-tool-heading">
        <x-ui.container class="max-w-4xl">
            <x-ui.section-heading id="choose-tool-heading" :title="$content['choose_heading'] ?? ''" :description="($content['choose_description'] ?? '') ?: null" />
            <div class="prose-content mt-8">
                <table>
                    <thead>
                        <tr><th>{{ $content['choose_column_task'] ?? '' }}</th><th>{{ $content['choose_column_use'] ?? '' }}</th><th>{{ $content['choose_column_why'] ?? '' }}</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($choices as $choice)
                            <tr>
                                <td>{{ $choice['task'] }}</td>
                                <td>
                                    @if ($choice['url'] !== '')
                                        <a href="{{ $choice['url'] }}">{{ $choice['label'] }}</a>
                                    @else
                                        {{ $choice['label'] }}
                                    @endif
                                </td>
                                <td>{{ $choice['why'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.container>
    </section>
@endif
