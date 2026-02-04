<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faq;
use App\Models\ContactHelpSection;
use App\Models\ImageMetadata;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WelcomeController extends Controller
{
    /**
     * Display the welcome page with fresh reports or search results
     */
    public function index(Request $request)
    {
        $searchQuery = $request->get('q', '');
        $statusFilter = $request->get('status', ''); // 'lost' or 'found'
        $isSearch = false;
        
        // If there's a search query, perform search
        if (!empty($searchQuery)) {
            $searchResults = $this->performSearch($searchQuery, $statusFilter);
            $freshReports = $searchResults;
            $isSearch = true;
        } else {
            // Get fresh reports (latest 8 items, grouped by upload_id)
            // Exclude claimed items and pending claims - they are only visible to admins/owners
            $freshReports = ImageMetadata::where(function($query) {
                    $query->where(function($q){
                        $q->where('is_claimed', false)
                          ->orWhereNull('is_claimed');
                    })
                          ->where(function($q) {
                              $q->whereNull('claim_verification_status')
                                ->orWhere('claim_verification_status', '!=', 'pending');
                          });
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('upload_id')
                ->take(8)
                ->map(function ($itemGroup) {
                    return $this->formatItemGroup($itemGroup);
                })
                ->values();
        }
        
        // Get statistics
        $totalLostReports = ImageMetadata::where('status', 'lost')
            ->select('upload_id')
            ->distinct()
            ->count('upload_id');
        
        $totalItemsReunited = ImageMetadata::whereNotNull('is_claimed')
            ->where('is_claimed', true)
            ->select('upload_id')
            ->distinct()
            ->count('upload_id');
        
        // Count unique locations from descriptions (simplified - count distinct upload_ids as proxy)
        $totalLocations = ImageMetadata::select('upload_id')
            ->distinct()
            ->count('upload_id');
        
        // Get top helpers (users who have claimed/returned the most items)
        // These are users who found lost items and returned them
        $topHelpers = ImageMetadata::where('is_claimed', true)
            ->whereNotNull('claimed_by_email')
            ->select('claimed_by_email', DB::raw('COUNT(DISTINCT upload_id) as returned_count'))
            ->groupBy('claimed_by_email')
            ->orderBy('returned_count', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                $user = User::where('email', $item->claimed_by_email)->first();
                return [
                    'name' => $user ? $this->getDisplayName($user->name) : 'Unknown',
                    'initial' => $user ? strtoupper(substr($user->name, 0, 2)) : 'UN',
                    'city' => $user ? ($user->location ?? $this->extractCityFromEmail($item->claimed_by_email)) : 'Unknown',
                    'returned_count' => $item->returned_count,
                    'profile_picture' => $user && $user->profile_picture ? $user->profile_picture : null,
                ];
            });
        
        // Get sponsors if carousel is enabled
        $showSponsors = \App\Models\Setting::get('show_sponsors_carousel', false);
        $sponsors = $showSponsors ? \App\Models\Sponsor::getActive() : collect([]);
        
        // Get social media links
        $socialLinks = [
            'facebook' => \App\Models\Setting::get('social_facebook', ''),
            'instagram' => \App\Models\Setting::get('social_instagram', ''),
            'twitter' => \App\Models\Setting::get('social_twitter', ''),
            'linkedin' => \App\Models\Setting::get('social_linkedin', ''),
            'youtube' => \App\Models\Setting::get('social_youtube', ''),
            'tiktok' => \App\Models\Setting::get('social_tiktok', ''),
        ];

        $contactEmail = Setting::get('contact_email', 'fif@ifinditfast.com');
        $contactWebsite = Setting::get('contact_website', 'finditfast.com');
        $contactSupportHours = Setting::get('contact_support_hours', 'Mon - Sat · 8AM - 8PM PHT');
        $contactEmailHelpText = Setting::get('contact_email_help_text', 'Expect a reply within 24 hours.');
        $contactHelpSections = ContactHelpSection::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $faqs = Faq::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->orderBy('created_at', 'asc')
            ->get(['question', 'answer']);

        if ($faqs->isEmpty()) {
            $faqs = collect([
                ['question' => 'How does FindITFast work?', 'answer' => 'Report a lost or found item with photos, and we notify users with matching posts so they can connect securely.'],
                ['question' => 'Do I need an account to post?', 'answer' => 'You can browse without signing in, but an account lets you post items, track updates, and message other members.'],
                ['question' => 'Is FindITFast available nationwide?', 'answer' => 'Yes, you can search and post items across all supported provinces and major cities in the Philippines.'],
            ])->map(fn ($faq) => (object) $faq);
        }

        return view('welcome', compact(
            'freshReports',
            'totalLostReports',
            'totalItemsReunited',
            'totalLocations',
            'topHelpers',
            'showSponsors',
            'sponsors',
            'searchQuery',
            'statusFilter',
            'isSearch',
            'socialLinks',
            'contactEmail',
            'contactWebsite',
            'faqs',
            'contactHelpSections',
            'contactSupportHours',
            'contactEmailHelpText'
        ));
    }
    
    /**
     * Perform search across items
     */
    private function performSearch($query, $statusFilter = '')
    {
        // Build query
        // Exclude claimed items and pending claims - they are only visible to admins/owners
        $baseQuery = ImageMetadata::where(function($query) {
            $query->where(function($q){
                $q->where('is_claimed', false)
                  ->orWhereNull('is_claimed');
            })
                  ->where(function($q) {
                      $q->whereNull('claim_verification_status')
                        ->orWhere('claim_verification_status', '!=', 'pending');
                  });
        });
        
        // Apply status filter if provided
        if (!empty($statusFilter) && in_array($statusFilter, ['lost', 'found'])) {
            $baseQuery->where('status', $statusFilter);
        }
        
        // Split query into keywords for better search
        $keywords = array_filter(explode(' ', trim($query)));
        
        // Search in description, tags, and original_name
        $baseQuery->where(function ($q) use ($query, $keywords) {
            // Search in description (full query)
            $q->where('description', 'like', '%' . $query . '%')
              ->orWhere('original_name', 'like', '%' . $query . '%');
            
            // Also search for individual keywords in description and original_name
            foreach ($keywords as $keyword) {
                if (strlen(trim($keyword)) > 0) {
                    $q->orWhere('description', 'like', '%' . trim($keyword) . '%')
                      ->orWhere('original_name', 'like', '%' . trim($keyword) . '%');
                }
            }
            
            // Search in tags - use JSON string search for database compatibility
            // Tags are stored as JSON, so we search the JSON string representation
            $q->orWhere('tags', 'like', '%' . $query . '%');
            foreach ($keywords as $keyword) {
                if (strlen(trim($keyword)) > 0) {
                    $q->orWhere('tags', 'like', '%' . trim($keyword) . '%');
                }
            }
            
            // Try JSON contains for databases that support it (MySQL 5.7+, PostgreSQL)
            // PostgreSQL uses JSONB operators, Laravel handles this automatically
            $driver = DB::connection()->getDriverName();
            if (in_array($driver, ['mysql', 'pgsql'])) {
                try {
                    // For PostgreSQL, whereJsonContains works with JSON/JSONB columns
                    $q->orWhereJsonContains('tags', $query);
                    foreach ($keywords as $keyword) {
                        if (strlen(trim($keyword)) > 0) {
                            $q->orWhereJsonContains('tags', trim($keyword));
                        }
                    }
                } catch (\Exception $e) {
                    // If JSON contains fails, the LIKE search above will still work
                    // Log for debugging but don't break the query
                    \Log::debug('JSON contains query failed, using LIKE fallback', [
                        'driver' => $driver,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });
        
        // Get results grouped by upload_id
        $results = $baseQuery->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('upload_id')
            ->map(function ($itemGroup) {
                return $this->formatItemGroup($itemGroup);
            })
            ->values();
        
        return $results;
    }
    
    /**
     * API endpoint for live search (returns JSON)
     */
    public function searchApi(Request $request)
    {
        $searchQuery = $request->get('q', '');
        $statusFilter = $request->get('status', '');
        
        if (empty($searchQuery)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required',
                'results' => []
            ]);
        }
        
        $searchResults = $this->performSearch($searchQuery, $statusFilter);
        
        return response()->json([
            'success' => true,
            'query' => $searchQuery,
            'status_filter' => $statusFilter,
            'count' => $searchResults->count(),
            'results' => $searchResults->toArray()
        ]);
    }
    
    /**
     * Format an item group for display
     */
    private function formatItemGroup($itemGroup)
    {
        $firstItem = $itemGroup->first();
        
        // Get the first image from this item group
        $firstImage = $itemGroup->first();
        $imagePath = null;
        
        // Get image path - normalize using Storage::url() for consistent paths
        if ($firstImage->file_path && trim($firstImage->file_path) !== '') {
            $filePath = trim($firstImage->file_path);
            
            // Normalize the path - ensure it starts with /storage/
            if (empty($filePath)) {
                $imagePath = '';
            } elseif (str_starts_with($filePath, '/storage/')) {
                // Already in correct format, use as is
                $imagePath = $filePath;
            } elseif (str_starts_with($filePath, 'storage/')) {
                // Missing leading slash, add it
                $imagePath = '/' . $filePath;
            } elseif (str_starts_with($filePath, 'http')) {
                // Full URL, use as is
                $imagePath = $filePath;
            } else {
                // Relative path, use Storage::url to generate proper path
                // If path contains user-items, construct properly
                if (str_contains($filePath, 'user-items/')) {
                    $imagePath = '/storage/' . ltrim($filePath, '/');
                } else {
                    // Try to use Storage::url() if it's a storage path
                    try {
                        $imagePath = Storage::url($filePath);
                    } catch (\Exception $e) {
                        // Fallback: construct path from filename
                        $imagePath = '/storage/user-items/' . basename($filePath);
                    }
                }
            }
        } elseif ($firstImage->filename && trim($firstImage->filename) !== '') {
            // Fallback: construct path from filename
            $imagePath = '/storage/user-items/' . trim($firstImage->filename);
        }
        
        // Final fallback: if still no path, try to construct from any available data
        if (!$imagePath && $firstImage->id) {
            // Last resort: try to find any image in the group with a valid path
            foreach ($itemGroup as $item) {
                if ($item->file_path && trim($item->file_path) !== '') {
                    $filePath = trim($item->file_path);
                    
                    // Normalize the path
                    if (str_starts_with($filePath, '/storage/')) {
                        $imagePath = $filePath;
                    } elseif (str_starts_with($filePath, 'storage/')) {
                        $imagePath = '/' . $filePath;
                    } elseif (str_starts_with($filePath, 'http')) {
                        $imagePath = $filePath;
                    } else {
                        if (str_contains($filePath, 'user-items/')) {
                            $imagePath = '/storage/' . ltrim($filePath, '/');
                        } else {
                            $imagePath = '/storage/user-items/' . basename($filePath);
                        }
                    }
                    break;
                } elseif ($item->filename && trim($item->filename) !== '') {
                    $imagePath = '/storage/user-items/' . trim($item->filename);
                    break;
                }
            }
        }
        
        // Use location from database, or extract from description as fallback
        $location = $firstItem->location;
        if (empty($location)) {
            // Fallback: try to extract location from description if not saved
            $location = $this->extractLocation($firstItem->description);
        }
        if (empty($location)) {
            $location = 'Location not specified';
        }
        
        // Format time ago
        $timeAgo = $firstItem->created_at->diffForHumans();
        
        // Ensure upload_id exists, if not use item id as fallback
        $uploadId = $firstItem->upload_id ?? 'item-' . $firstItem->id;
        
        return [
            'upload_id' => $uploadId,
            'title' => $this->extractTitle($firstItem->description),
            'location' => $location,
            'type' => $firstItem->status,
            'image_path' => $imagePath,
            'time_ago' => $timeAgo,
            'created_at' => $firstItem->created_at,
        ];
    }
    
    /**
     * Get item icon based on description and tags
     */
    private function getItemIcon($description, $tags)
    {
        $text = strtolower($description . ' ' . (is_array($tags) ? implode(' ', $tags) : ''));
        
        if (str_contains($text, 'wallet') || str_contains($text, 'money')) {
            return 'fa-wallet';
        } elseif (str_contains($text, 'backpack') || str_contains($text, 'bag')) {
            return 'fa-backpack';
        } elseif (str_contains($text, 'phone') || str_contains($text, 'iphone') || str_contains($text, 'mobile')) {
            return 'fa-mobile-alt';
        } elseif (str_contains($text, 'id') || str_contains($text, 'card')) {
            return 'fa-id-card';
        } elseif (str_contains($text, 'key')) {
            return 'fa-key';
        } elseif (str_contains($text, 'watch')) {
            return 'fa-clock';
        } elseif (str_contains($text, 'laptop') || str_contains($text, 'computer')) {
            return 'fa-laptop';
        } elseif (str_contains($text, 'book') || str_contains($text, 'notebook')) {
            return 'fa-book';
        } else {
            return 'fa-box';
        }
    }
    
    /**
     * Extract title from description
     */
    private function extractTitle($description)
    {
        if (empty($description)) {
            return 'Untitled Item';
        }
        
        // Try to extract first few words or a meaningful title
        $words = explode(' ', $description);
        if (count($words) > 5) {
            return implode(' ', array_slice($words, 0, 5)) . '...';
        }
        
        return $description;
    }
    
    /**
     * Extract location from description
     */
    private function extractLocation($description)
    {
        if (empty($description)) {
            return 'Location not specified';
        }
        
        // Common location keywords
        $locationKeywords = [
            'SM', 'Mall', 'University', 'UM', 'Market', 'Office', 'Room', 
            'Library', 'Gym', 'Park', 'Street', 'Avenue', 'City', 'Davao',
            'Manila', 'Cebu', 'Quezon', 'Gaisano', 'Roxas'
        ];
        
        foreach ($locationKeywords as $keyword) {
            if (stripos($description, $keyword) !== false) {
                // Try to extract a phrase around the keyword
                $words = explode(' ', $description);
                $keywordIndex = -1;
                foreach ($words as $index => $word) {
                    if (stripos($word, $keyword) !== false) {
                        $keywordIndex = $index;
                        break;
                    }
                }
                
                if ($keywordIndex >= 0) {
                    // Get a few words around the keyword
                    $start = max(0, $keywordIndex - 1);
                    $end = min(count($words), $keywordIndex + 3);
                    $location = implode(' ', array_slice($words, $start, $end - $start));
                    return $location;
                }
            }
        }
        
        // If no location found, return first part of description
        $words = explode(' ', $description);
        return implode(' ', array_slice($words, 0, min(3, count($words))));
    }
    
    /**
     * Get display name from full name
     */
    private function getDisplayName($name)
    {
        $parts = explode(' ', $name);
        if (count($parts) >= 2) {
            return $parts[0] . ' ' . substr($parts[1], 0, 1) . '.';
        }
        return $name;
    }
    
    /**
     * Extract city from email (fallback if location not available)
     */
    private function extractCityFromEmail($email)
    {
        // Try to extract city from email domain or return a default
        if (str_contains($email, 'davao')) {
            return 'Davao City';
        } elseif (str_contains($email, 'manila')) {
            return 'Manila';
        } elseif (str_contains($email, 'cebu')) {
            return 'Cebu City';
        }
        return 'Unknown';
    }
}
