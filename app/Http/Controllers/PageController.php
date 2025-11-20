<?php

namespace App\Http\Controllers;

use App\Models\Setting;

class PageController extends Controller
{
    private function sharedFooterData(): array
    {
        return [
            'socialLinks' => [
                'facebook' => Setting::get('social_facebook', ''),
                'instagram' => Setting::get('social_instagram', ''),
                'twitter' => Setting::get('social_twitter', ''),
                'linkedin' => Setting::get('social_linkedin', ''),
                'youtube' => Setting::get('social_youtube', ''),
                'tiktok' => Setting::get('social_tiktok', ''),
            ],
            'contactEmail' => Setting::get('contact_email', 'support@finditfast.com'),
            'contactWebsite' => Setting::get('contact_website', 'finditfast.com'),
        ];
    }

    public function privacy()
    {
        return view('privacy-policy', $this->sharedFooterData());
    }

    public function terms()
    {
        return view('terms-and-conditions', $this->sharedFooterData());
    }
}

