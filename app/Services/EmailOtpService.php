<?php

namespace App\Services;

use App\Mail\EmailOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EmailOtpService
{
    public function isVerified(User $user): bool
    {
        return $user->email_verified_at !== null;
    }

    /**
     * Generate, store (hashed), and email a new OTP.
     *
     * @throws ValidationException
     */
    public function send(User $user, bool $force = false): void
    {
        if ($this->isVerified($user)) {
            return;
        }

        $cooldown = (int) config('email_otp.resend_cooldown_seconds', 60);
        if (! $force && $user->email_otp_sent_at && $user->email_otp_sent_at->gt(now()->subSeconds($cooldown))) {
            $secondsLeft = max(1, $cooldown - $user->email_otp_sent_at->diffInSeconds(now()));

            throw ValidationException::withMessages([
                'otp' => "Please wait {$secondsLeft} seconds before requesting a new code.",
            ]);
        }

        $length = max(4, min(8, (int) config('email_otp.length', 6)));
        $expiresMinutes = max(1, (int) config('email_otp.expires_minutes', 15));
        $otp = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);

        $user->forceFill([
            'email_otp_hash' => Hash::make($otp),
            'email_otp_expires_at' => now()->addMinutes($expiresMinutes),
            'email_otp_attempts' => 0,
            'email_otp_sent_at' => now(),
        ])->save();

        try {
            Mail::to($user->email)->send(new EmailOtpMail($user, $otp, $expiresMinutes));
        } catch (\Throwable $e) {
            Log::error('Failed to send email OTP via mailer', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mailer' => config('mail.default'),
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'otp' => 'We could not send the verification email. Please try again in a moment.',
            ]);
        }

        Log::info('Email OTP sent', [
            'user_id' => $user->id,
            'email' => $user->email,
            'expires_at' => $user->email_otp_expires_at?->toDateTimeString(),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function verify(User $user, string $otp): void
    {
        if ($this->isVerified($user)) {
            return;
        }

        $otp = preg_replace('/\s+/', '', trim($otp)) ?? '';

        if ($otp === '' || $user->email_otp_hash === null) {
            throw ValidationException::withMessages([
                'otp' => 'Enter the verification code we emailed you.',
            ]);
        }

        if ($user->email_otp_expires_at === null || $user->email_otp_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'otp' => 'This code has expired. Request a new one.',
            ]);
        }

        $maxAttempts = (int) config('email_otp.max_attempts', 5);
        if ((int) $user->email_otp_attempts >= $maxAttempts) {
            throw ValidationException::withMessages([
                'otp' => 'Too many incorrect attempts. Request a new code.',
            ]);
        }

        if (! Hash::check($otp, $user->email_otp_hash)) {
            $user->forceFill([
                'email_otp_attempts' => (int) $user->email_otp_attempts + 1,
            ])->save();

            $remaining = max(0, $maxAttempts - (int) $user->email_otp_attempts);

            throw ValidationException::withMessages([
                'otp' => $remaining > 0
                    ? "Incorrect code. {$remaining} attempt(s) left."
                    : 'Too many incorrect attempts. Request a new code.',
            ]);
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'email_otp_hash' => null,
            'email_otp_expires_at' => null,
            'email_otp_attempts' => 0,
            'email_otp_sent_at' => null,
        ])->save();
    }
}
