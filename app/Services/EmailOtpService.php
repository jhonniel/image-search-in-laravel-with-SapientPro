<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;

class EmailOtpService
{
    public function isVerified(User $user): bool
    {
        return $user->email_verified_at !== null;
    }

    /**
     * Generate, store (hashed), and email a new OTP via Resend.
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
            $secondsLeft = max(1, (int) ceil($cooldown - $user->email_otp_sent_at->diffInSeconds(now())));

            throw ValidationException::withMessages([
                'otp' => "Please wait {$secondsLeft} seconds before requesting a new code.",
            ]);
        }

        $this->assertMailConfigured();

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
            $messageId = $this->deliverViaResend($user, $otp, $expiresMinutes);
        } catch (\Throwable $e) {
            Log::error('Failed to send email OTP via Resend', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mailer' => config('mail.default'),
                'from' => config('mail.from.address'),
                'error' => $e->getMessage(),
            ]);

            // Clear the unused code so the user can request a fresh one.
            $user->forceFill([
                'email_otp_hash' => null,
                'email_otp_expires_at' => null,
                'email_otp_attempts' => 0,
                'email_otp_sent_at' => null,
            ])->save();

            throw ValidationException::withMessages([
                'otp' => 'We could not send the verification email: '.$this->friendlyMailError($e),
            ]);
        }

        Log::info('Email OTP sent', [
            'user_id' => $user->id,
            'email' => $user->email,
            'resend_id' => $messageId,
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
                'otp' => 'Enter the verification code we emailed you. If you did not get one, tap Resend code.',
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

    /**
     * @throws ValidationException
     */
    private function assertMailConfigured(): void
    {
        $key = $this->resendApiKey();
        $from = trim((string) config('mail.from.address', env('MAIL_FROM_ADDRESS')));

        if ($key === '') {
            throw ValidationException::withMessages([
                'otp' => 'Resend API key is missing. Set RESEND_KEY in .env.',
            ]);
        }

        if ($from === '' || str_contains($from, 'example.com')) {
            throw ValidationException::withMessages([
                'otp' => 'Set MAIL_FROM_ADDRESS to a verified Resend sender (not example.com).',
            ]);
        }
    }

    /**
     * Always send through Resend's HTTP API using RESEND_KEY from .env.
     * Does not use Laravel SMTP/log mailers.
     */
    private function deliverViaResend(User $user, string $otp, int $expiresMinutes): ?string
    {
        $fromAddress = trim((string) config('mail.from.address', env('MAIL_FROM_ADDRESS')));
        $fromName = trim((string) (config('mail.from.name') ?: env('MAIL_FROM_NAME', 'FindITFast')));
        $from = $fromName !== '' ? "{$fromName} <{$fromAddress}>" : $fromAddress;

        $html = View::make('emails.email-otp', [
            'user' => $user,
            'otp' => $otp,
            'expiresMinutes' => $expiresMinutes,
        ])->render();

        // resend/resend-php exposes a global \Resend class (files autoload), not Resend\Resend.
        if (! class_exists('Resend', true)) {
            throw new \RuntimeException(
                'Resend SDK is not installed. Run: composer require resend/resend-php'
            );
        }

        $client = \Resend::client($this->resendApiKey());
        $result = $client->emails->send([
            'from' => $from,
            'to' => [$user->email],
            'subject' => 'Your FindITFast verification code',
            'html' => $html,
        ]);

        // SDK may return array or object depending on version.
        if (is_array($result)) {
            if (! empty($result['statusCode']) && (int) $result['statusCode'] >= 400) {
                throw new \RuntimeException($result['message'] ?? 'Resend rejected the email.');
            }

            return $result['id'] ?? null;
        }

        if (is_object($result)) {
            if (isset($result->statusCode) && (int) $result->statusCode >= 400) {
                throw new \RuntimeException($result->message ?? 'Resend rejected the email.');
            }

            return $result->id ?? null;
        }

        return null;
    }

    private function resendApiKey(): string
    {
        return trim((string) (
            config('services.resend.key')
            ?: env('RESEND_KEY', '')
        ));
    }

    private function friendlyMailError(\Throwable $e): string
    {
        $message = trim($e->getMessage());
        if ($message === '') {
            return 'please try again in a moment.';
        }

        // Keep it short for the UI.
        return mb_strlen($message) > 180 ? mb_substr($message, 0, 177).'...' : $message;
    }
}
