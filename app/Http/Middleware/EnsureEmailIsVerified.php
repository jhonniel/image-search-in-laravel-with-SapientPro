<?php

namespace App\Http\Middleware;

use App\Services\EmailOtpService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function __construct(private EmailOtpService $otpService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $this->otpService->isVerified($user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please verify your email address first.',
                ], 403);
            }

            return redirect()
                ->route('verification.notice')
                ->with('status', 'Please verify your email to continue.');
        }

        return $next($request);
    }
}
