<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set execution time limit to prevent timeout errors
        @set_time_limit(300); // 5 minutes
        @ini_set('max_execution_time', '300');
        @ini_set('default_socket_timeout', '60');
        
        // Defer mail configuration to avoid timeout during bootstrap
        // Mail configuration will be applied lazily when needed
        // This prevents database queries during service provider boot
    }
    
    /**
     * Apply mail configuration from database settings
     * This method is kept for backward compatibility but is no longer called in boot()
     */
    private function applyMailConfiguration(): void
    {
        try {
            // Check if settings table exists
            if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return;
            }
            
            // Batch load all mail settings in a single query to avoid multiple database calls
            $mailSettings = \App\Models\Setting::whereIn('key', [
                'mail_mailer',
                'mail_host',
                'mail_port',
                'mail_username',
                'mail_password',
                'mail_encryption',
                'mail_from_address',
                'mail_from_name'
            ])->pluck('value', 'key')->toArray();
            
            // Get mail settings from database with defaults
            $mailMailer = $mailSettings['mail_mailer'] ?? null;
            $mailHost = $mailSettings['mail_host'] ?? null;
            $mailPort = $mailSettings['mail_port'] ?? null;
            $mailUsername = $mailSettings['mail_username'] ?? null;
            $mailPassword = $mailSettings['mail_password'] ?? null;
            $mailEncryption = $mailSettings['mail_encryption'] ?? null;
            $mailFromAddress = $mailSettings['mail_from_address'] ?? null;
            $mailFromName = $mailSettings['mail_from_name'] ?? null;
            
            // Only update if database has values
            if ($mailMailer) {
                Config::set('mail.default', $mailMailer);
            }
            
            if ($mailHost) {
                Config::set('mail.mailers.smtp.host', $mailHost);
            }
            
            if ($mailPort) {
                Config::set('mail.mailers.smtp.port', $mailPort);
            }
            
            if ($mailUsername) {
                Config::set('mail.mailers.smtp.username', $mailUsername);
            }
            
            if ($mailPassword) {
                Config::set('mail.mailers.smtp.password', $mailPassword);
            }
            
            if ($mailEncryption) {
                Config::set('mail.mailers.smtp.encryption', $mailEncryption);
            }
            
            if ($mailFromAddress) {
                Config::set('mail.from.address', $mailFromAddress);
            }
            
            if ($mailFromName) {
                Config::set('mail.from.name', $mailFromName);
            }
        } catch (\Exception $e) {
            // Silently fail - settings might not be configured yet
        }
    }
}
