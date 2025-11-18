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
    public function showLoginForm(Request $request)
    {
        // Check if user is already logged in
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if there's a redirect URL in session (from item page)
            $redirectUrl = $request->session()->pull('redirect_after_login', null);
            if ($redirectUrl) {
                return redirect($redirectUrl);
            }
            
            // Redirect based on user role
            return redirect('/dashboard');
        }
        
        // Check if redirecting from item page
        $itemId = $request->get('item');
        $redirectToItem = (!empty($itemId)) ? route('public.item.show', $itemId) : null;
        
        // Store item ID in session for redirect after login
        if ($itemId) {
            $request->session()->put('redirect_after_login', $redirectToItem);
        }
        
        return view('auth.login', compact('redirectToItem'));
    }

    public function showRegistrationForm(Request $request)
    {
        // Check if user is already logged in
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if there's a redirect URL in session (from item page)
            $redirectUrl = $request->session()->pull('redirect_after_register', null);
            if ($redirectUrl) {
                return redirect($redirectUrl);
            }
            
            // Redirect based on user role
            return redirect('/dashboard');
        }
        
        // Check if there's a pending guest item
        $hasPendingItem = $request->session()->has('guest_pending_item');
        
        // Check if redirecting from item page
        $itemId = $request->get('item');
        $redirectToItem = (!empty($itemId)) ? route('public.item.show', $itemId) : null;
        
        if ($hasPendingItem) {
            Log::info('Registration form shown with pending guest item', [
                'session_id' => $request->session()->getId(),
                'has_guest_pending_item' => true
            ]);
        }
        
        // Store item ID in session for redirect after registration
        if ($itemId) {
            $request->session()->put('redirect_after_register', $redirectToItem);
        }
        
        return view('auth.register', compact('hasPendingItem', 'redirectToItem'));
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
            // Determine role based on email
            $email = strtolower($request->email);
            $role = ($email === 'admin@finditfast.com' || str_contains($email, 'admin@')) ? 'admin' : 'user';
            
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $role,
            ]);

            Auth::login($user);

            // Process guest pending item if exists
            $itemsLinked = $this->processGuestPendingItem($request, $user);
            
            $successMessage = 'Account created successfully! Welcome to FindITFast!';
            if ($itemsLinked > 0) {
                $successMessage .= " Your {$itemsLinked} item(s) have been linked to your account.";
            }

            // Check if there's a redirect URL in session (from item page)
            $redirectUrl = $request->session()->pull('redirect_after_register', null);
            if ($redirectUrl) {
                return redirect($redirectUrl)->with('success', $successMessage);
            }

            // Redirect based on user role
            return redirect('/dashboard')->with('success', $successMessage);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Check if user is already logged in
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if there's a redirect URL in session (from item page)
            $redirectUrl = $request->session()->pull('redirect_after_login', null);
            if ($redirectUrl) {
                return redirect($redirectUrl);
            }
            
            // Redirect based on user role
            return redirect('/dashboard');
        }
        
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|min:6',
        ]);

        $identifier = trim($request->input('login'));
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // If identifier looks like an email
        if (str_contains($identifier, '@')) {
            if (Auth::attempt(['email' => $identifier, 'password' => $password], $remember)) {
                return $this->afterSuccessfulLogin($request);
            }
        } else {
            // Try username, then code_name, then email equal to identifier
            $user = User::where('username', $identifier)
                ->orWhere('code_name', $identifier)
                ->orWhere('email', $identifier)
                ->first();

            if ($user && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
                Auth::login($user, $remember);
                return $this->afterSuccessfulLogin($request);
            }
        }

        throw ValidationException::withMessages([
            'login' => 'The provided credentials do not match our records.',
        ]);
    }

    private function afterSuccessfulLogin(Request $request)
    {
        $user = Auth::user();
        // Link pending guest item before regenerating session
        $this->processGuestPendingItem($request, $user);
        $request->session()->regenerate();

        // Redirect to stored redirect first
        $redirectUrl = $request->session()->pull('redirect_after_login', null);
        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        // Redirect based on user role
        return redirect('/dashboard');
    }

    /**
     * Process guest pending item and link it to user account
     * Returns the number of items successfully linked
     */
    private function processGuestPendingItem(Request $request, User $user)
    {
        $itemsLinked = 0;
        
        // Log session info for debugging
        Log::info('Checking for guest pending item', [
            'session_id' => $request->session()->getId(),
            'has_guest_pending_item' => $request->session()->has('guest_pending_item'),
            'all_session_keys' => array_keys($request->session()->all()),
            'user_email' => $user->email,
            'user_id' => $user->id
        ]);
        
        // If there's a pending guest item in session, finalize it under this account
        if ($request->session()->has('guest_pending_item')) {
            $pending = $request->session()->pull('guest_pending_item');
            
            Log::info('Guest pending item found in session', [
                'item_type' => $pending['item_type'] ?? 'unknown',
                'files_count' => count($pending['files'] ?? []),
                'has_description' => !empty($pending['description']),
                'user_email' => $user->email,
                'user_id' => $user->id,
                'pending_data' => $pending
            ]);
            
            try {
                $uploadId = 'user_upload_' . Str::random(10);
                
                Log::info('Processing guest pending item on login/register', [
                    'user_email' => $user->email,
                    'user_id' => $user->id,
                    'item_type' => $pending['item_type'] ?? 'unknown',
                    'files_count' => count($pending['files'] ?? [])
                ]);
                
                foreach ($pending['files'] as $index => $storedPath) {
                    // $storedPath is already relative to public disk (e.g., 'temp-guest/filename.jpg')
                    Log::info('Processing file', [
                        'index' => $index,
                        'stored_path' => $storedPath,
                        'file_exists' => Storage::disk('public')->exists($storedPath)
                    ]);
                    
                    if (!Storage::disk('public')->exists($storedPath)) {
                        Log::warning('Guest file not found', [
                            'path' => $storedPath,
                            'full_path' => storage_path('app/public/' . $storedPath)
                        ]);
                        continue;
                    }
                    
                    // Get file info before moving
                    $fileSize = Storage::disk('public')->size($storedPath);
                    $mimeType = Storage::disk('public')->mimeType($storedPath);
                    
                    // Extract original filename from stored path (format: time_index_originalname)
                    $filename = basename($storedPath);
                    $originalName = $filename;
                    // Try to extract original name if it follows the pattern: time_index_originalname
                    if (preg_match('/^\d+_\d+_(.+)$/', $filename, $matches)) {
                        $originalName = $matches[1];
                    }
                    
                    // Move file from temp-guest to user-items
                    $targetPath = 'user-items/' . $filename;
                    $moved = Storage::disk('public')->move($storedPath, $targetPath);
                    
                    if (!$moved) {
                        Log::error('Failed to move guest file', [
                            'from' => $storedPath,
                            'to' => $targetPath,
                            'from_exists' => Storage::disk('public')->exists($storedPath),
                            'to_exists' => Storage::disk('public')->exists($targetPath)
                        ]);
                        continue;
                    }
                    
                    // Create metadata record with explicit user email
                    $metadataData = [
                        'filename' => $filename,
                        'file_path' => Storage::url($targetPath),
                        'original_name' => $originalName,
                        'uploader_email' => $user->email, // Explicitly set to user's email
                        'description' => $pending['description'] ?? '',
                        'location' => $pending['location'] ?? null, // Save location field
                        'tags' => !empty($pending['tags']) ? array_map('trim', explode(',', $pending['tags'])) : [],
                        'file_size' => $fileSize ?? 0,
                        'mime_type' => $mimeType,
                        'status' => $pending['item_type'] ?? 'lost',
                        'upload_id' => $uploadId,
                    ];
                    
                    // Only include province/city if they're provided in pending data
                    if (isset($pending['province']) && $pending['province'] !== null && $pending['province'] !== '') {
                        $metadataData['province'] = $pending['province'];
                    }
                    if (isset($pending['city']) && $pending['city'] !== null && $pending['city'] !== '') {
                        $metadataData['city'] = $pending['city'];
                    }
                    
                    $metadata = ImageMetadata::create($metadataData);
                    
                    $itemsLinked++;
                    
                    Log::info('Guest item metadata created successfully', [
                        'metadata_id' => $metadata->id,
                        'upload_id' => $uploadId,
                        'uploader_email' => $metadata->uploader_email,
                        'user_email' => $user->email,
                        'user_id' => $user->id,
                        'status' => $metadata->status,
                        'description' => $metadata->description
                    ]);
                    
                    // Verify the record was created correctly
                    $verify = ImageMetadata::find($metadata->id);
                    if ($verify && $verify->uploader_email === $user->email) {
                        Log::info('Verified: Item linked correctly to user', [
                            'metadata_id' => $verify->id,
                            'uploader_email' => $verify->uploader_email,
                            'user_email' => $user->email
                        ]);
                    } else {
                        Log::error('VERIFICATION FAILED: Item not linked correctly', [
                            'metadata_id' => $metadata->id,
                            'stored_uploader_email' => $verify ? $verify->uploader_email : 'NOT FOUND',
                            'expected_user_email' => $user->email
                        ]);
                    }

                    // Check for similar images and notify involved users
                    try {
                        $similarityService = new SimilarityNotificationService(app(ImageComparator::class));
                        $similarityService->checkAndNotifySimilarities($metadata, $user->email);
                    } catch (\Throwable $e) {
                        Log::error('Similarity check failed for guest finalization: '.$e->getMessage());
                    }
                }
                
                Log::info('Guest item finalization completed', [
                    'user_email' => $user->email,
                    'user_id' => $user->id,
                    'upload_id' => $uploadId,
                    'items_linked' => $itemsLinked
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to finalize guest item: '.$e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'user_id' => $user->id,
                    'exception_class' => get_class($e)
                ]);
            }
        } else {
            Log::warning('No guest pending item found in session during registration/login', [
                'user_email' => $user->email,
                'user_id' => $user->id,
                'session_id' => $request->session()->getId()
            ]);
        }
        
        return $itemsLinked;
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
