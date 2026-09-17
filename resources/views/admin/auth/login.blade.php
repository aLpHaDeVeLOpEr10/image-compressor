<!DOCTYPE html>
<html lang="{{ config('site.locale') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login | {{ config('site.brand') }}</title>

    @fonts
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-canvas px-4 py-12">
    <main class="w-full max-w-sm">
        <div class="mb-8 flex justify-center">
            <x-site.logo />
        </div>

        <div class="card p-6 sm:p-8">
            <h1 class="text-xl font-bold tracking-tight">Admin login</h1>
            <p class="mt-1 text-sm text-muted">Sign in to manage {{ config('site.brand') }}.</p>

            @if ($errors->any())
                <x-ui.alert type="error" class="mt-6">{{ $errors->first() }}</x-ui.alert>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 grid gap-5">
                @csrf

                <div>
                    <label for="email" class="form-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus class="form-input" @error('email') aria-invalid="true" @enderror>
                </div>

                <div>
                    <label for="password" class="form-label">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="form-input" @error('password') aria-invalid="true" @enderror>
                </div>

                <label class="flex items-center gap-2 text-sm text-body">
                    <input type="checkbox" name="remember" value="1" class="size-4 rounded border-line-strong accent-brand-600" @checked(old('remember'))>
                    Remember me
                </label>

                <x-ui.button type="submit" size="lg" icon="lock" class="w-full">Sign in</x-ui.button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm">
            <a href="{{ route('home') }}" class="link">Back to {{ config('site.brand') }}</a>
        </p>
    </main>
</body>
</html>
