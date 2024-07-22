<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Notifications\TwoFactorCode;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Twilio\Rest\Client;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = User::where('email', $this->email)->first();

        // Periksa apakah kode sudah ada atau sudah kadaluarsa
        if (!$user->code || ($user->expire_at && $user->expire_at < now())) {
            $user->generateCode();
        }
        // send mail
        // $user->notify(new TwoFactorCode());

        // send mobile
        $message = "Jangan Beritahu kode ini kepada siapapun termasuk teman terdekat anda. Jaga Rahasia kode ini, OTP : " . $user->code;

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $twilio = new Client($sid, $token);

        $twilio->messages->create(
            'whatsapp:' . $user->phone,
            [
                'from' => 'whatsapp:' . config('services.twilio.twilio_number'),
                'body' => $message,
            ]
        );

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
