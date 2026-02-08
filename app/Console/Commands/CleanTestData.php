<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ImageMetadata;
use App\Models\Message;
use App\Models\Reward;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CleanTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:test-data {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all test data from the database and storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will delete all test data. Are you sure?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Cleaning test data...');

        // Delete test users (excluding admin)
        $testEmails = ['test@example.com', 'user@finditfast.com', 'john@example.com'];
        $deletedUsers = 0;
        
        foreach ($testEmails as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                // Delete user's items and files
                $items = ImageMetadata::where('uploader_email', $email)->get();
                foreach ($items as $item) {
                    // Delete image files
                    if ($item->file_path) {
                        $filePath = $item->file_path;
                        if (str_starts_with($filePath, '/storage/')) {
                            $filePath = substr($filePath, 9);
                        }
                        if (Storage::disk('public')->exists($filePath)) {
                            Storage::disk('public')->delete($filePath);
                        }
                    }
                }
                
                // Delete user's messages
                Message::where('sender_id', $user->id)->orWhere('receiver_id', $user->id)->delete();
                
                // Delete user's rewards
                Reward::where('user_id', $user->id)->delete();
                
                // Delete user's items
                ImageMetadata::where('uploader_email', $email)->delete();
                
                // Delete user
                $user->delete();
                $deletedUsers++;
                $this->info("Deleted test user: {$email}");
            }
        }

        // Delete items claimed by test users
        $testUserEmails = User::whereIn('email', $testEmails)->pluck('email')->toArray();
        if (!empty($testUserEmails)) {
            ImageMetadata::whereIn('claimed_by_email', $testUserEmails)->delete();
        }

        // Clean up orphaned files in temp-guest
        $tempFiles = Storage::disk('public')->files('temp-guest');
        foreach ($tempFiles as $file) {
            Storage::disk('public')->delete($file);
        }
        $this->info('Cleaned temp-guest files.');

        // Clean up orphaned files in user-items (files without database records)
        $userItemFiles = Storage::disk('public')->files('user-items');
        $existingFiles = ImageMetadata::pluck('file_path')->map(function($path) {
            if (str_starts_with($path, '/storage/')) {
                return substr($path, 9);
            }
            return $path;
        })->toArray();

        $orphanedCount = 0;
        foreach ($userItemFiles as $file) {
            if (!in_array($file, $existingFiles)) {
                Storage::disk('public')->delete($file);
                $orphanedCount++;
            }
        }
        if ($orphanedCount > 0) {
            $this->info("Deleted {$orphanedCount} orphaned files.");
        }

        $this->info("Test data cleanup completed!");
        $this->info("Deleted {$deletedUsers} test user(s).");
        
        return 0;
    }
}
