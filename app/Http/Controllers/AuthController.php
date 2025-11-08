<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\ImageMetadata;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\SimilarityNotificationService;
use SapientPro\ImageComparator\ImageComparator;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ], [
            'name.required' => 'Full name is required',
            'name.max' => 'Name must not exceed 255 characters',
            'username.unique' => 'This username is already taken',
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'email.unique' => 'This email address is already registered',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
            'terms.required' => 'You must accept the terms and conditions',
            'terms.accepted' => 'You must accept the terms and conditions',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            Auth::login($user);

            // If there's a pending guest item in session, finalize it under this new account
            if ($request->session()->has('guest_pending_item')) {
                $pending = $request->session()->pull('guest_pending_item');
                try {
                    $uploadId = 'user_upload_' . Str::random(10);
                    foreach ($pending['files'] as $index => $storedPath) {
                        $filename = basename($storedPath);
                        // Move file from temp-guest to user-items
                        $targetPath = 'user-items/' . $filename;
                        if (Storage::disk('public')->exists($storedPath)) {
                            Storage::disk('public')->move($storedPath, $targetPath);
                        } else {
                            continue;
                        }
                        $metadata = ImageMetadata::create([
                            'filename' => $filename,
                            'file_path' => Storage::url($targetPath),
                            'original_name' => $filename,
                            'uploader_email' => $user->email,
                            'description' => $pending['description'],
                            'tags' => $pending['tags'] ? explode(',', $pending['tags']) : [],
                            'file_size' => 0,
                            'mime_type' => null,
                            'status' => $pending['item_type'],
                            'upload_id' => $uploadId,
                        ]);

                        // Check for similar images and notify involved users
                        try {
                            $similarityService = new SimilarityNotificationService(app(ImageComparator::class));
                            $similarityService->checkAndNotifySimilarities($metadata, $user->email);
                        } catch (\Throwable $e) {
                            Log::error('Similarity check failed for guest finalization: '.$e->getMessage());
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error('Failed to finalize guest item: '.$e->getMessage());
                }
            }

            return redirect()->intended('/user/dashboard')->with('success', 'Account created successfully! Welcome to FindITFast!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Check if user is admin based on email
            $user = Auth::user();

            // Redirect admin users to admin dashboard
            if (str_ends_with($user->email, 'admin@imagesearch.com') || str_contains(strtolower($user->email), 'admin')) {
                return redirect()->intended('/admin/dashboard');
            }

            // Redirect regular users to user dashboard
            return redirect()->intended('/user/dashboard');
        }

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
