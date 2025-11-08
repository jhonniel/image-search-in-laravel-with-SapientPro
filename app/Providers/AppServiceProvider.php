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
        // Apply mail configuration from database settings
        try {
            $this->applyMailConfiguration();
        } catch (\Exception $e) {
            // Silently fail if settings table doesn't exist yet
        }
    }
    
    /**
     * Apply mail configuration from database settings
     */
    private function applyMailConfiguration(): void
    {
        try {
            // Check if settings table exists
            if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return;
            }
            
            // Get mail settings from database
            $mailMailer = Setting::get('mail_mailer');
            $mailHost = Setting::get('mail_host');
            $mailPort = Setting::get('mail_port');
            $mailUsername = Setting::get('mail_username');
            $mailPassword = Setting::get('mail_password');
            $mailEncryption = Setting::get('mail_encryption');
            $mailFromAddress = Setting::get('mail_from_address');
            $mailFromName = Setting::get('mail_from_name');
            
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
