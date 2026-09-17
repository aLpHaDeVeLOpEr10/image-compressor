<x-layouts.app :seo="$seo">
    <x-ui.page-header
        title="Contact {{ config('site.brand') }}"
        description="Questions, feedback or a problem with the compressor? Send us a message."
        :breadcrumbs="$seo->breadcrumbs"
    />

    <x-ui.container class="py-12 sm:py-16">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <div class="card p-5 sm:p-8">
                @if (session('status'))
                    <x-ui.alert type="success" class="mb-6">{{ session('status') }}</x-ui.alert>
                @endif

                @error('form')
                    <x-ui.alert type="error" class="mb-6">{{ $message }}</x-ui.alert>
                @enderror

                @if ($errors->any() && ! $errors->has('form'))
                    <x-ui.alert type="error" class="mb-6">Please correct the highlighted fields and try again.</x-ui.alert>
                @endif

                <form method="POST" action="{{ route('pages.contact.store') }}" class="grid gap-5" novalidate>
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        @foreach (['name' => ['Name', 'text', 'name'], 'email' => ['Email', 'email', 'email']] as $field => [$label, $type, $autocomplete])
                            <div>
                                <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                                <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field) }}" autocomplete="{{ $autocomplete }}" required maxlength="{{ $field === 'name' ? 100 : 255 }}" class="form-input" @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
                                @error($field)<p id="{{ $field }}-error" class="mt-1.5 text-sm text-danger-700">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <label for="subject" class="form-label">Subject</label>
                        <input id="subject" name="subject" type="text" value="{{ old('subject') }}" required maxlength="150" class="form-input" @error('subject') aria-invalid="true" aria-describedby="subject-error" @enderror>
                        @error('subject')<p id="subject-error" class="mt-1.5 text-sm text-danger-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="message" class="form-label">Message</label>
                        <textarea id="message" name="message" rows="6" required maxlength="5000" class="form-input" @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ old('message') }}</textarea>
                        @error('message')<p id="message-error" class="mt-1.5 text-sm text-danger-700">{{ $message }}</p>@enderror
                    </div>

                    <div class="hidden" aria-hidden="true">
                        <label for="website">Leave this field empty</label>
                        <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs leading-5 text-muted">
                            We use your details only to respond. See our <a href="{{ route('pages.privacy-policy') }}" class="link">privacy policy</a>.
                        </p>
                        <x-ui.button type="submit" size="lg" icon="mail">Send Message</x-ui.button>
                    </div>
                </form>
            </div>

            <aside class="space-y-5">
                <div class="card p-5">
                    <h2 class="text-base font-semibold">Who you are writing to</h2>
                    <p class="mt-2 text-sm leading-6 text-muted">Messages go to the team that builds and runs {{ config('site.brand') }}. Learn more <a href="{{ route('pages.about') }}" class="link">about us</a>.</p>
                </div>
                <div class="card p-5">
                    <h2 class="text-base font-semibold">Before you write</h2>
                    <p class="mt-2 text-sm leading-6 text-muted">Many common questions about formats, quality and target sizes are answered in our <a href="{{ route('pages.faq') }}" class="link">FAQ</a>.</p>
                </div>
                <div class="card p-5">
                    <h2 class="text-base font-semibold">Reporting a problem?</h2>
                    <p class="mt-2 text-sm leading-6 text-muted">Tell us the image format, approximate file size, browser and device you used. Please don't attach private images.</p>
                </div>
            </aside>
        </div>
    </x-ui.container>
</x-layouts.app>
