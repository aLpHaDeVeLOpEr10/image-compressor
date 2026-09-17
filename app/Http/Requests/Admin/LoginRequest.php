<?php

namespace App\Http\Requests\Admin;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public const MAX_ATTEMPTS = 5;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to sign in an admin, locking the email and IP pair out after repeated failures.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = [...$this->safe()->only(['email', 'password']), 'is_admin' => true];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), 300);

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match an admin account.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $minutes = (int) ceil(RateLimiter::availableIn($this->throttleKey()) / 60);

        throw ValidationException::withMessages([
            'email' => "Too many sign-in attempts. Please try again in {$minutes} ".Str::plural('minute', $minutes).'.',
        ]);
    }

    private function throttleKey(): string
    {
        return 'admin-login|'.Str::transliterate(Str::lower((string) $this->input('email'))).'|'.$this->ip();
    }
}
