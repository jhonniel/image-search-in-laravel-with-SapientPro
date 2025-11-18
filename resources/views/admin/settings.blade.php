@extends('layouts.admin')

@section('title', 'Settings - FindITFast Admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Settings</h1>
        <p class="text-gray-600">Manage system configuration and preferences</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}">
        @csrf
        
        <!-- Settings Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button type="button" onclick="showTab('general')" id="tab-general" class="tab-button active py-4 px-1 border-b-2 border-purple-primary font-medium text-sm text-purple-primary">
                    <i class="fas fa-cog mr-2"></i>General
                </button>
                <button type="button" onclick="showTab('email')" id="tab-email" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-envelope mr-2"></i>Email
                </button>
                <button type="button" onclick="showTab('locations')" id="tab-locations" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-map-marker-alt mr-2"></i>Locations
                </button>
            </nav>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Settings -->
            <div class="lg:col-span-2 space-y-6">
                <!-- General Tab Content -->
                <div id="content-general" class="tab-content">
                    <!-- General Settings -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <div class="px-6 py-5 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-primary rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-cog text-white"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">General Settings</h2>
                                    <p class="text-sm text-gray-600">Basic system configuration</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Site Name
                            </label>
                            <input type="text" name="site_name" value="FindITFast" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Site Description
                            </label>
                            <textarea name="site_description" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">A platform for finding lost and found items</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Admin Email
                            </label>
                            <input type="email" name="admin_email" value="admin@finditfast.com" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Maintenance Mode
                                </label>
                                <p class="text-xs text-gray-500">Enable to temporarily disable public access</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="maintenance_mode" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-primary"></div>
                            </label>
                        </div>
                        </div>
                    </div>

                    <!-- Social Media Settings -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mt-8">
                        <div class="px-6 py-5 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fab fa-facebook-f text-white"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Social Media Links</h2>
                                    <p class="text-sm text-gray-600">Manage social media links displayed in the footer</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-facebook-f text-blue-600 mr-2"></i>Facebook URL
                                </label>
                                <input type="url" name="social_facebook" 
                                       value="{{ \App\Models\Setting::get('social_facebook', '') }}"
                                       placeholder="https://facebook.com/yourpage"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Leave empty to hide this social media icon</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-instagram text-pink-600 mr-2"></i>Instagram URL
                                </label>
                                <input type="url" name="social_instagram" 
                                       value="{{ \App\Models\Setting::get('social_instagram', '') }}"
                                       placeholder="https://instagram.com/yourpage"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Leave empty to hide this social media icon</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-twitter text-blue-400 mr-2"></i>Twitter/X URL
                                </label>
                                <input type="url" name="social_twitter" 
                                       value="{{ \App\Models\Setting::get('social_twitter', '') }}"
                                       placeholder="https://twitter.com/yourpage"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Leave empty to hide this social media icon</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-linkedin text-blue-700 mr-2"></i>LinkedIn URL
                                </label>
                                <input type="url" name="social_linkedin" 
                                       value="{{ \App\Models\Setting::get('social_linkedin', '') }}"
                                       placeholder="https://linkedin.com/company/yourpage"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Leave empty to hide this social media icon</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-youtube text-red-600 mr-2"></i>YouTube URL
                                </label>
                                <input type="url" name="social_youtube" 
                                       value="{{ \App\Models\Setting::get('social_youtube', '') }}"
                                       placeholder="https://youtube.com/@yourchannel"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Leave empty to hide this social media icon</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-tiktok text-gray-900 mr-2"></i>TikTok URL
                                </label>
                                <input type="url" name="social_tiktok" 
                                       value="{{ \App\Models\Setting::get('social_tiktok', '') }}"
                                       placeholder="https://tiktok.com/@yourpage"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Leave empty to hide this social media icon</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Email Tab Content -->
                <div id="content-email" class="tab-content hidden space-y-6">
                    <!-- Email Configuration -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <div class="px-6 py-5 bg-gradient-to-r from-blue-50 to-cyan-50 border-b border-gray-200">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-envelope text-white"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Email Configuration</h2>
                                    <p class="text-sm text-gray-600">Configure SMTP and email sending settings</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Mail Driver <span class="text-red-500">*</span>
                            </label>
                            <select name="mail_mailer" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                <option value="smtp" {{ $emailSettings['mail_mailer'] === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                <option value="log" {{ $emailSettings['mail_mailer'] === 'log' ? 'selected' : '' }}>Log (for testing)</option>
                                <option value="sendmail" {{ $emailSettings['mail_mailer'] === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                <option value="mailgun" {{ $emailSettings['mail_mailer'] === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                <option value="ses" {{ $emailSettings['mail_mailer'] === 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                <option value="postmark" {{ $emailSettings['mail_mailer'] === 'postmark' ? 'selected' : '' }}>Postmark</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Choose how emails are sent. Use 'log' for testing.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    SMTP Host
                                </label>
                                <input type="text" name="mail_host" value="{{ $emailSettings['mail_host'] }}" 
                                       placeholder="smtp.gmail.com"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">e.g., smtp.gmail.com, smtp-mail.outlook.com</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    SMTP Port
                                </label>
                                <input type="number" name="mail_port" value="{{ $emailSettings['mail_port'] ?? 587 }}" min="1" max="65535"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Common: 587 (TLS), 465 (SSL)</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                SMTP Username
                            </label>
                            <input type="text" name="mail_username" value="{{ $emailSettings['mail_username'] }}" 
                                   placeholder="your-email@gmail.com"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                SMTP Password
                            </label>
                            <input type="password" name="mail_password" value="{{ $emailSettings['mail_password'] }}" 
                                   placeholder="Your app password"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                            <div class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-xs font-semibold text-blue-900 mb-1">
                                    <i class="fas fa-info-circle mr-1"></i>For Gmail Users:
                                </p>
                                <ol class="text-xs text-blue-800 list-decimal list-inside space-y-1">
                                    <li>Enable 2-Factor Authentication on your Google account</li>
                                    <li>Go to <a href="https://myaccount.google.com/apppasswords" target="_blank" class="underline font-semibold">Google App Passwords</a></li>
                                    <li>Generate a new App Password for "Mail"</li>
                                    <li>Use the 16-character App Password (not your regular password)</li>
                                </ol>
                                <p class="text-xs text-red-600 mt-2 font-semibold">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Important: Regular Gmail passwords will NOT work!
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Encryption
                            </label>
                            <select name="mail_encryption"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                <option value="tls" {{ ($emailSettings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ ($emailSettings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="null" {{ ($emailSettings['mail_encryption'] ?? '') === 'null' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    From Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="mail_from_address" value="{{ $emailSettings['mail_from_address'] }}" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    From Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="mail_from_name" value="{{ $emailSettings['mail_from_name'] }}" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                            </div>
                        </div>
                        
                        <!-- Test Email Section -->
                        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-900 mb-3">Test Email Configuration</h3>
                            <p class="text-xs text-gray-600 mb-4">Send a test email to verify your email settings are working correctly.</p>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <input type="email" id="testEmailInput" 
                                       value="devjry@gmail.com"
                                       placeholder="devjry@gmail.com"
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                <button type="button" id="testEmailBtn" 
                                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium whitespace-nowrap">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    <span id="testEmailBtnText">Send Test Email</span>
                                </button>
                            </div>
                            <div id="testEmailResult" class="mt-3 hidden"></div>
                        </div>
                        </div>
                    </div>

                    <!-- Notification Settings -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <div class="px-6 py-5 bg-gradient-to-r from-blue-50 to-cyan-50 border-b border-gray-200">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-bell text-white"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Notification Settings</h2>
                                    <p class="text-sm text-gray-600">Configure email and system notifications</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Email Notifications
                                    </label>
                                    <p class="text-xs text-gray-500">Send email notifications for new items</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="email_notifications" value="1" {{ $emailSettings['email_notifications'] ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-primary"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Similarity Alerts
                                    </label>
                                    <p class="text-xs text-gray-500">Notify users when similar items are found</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="similarity_alerts" value="1" {{ $emailSettings['similarity_alerts'] ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-primary"></div>
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Notification Email
                                </label>
                                <input type="email" name="notification_email" value="{{ $emailSettings['notification_email'] }}" 
                                       placeholder="notifications@finditfast.com"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Email address to receive system notifications</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Locations Tab Content -->
                <div id="content-locations" class="tab-content hidden space-y-6">
                    <!-- Field Visibility & Requirements Settings -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <div class="px-6 py-5 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-toggle-on text-white"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Field Visibility & Requirements</h2>
                                    <p class="text-sm text-gray-600">Control whether city and province fields appear in forms and if they are required</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Province Field Settings -->
                            <div class="border border-gray-200 rounded-lg p-5 bg-gray-50">
                                <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-map mr-2 text-indigo-600"></i>Province Field
                                </h3>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Show Province Field in Forms
                                            </label>
                                            <p class="text-xs text-gray-500">Enable to display province field when users post new items</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="enable_province_field" value="1" 
                                                   {{ \App\Models\Setting::get('enable_province_field', true) ? 'checked' : '' }}
                                                   class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                        </label>
                                    </div>
                                    <div class="flex items-center justify-between" id="province-required-container">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Make Province Required
                                            </label>
                                            <p class="text-xs text-gray-500">Require users to fill in province when posting items</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="province_field_required" value="1" 
                                                   {{ \App\Models\Setting::get('province_field_required', true) ? 'checked' : '' }}
                                                   class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- City Field Settings -->
                            <div class="border border-gray-200 rounded-lg p-5 bg-gray-50">
                                <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-city mr-2 text-purple-600"></i>City Field
                                </h3>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Show City Field in Forms
                                            </label>
                                            <p class="text-xs text-gray-500">Enable to display city field when users post new items</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="enable_city_field" value="1" 
                                                   {{ \App\Models\Setting::get('enable_city_field', true) ? 'checked' : '' }}
                                                   class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                        </label>
                                    </div>
                                    <div class="flex items-center justify-between" id="city-required-container">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Make City Required
                                            </label>
                                            <p class="text-xs text-gray-500">Require users to fill in city when posting items</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="city_field_required" value="1" 
                                                   {{ \App\Models\Setting::get('city_field_required', true) ? 'checked' : '' }}
                                                   class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- City Management -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <div class="px-6 py-5 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-primary rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-city text-white"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Available Cities</h2>
                                    <p class="text-sm text-gray-600">Select which cities in the Philippines are available for users</p>
                                </div>
                            </div>
                        </div>
                    <div class="p-6 space-y-6">
                        <!-- Add New City -->
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <h3 class="text-sm font-semibold text-gray-900 mb-3">
                                <i class="fas fa-plus-circle text-purple-600 mr-2"></i>Add New City
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">City Name</label>
                                    <input type="text" name="new_city_name" id="newCityName" 
                                           placeholder="Enter city name"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Region</label>
                                    <select name="new_city_region" id="newCityRegion" 
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                        <option value="">Select Region</option>
                                        @foreach($allRegions as $region)
                                        <option value="{{ $region }}">{{ $region }}</option>
                                        @endforeach
                                        <option value="__new_region__">+ Add New Region</option>
                                    </select>
                                    <input type="text" name="new_region_name" id="newRegionName" 
                                           placeholder="Enter new region name"
                                           class="hidden w-full mt-2 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                </div>
                                <div class="flex items-end">
                                    <button type="button" onclick="addNewCity()" 
                                            class="w-full px-4 py-2 bg-purple-primary text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
                                        <i class="fas fa-plus mr-1"></i>Add City
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Search and Select All -->
                        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                            <div class="flex-1 w-full sm:max-w-md">
                                <input type="text" id="citySearch" placeholder="Search cities..." 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="selectAllCities()" 
                                        class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-colors text-sm font-medium">
                                    <i class="fas fa-check-square mr-1"></i>Select All
                                </button>
                                <button type="button" onclick="deselectAllCities()" 
                                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                                    <i class="fas fa-square mr-1"></i>Deselect All
                                </button>
                            </div>
                        </div>

                        <!-- Cities by Region -->
                        <div id="citiesContainer" class="space-y-6 max-h-[600px] overflow-y-auto pr-2">
                            @foreach($philippineCities as $region => $cities)
                            <div class="city-region border border-gray-200 rounded-lg p-4" data-region="{{ strtolower($region) }}">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-base font-semibold text-gray-900">{{ $region }}</h3>
                                    <div class="flex gap-2">
                                        <button type="button" onclick="selectRegionCities('{{ $region }}')" 
                                                class="text-xs px-2 py-1 bg-purple-50 text-purple-600 rounded hover:bg-purple-100 transition-colors">
                                            Select All
                                        </button>
                                        <button type="button" onclick="deselectRegionCities('{{ $region }}')" 
                                                class="text-xs px-2 py-1 bg-gray-50 text-gray-600 rounded hover:bg-gray-100 transition-colors">
                                            Deselect
                                        </button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                    @foreach($cities as $city)
                                    @php
                                        $isCustomCity = isset($customCities[$region]) && in_array($city, $customCities[$region] ?? []);
                                    @endphp
                                    <div class="flex items-center p-2 rounded hover:bg-gray-50 city-item {{ $isCustomCity ? 'bg-yellow-50 border border-yellow-200' : '' }}" 
                                           data-city="{{ strtolower($city) }}" data-region="{{ strtolower($region) }}">
                                        <label class="flex items-center cursor-pointer flex-1">
                                            <input type="checkbox" name="enabled_cities[]" value="{{ $city }}" 
                                                   {{ in_array($city, $enabledCities ?? []) ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-purple-primary focus:ring-purple-primary city-checkbox">
                                            <span class="ml-2 text-sm text-gray-700 city-name flex-1">{{ $city }}</span>
                                        </label>
                                        <div class="flex gap-1 ml-2">
                                            <button type="button" onclick="editCity('{{ $city }}', '{{ $region }}', {{ $isCustomCity ? 'true' : 'false' }})" 
                                                    class="text-blue-500 hover:text-blue-700 text-xs" 
                                                    title="Edit city">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" onclick="deleteCity('{{ $city }}', '{{ $region }}', {{ $isCustomCity ? 'true' : 'false' }})" 
                                                    class="text-red-500 hover:text-red-700 text-xs" 
                                                    title="Delete city">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                <span id="selectedCount">{{ count($enabledCities ?? []) }}</span> city/cities selected
                            </p>
                        </div>
                    </div>
                </div>

                    <!-- Available Provinces -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <div class="px-6 py-5 bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-map text-white"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Available Provinces</h2>
                                    <p class="text-sm text-gray-600">Select which provinces in the Philippines are available for users</p>
                                </div>
                            </div>
                        </div>
                    <div class="p-6 space-y-6">
                        <!-- Add New Province -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900 mb-3">Add New Province</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Province Name</label>
                                    <input type="text" name="new_province_name" id="newProvinceName" 
                                           placeholder="Enter province name"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Region</label>
                                    <select name="new_province_region" id="newProvinceRegion" 
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                        <option value="">Select Region</option>
                                        @foreach($allProvinceRegions as $region)
                                        <option value="{{ $region }}">{{ $region }}</option>
                                        @endforeach
                                        <option value="__new_region__">+ Add New Region</option>
                                    </select>
                                    <input type="text" name="new_province_region_name" id="newProvinceRegionName" 
                                           placeholder="Enter new region name"
                                           class="hidden w-full mt-2 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                </div>
                                <div class="flex items-end">
                                    <button type="button" onclick="addNewProvince()" 
                                            class="w-full px-4 py-2 bg-purple-primary text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
                                        <i class="fas fa-plus mr-1"></i>Add Province
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Search and Select All -->
                        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                            <div class="flex-1 w-full sm:max-w-md">
                                <input type="text" id="provinceSearch" placeholder="Search provinces..." 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="selectAllProvinces()" 
                                        class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-colors text-sm font-medium">
                                    <i class="fas fa-check-square mr-1"></i>Select All
                                </button>
                                <button type="button" onclick="deselectAllProvinces()" 
                                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                                    <i class="fas fa-square mr-1"></i>Deselect All
                                </button>
                            </div>
                        </div>

                        <!-- Provinces by Region -->
                        <div id="provincesContainer" class="space-y-6 max-h-[600px] overflow-y-auto pr-2">
                            @foreach($philippineProvinces as $region => $provinces)
                            @if(count($provinces) > 0)
                            <div class="province-region border border-gray-200 rounded-lg p-4" data-region="{{ strtolower($region) }}">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-base font-semibold text-gray-900">{{ $region }}</h3>
                                    <div class="flex gap-2">
                                        <button type="button" onclick="selectRegionProvinces('{{ $region }}')" 
                                                class="text-xs px-2 py-1 bg-purple-50 text-purple-600 rounded hover:bg-purple-100 transition-colors">
                                            Select All
                                        </button>
                                        <button type="button" onclick="deselectRegionProvinces('{{ $region }}')" 
                                                class="text-xs px-2 py-1 bg-gray-50 text-gray-600 rounded hover:bg-gray-100 transition-colors">
                                            Deselect
                                        </button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                    @foreach($provinces as $province)
                                    @php
                                        $isCustomProvince = isset($customProvinces[$region]) && in_array($province, $customProvinces[$region] ?? []);
                                    @endphp
                                    <div class="flex items-center p-2 rounded hover:bg-gray-50 province-item {{ $isCustomProvince ? 'bg-yellow-50 border border-yellow-200' : '' }}" 
                                           data-province="{{ strtolower($province) }}" data-region="{{ strtolower($region) }}">
                                        <label class="flex items-center cursor-pointer flex-1">
                                            <input type="checkbox" name="enabled_provinces[]" value="{{ $province }}" 
                                                   {{ in_array($province, $enabledProvinces ?? []) ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-purple-primary focus:ring-purple-primary province-checkbox">
                                            <span class="ml-2 text-sm text-gray-700 province-name flex-1">{{ $province }}</span>
                                        </label>
                                        <div class="flex gap-1 ml-2">
                                            <button type="button" onclick="editProvince('{{ $province }}', '{{ $region }}', {{ $isCustomProvince ? 'true' : 'false' }})" 
                                                    class="text-blue-500 hover:text-blue-700 text-xs" 
                                                    title="Edit province">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" onclick="deleteProvince('{{ $province }}', '{{ $region }}', {{ $isCustomProvince ? 'true' : 'false' }})" 
                                                    class="text-red-500 hover:text-red-700 text-xs" 
                                                    title="Delete province">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                <span id="selectedProvincesCount">{{ count($enabledProvinces ?? []) }}</span> province/provinces selected
                            </p>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-purple-primary text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                        <i class="fas fa-save mr-2"></i>
                        Save Settings
                    </button>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <button type="button" id="exportDatabaseBtn" 
                                    class="w-full flex items-center justify-between px-4 py-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border border-blue-200">
                                <div class="flex items-center">
                                    <i class="fas fa-database text-blue-600 mr-3"></i>
                                    <span class="text-sm font-medium text-blue-700">Export Database (SQL)</span>
                                </div>
                                <i class="fas fa-download text-blue-600"></i>
                            </button>
                            <p class="text-xs text-gray-500 mt-2 ml-4">Download a backup of your database as an SQL file</p>
                        </div>
                        
                        <div>
                            <label for="importDatabaseFile" class="w-full flex items-center justify-between px-4 py-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors border border-green-200 cursor-pointer">
                                <div class="flex items-center">
                                    <i class="fas fa-upload text-green-600 mr-3"></i>
                                    <span class="text-sm font-medium text-green-700">Import Database (SQL)</span>
                                </div>
                                <i class="fas fa-file-upload text-green-600"></i>
                                <input type="file" id="importDatabaseFile" name="sql_file" accept=".sql,.txt" class="hidden" onchange="importDatabase(this)">
                            </label>
                            <p class="text-xs text-amber-600 mt-2 ml-4">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <strong>Warning:</strong> This will replace all current database data
                            </p>
                        </div>
                        
                        <button type="button" class="w-full flex items-center px-4 py-3 bg-red-50 hover:bg-red-100 rounded-lg transition-colors" onclick="resetToDefaults()">
                            <i class="fas fa-undo text-red-600 mr-3"></i>
                            <span class="text-sm font-medium text-red-700">Reset to Defaults</span>
                        </button>
                        
                        <div id="importResult" class="hidden mt-3 p-3 rounded-lg"></div>
                    </div>
                </div>

                <!-- System Info -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">System Information</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">PHP Version</p>
                            <p class="text-sm font-medium text-gray-900">{{ PHP_VERSION }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Laravel Version</p>
                            <p class="text-sm font-medium text-gray-900">{{ app()->version() }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Environment</p>
                            <p class="text-sm font-medium text-gray-900">{{ app()->environment() }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Last Updated</p>
                            <p class="text-sm font-medium text-gray-900">{{ now()->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Help & Support -->
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg shadow-sm border border-purple-200 p-6">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-purple-primary rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-question-circle text-white"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Need Help?</h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">If you need assistance with settings, check our documentation or contact support.</p>
                    <div class="space-y-2">
                        <a href="#" class="block text-sm text-purple-primary hover:text-purple-700 font-medium">
                            <i class="fas fa-book mr-2"></i>
                            Documentation
                        </a>
                        <a href="#" class="block text-sm text-purple-primary hover:text-purple-700 font-medium">
                            <i class="fas fa-envelope mr-2"></i>
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Tab switching functionality
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-purple-primary', 'text-purple-primary');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    const selectedContent = document.getElementById('content-' + tabName);
    if (selectedContent) {
        selectedContent.classList.remove('hidden');
    }
    
    // Add active class to selected tab
    const selectedTab = document.getElementById('tab-' + tabName);
    if (selectedTab) {
        selectedTab.classList.add('active', 'border-purple-primary', 'text-purple-primary');
        selectedTab.classList.remove('border-transparent', 'text-gray-500');
    }
}

// Field visibility toggle functionality
function toggleRequiredFieldVisibility(fieldType) {
    const enableCheckbox = document.querySelector(`input[name="enable_${fieldType}_field"]`);
    const requiredContainer = document.getElementById(`${fieldType}-required-container`);
    
    if (enableCheckbox && requiredContainer) {
        const isEnabled = enableCheckbox.checked;
        if (isEnabled) {
            requiredContainer.style.opacity = '1';
            requiredContainer.style.pointerEvents = 'auto';
        } else {
            requiredContainer.style.opacity = '0.5';
            requiredContainer.style.pointerEvents = 'none';
            // Uncheck required if field is disabled
            const requiredCheckbox = document.querySelector(`input[name="${fieldType}_field_required"]`);
            if (requiredCheckbox) {
                requiredCheckbox.checked = false;
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize field visibility toggles
    const enableProvinceField = document.querySelector('input[name="enable_province_field"]');
    const enableCityField = document.querySelector('input[name="enable_city_field"]');
    
    if (enableProvinceField) {
        toggleRequiredFieldVisibility('province');
        enableProvinceField.addEventListener('change', function() {
            toggleRequiredFieldVisibility('province');
        });
    }
    
    if (enableCityField) {
        toggleRequiredFieldVisibility('city');
        enableCityField.addEventListener('change', function() {
            toggleRequiredFieldVisibility('city');
        });
    }
    
    const testEmailBtn = document.getElementById('testEmailBtn');
    const testEmailInput = document.getElementById('testEmailInput');
    const testEmailResult = document.getElementById('testEmailResult');
    const testEmailBtnText = document.getElementById('testEmailBtnText');
    
    testEmailBtn.addEventListener('click', function() {
        const email = testEmailInput.value.trim();
        
        if (!email) {
            showResult('Please enter an email address', 'error');
            return;
        }
        
        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showResult('Please enter a valid email address', 'error');
            return;
        }
        
        // Disable button and show loading
        testEmailBtn.disabled = true;
        testEmailBtnText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
        testEmailResult.classList.add('hidden');
        
        // Send AJAX request
        fetch('{{ route("settings.test-email") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                test_email: email
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showResult(data.message, 'success');
            } else {
                showResult(data.message, 'error');
            }
        })
        .catch(error => {
            showResult('An error occurred while sending the test email. Please try again.', 'error');
            console.error('Error:', error);
        })
        .finally(() => {
            // Re-enable button
            testEmailBtn.disabled = false;
            testEmailBtnText.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Send Test Email';
        });
    });
    
    function showResult(message, type) {
        testEmailResult.classList.remove('hidden');
        testEmailResult.className = 'mt-3 p-3 rounded-lg ' + (type === 'success' 
            ? 'bg-green-50 border border-green-200 text-green-800' 
            : 'bg-red-50 border border-red-200 text-red-800');
        testEmailResult.innerHTML = '<div class="flex items-center"><i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + ' mr-2"></i><span>' + message + '</span></div>';
        
        // Scroll to result
        testEmailResult.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});

// Import database function
function importDatabase(input) {
    const file = input.files[0];
    if (!file) {
        return;
    }
    
    // Validate file type
    const validExtensions = ['sql', 'txt'];
    const fileExtension = file.name.split('.').pop().toLowerCase();
    if (!validExtensions.includes(fileExtension)) {
        showImportResult('Please select a valid SQL file (.sql or .txt)', 'error');
        input.value = '';
        return;
    }
    
    // Confirm import
    if (!confirm('WARNING: Importing a database file will replace all current data. This action cannot be undone. Are you sure you want to continue?')) {
        input.value = '';
        return;
    }
    
    // Show loading
    const importResult = document.getElementById('importResult');
    importResult.classList.remove('hidden');
    importResult.className = 'mt-3 p-3 rounded-lg bg-blue-50 border border-blue-200 text-blue-800';
    importResult.innerHTML = '<div class="flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i><span>Importing database... Please wait.</span></div>';
    
    // Create form data
    const formData = new FormData();
    formData.append('sql_file', file);
    formData.append('_token', '{{ csrf_token() }}');
    
    // Send request
    fetch('{{ route("settings.import-database") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showImportResult(data.message || 'Database imported successfully!', 'success');
            // Optionally reload the page after a delay
            setTimeout(() => {
                if (confirm('Database imported successfully. Page will reload to reflect changes.')) {
                    window.location.reload();
                }
            }, 2000);
        } else {
            showImportResult(data.message || 'Failed to import database', 'error');
        }
    })
    .catch(error => {
        showImportResult('An error occurred while importing the database: ' + error.message, 'error');
        console.error('Error:', error);
    })
    .finally(() => {
        input.value = '';
    });
}

function showImportResult(message, type) {
    const importResult = document.getElementById('importResult');
    importResult.classList.remove('hidden');
    importResult.className = 'mt-3 p-3 rounded-lg ' + (type === 'success' 
        ? 'bg-green-50 border border-green-200 text-green-800' 
        : 'bg-red-50 border border-red-200 text-red-800');
    importResult.innerHTML = '<div class="flex items-center"><i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + ' mr-2"></i><span>' + message + '</span></div>';
    
    // Scroll to result
    importResult.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function resetToDefaults() {
    if (confirm('Are you sure you want to reset all settings to their default values? This action cannot be undone.')) {
        // TODO: Implement reset to defaults functionality
        alert('Reset to defaults functionality will be implemented soon.');
    }
}

// Export Database functionality
document.addEventListener('DOMContentLoaded', function() {
    const exportBtn = document.getElementById('exportDatabaseBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            exportDatabase();
        });
    }
    
    const modalClose = document.getElementById('exportDatabaseModalClose');
    if (modalClose) {
        modalClose.addEventListener('click', function() {
            document.getElementById('exportDatabaseModal').classList.add('hidden');
            if (exportBtn) exportBtn.disabled = false;
        });
    }
});

// City Management Functions
function addNewCity() {
    const cityName = document.getElementById('newCityName').value.trim();
    const regionSelect = document.getElementById('newCityRegion');
    const newRegionInput = document.getElementById('newRegionName');
    let region = regionSelect.value;
    
    if (!cityName) {
        alert('Please enter a city name');
        return;
    }
    
    // Handle new region
    if (region === '__new_region__') {
        region = newRegionInput.value.trim();
        if (!region) {
            alert('Please enter a region name or select an existing region');
            newRegionInput.focus();
            return;
        }
    }
    
    if (!region) {
        alert('Please select or enter a region');
        return;
    }
    
    // Check if city already exists in this region
    const existingCity = document.querySelector(`[data-city="${cityName.toLowerCase()}"][data-region="${region.toLowerCase()}"]`);
    if (existingCity) {
        alert('This city already exists in this region');
        return;
    }
    
    // Submit the form to add the city
    const form = document.querySelector('form');
    
    // Create hidden inputs for the new city
    const cityInput = document.createElement('input');
    cityInput.type = 'hidden';
    cityInput.name = 'new_city_name';
    cityInput.value = cityName;
    form.appendChild(cityInput);
    
    const regionInput = document.createElement('input');
    regionInput.type = 'hidden';
    regionInput.name = 'new_city_region';
    regionInput.value = region;
    form.appendChild(regionInput);
    
    // Submit form
    form.submit();
}

function editCity(cityName, region, isCustom) {
    // Show edit modal
    document.getElementById('editCityName').value = cityName;
    document.getElementById('editCityOriginalName').value = cityName;
    document.getElementById('editCityRegion').value = region;
    document.getElementById('editCityOriginalRegion').value = region;
    document.getElementById('editCityIsCustom').value = isCustom ? '1' : '0';
    
    // Show new region input if needed
    const newRegionInput = document.getElementById('editNewRegionName');
    if (region === '__new_region__') {
        newRegionInput.classList.remove('hidden');
    } else {
        newRegionInput.classList.add('hidden');
    }
    
    // Show modal
    document.getElementById('editCityModal').classList.remove('hidden');
}

function closeEditCityModal() {
    document.getElementById('editCityModal').classList.add('hidden');
    document.getElementById('editCityName').value = '';
    document.getElementById('editCityOriginalName').value = '';
    document.getElementById('editCityRegion').value = '';
    document.getElementById('editCityOriginalRegion').value = '';
    document.getElementById('editNewRegionName').value = '';
    document.getElementById('editNewRegionName').classList.add('hidden');
}

function saveCityEdit() {
    const originalName = document.getElementById('editCityOriginalName').value.trim();
    const originalRegion = document.getElementById('editCityOriginalRegion').value.trim();
    const newName = document.getElementById('editCityName').value.trim();
    const regionSelect = document.getElementById('editCityRegion');
    const newRegionInput = document.getElementById('editNewRegionName');
    let newRegion = regionSelect.value;
    const isCustom = document.getElementById('editCityIsCustom').value === '1';
    
    if (!newName) {
        alert('Please enter a city name');
        return;
    }
    
    // Handle new region
    if (newRegion === '__new_region__') {
        newRegion = newRegionInput.value.trim();
        if (!newRegion) {
            alert('Please enter a region name or select an existing region');
            newRegionInput.focus();
            return;
        }
    }
    
    if (!newRegion) {
        alert('Please select or enter a region');
        return;
    }
    
    // Check if the new name already exists in the target region (and it's not the same city)
    if (newName !== originalName || newRegion !== originalRegion) {
        const existingCity = document.querySelector(`[data-city="${newName.toLowerCase()}"][data-region="${newRegion.toLowerCase()}"]`);
        if (existingCity) {
            alert('A city with this name already exists in this region');
            return;
        }
    }
    
    // Submit the form to edit the city
    const form = document.querySelector('form');
    
    // Create hidden inputs for editing
    const originalNameInput = document.createElement('input');
    originalNameInput.type = 'hidden';
    originalNameInput.name = 'edit_city_original_name';
    originalNameInput.value = originalName;
    form.appendChild(originalNameInput);
    
    const originalRegionInput = document.createElement('input');
    originalRegionInput.type = 'hidden';
    originalRegionInput.name = 'edit_city_original_region';
    originalRegionInput.value = originalRegion;
    form.appendChild(originalRegionInput);
    
    const newNameInput = document.createElement('input');
    newNameInput.type = 'hidden';
    newNameInput.name = 'edit_city_new_name';
    newNameInput.value = newName;
    form.appendChild(newNameInput);
    
    const newRegionInputField = document.createElement('input');
    newRegionInputField.type = 'hidden';
    newRegionInputField.name = 'edit_city_new_region';
    newRegionInputField.value = newRegion;
    form.appendChild(newRegionInputField);
    
    const isCustomInput = document.createElement('input');
    isCustomInput.type = 'hidden';
    isCustomInput.name = 'edit_city_is_custom';
    isCustomInput.value = isCustom ? '1' : '0';
    form.appendChild(isCustomInput);
    
    // Submit form
    form.submit();
}

function deleteCity(cityName, region, isCustom) {
    if (!confirm(`Are you sure you want to delete "${cityName}" from ${region}?`)) {
        return;
    }
    
    // Submit the form to delete the city
    const form = document.querySelector('form');
    
    // Create hidden inputs for deletion
    const cityInput = document.createElement('input');
    cityInput.type = 'hidden';
    cityInput.name = 'delete_city';
    cityInput.value = cityName;
    form.appendChild(cityInput);
    
    const regionInput = document.createElement('input');
    regionInput.type = 'hidden';
    regionInput.name = 'delete_city_region';
    regionInput.value = region;
    form.appendChild(regionInput);
    
    const isCustomInput = document.createElement('input');
    isCustomInput.type = 'hidden';
    isCustomInput.name = 'delete_city_is_custom';
    isCustomInput.value = isCustom ? '1' : '0';
    form.appendChild(isCustomInput);
    
    // Submit form
    form.submit();
}

// Handle new region option
document.addEventListener('DOMContentLoaded', function() {
    const regionSelect = document.getElementById('newCityRegion');
    const newRegionInput = document.getElementById('newRegionName');
    
    if (regionSelect && newRegionInput) {
        regionSelect.addEventListener('change', function() {
            if (this.value === '__new_region__') {
                newRegionInput.classList.remove('hidden');
                newRegionInput.required = true;
            } else {
                newRegionInput.classList.add('hidden');
                newRegionInput.required = false;
                newRegionInput.value = '';
            }
        });
    }
    
    // Handle edit city region option
    const editRegionSelect = document.getElementById('editCityRegion');
    const editNewRegionInput = document.getElementById('editNewRegionName');
    
    if (editRegionSelect && editNewRegionInput) {
        editRegionSelect.addEventListener('change', function() {
            if (this.value === '__new_region__') {
                editNewRegionInput.classList.remove('hidden');
                editNewRegionInput.required = true;
            } else {
                editNewRegionInput.classList.add('hidden');
                editNewRegionInput.required = false;
                editNewRegionInput.value = '';
            }
        });
    }
});

function selectAllCities() {
    document.querySelectorAll('.city-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    updateSelectedCount();
}

function deselectAllCities() {
    document.querySelectorAll('.city-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    updateSelectedCount();
}

function selectRegionCities(region) {
    const regionDiv = document.querySelector(`[data-region="${region.toLowerCase()}"]`);
    if (regionDiv) {
        regionDiv.querySelectorAll('.city-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
        updateSelectedCount();
    }
}

function deselectRegionCities(region) {
    const regionDiv = document.querySelector(`[data-region="${region.toLowerCase()}"]`);
    if (regionDiv) {
        regionDiv.querySelectorAll('.city-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateSelectedCount();
    }
}

function updateSelectedCount() {
    const count = document.querySelectorAll('.city-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count;
}

// City search functionality
document.addEventListener('DOMContentLoaded', function() {
    const citySearch = document.getElementById('citySearch');
    if (citySearch) {
        citySearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cityItems = document.querySelectorAll('.city-item');
            const regions = document.querySelectorAll('.city-region');
            
            let hasVisibleItems = false;
            
            cityItems.forEach(item => {
                const cityName = item.dataset.city;
                const regionName = item.dataset.region;
                
                if (cityName.includes(searchTerm) || regionName.includes(searchTerm)) {
                    item.style.display = '';
                    hasVisibleItems = true;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Hide/show regions based on visible items
            regions.forEach(region => {
                const cityItems = region.querySelectorAll('.city-item');
                let hasVisible = false;
                
                cityItems.forEach(item => {
                    if (item.style.display !== 'none') {
                        hasVisible = true;
                    }
                });
                
                if (!hasVisible && searchTerm !== '') {
                    region.style.display = 'none';
                } else {
                    region.style.display = '';
                }
            });
        });
    }
    
    // Update count on checkbox change
    document.querySelectorAll('.city-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    // Initial count
    updateSelectedCount();
});

// Province functions
function addNewProvince() {
    const provinceName = document.getElementById('newProvinceName').value.trim();
    const regionSelect = document.getElementById('newProvinceRegion');
    let region = regionSelect.value;
    
    if (region === '__new_region__') {
        region = document.getElementById('newProvinceRegionName').value.trim();
    }
    
    if (!provinceName || !region) {
        alert('Please enter both province name and region');
        return;
    }
    
    // Submit the form
    document.querySelector('form').submit();
}

function editProvince(provinceName, region, isCustom) {
    // Show edit modal
    document.getElementById('editProvinceName').value = provinceName;
    document.getElementById('editProvinceOriginalName').value = provinceName;
    document.getElementById('editProvinceRegion').value = region;
    document.getElementById('editProvinceOriginalRegion').value = region;
    document.getElementById('editProvinceIsCustom').value = isCustom ? '1' : '0';
    
    // Show modal
    document.getElementById('editProvinceModal').classList.remove('hidden');
}

function closeEditProvinceModal() {
    document.getElementById('editProvinceModal').classList.add('hidden');
    document.getElementById('editProvinceName').value = '';
    document.getElementById('editProvinceOriginalName').value = '';
    document.getElementById('editProvinceRegion').value = '';
    document.getElementById('editProvinceOriginalRegion').value = '';
    document.getElementById('editProvinceNewRegionName').value = '';
    document.getElementById('editProvinceIsCustom').value = '0';
}

function saveProvinceEdit() {
    const form = document.querySelector('form');
    
    // Create hidden inputs for editing
    const originalNameInput = document.createElement('input');
    originalNameInput.type = 'hidden';
    originalNameInput.name = 'edit_province_original_name';
    originalNameInput.value = document.getElementById('editProvinceOriginalName').value;
    form.appendChild(originalNameInput);
    
    const newNameInput = document.createElement('input');
    newNameInput.type = 'hidden';
    newNameInput.name = 'edit_province_new_name';
    newNameInput.value = document.getElementById('editProvinceName').value;
    form.appendChild(newNameInput);
    
    const originalRegionInput = document.createElement('input');
    originalRegionInput.type = 'hidden';
    originalRegionInput.name = 'edit_province_original_region';
    originalRegionInput.value = document.getElementById('editProvinceOriginalRegion').value;
    form.appendChild(originalRegionInput);
    
    let newRegion = document.getElementById('editProvinceRegion').value;
    if (newRegion === '__new_region__') {
        newRegion = document.getElementById('editProvinceNewRegionName').value.trim();
    }
    
    const newRegionInput = document.createElement('input');
    newRegionInput.type = 'hidden';
    newRegionInput.name = 'edit_province_new_region';
    newRegionInput.value = newRegion;
    form.appendChild(newRegionInput);
    
    const isCustomInput = document.createElement('input');
    isCustomInput.type = 'hidden';
    isCustomInput.name = 'edit_province_is_custom';
    isCustomInput.value = document.getElementById('editProvinceIsCustom').value;
    form.appendChild(isCustomInput);
    
    // Submit form
    form.submit();
}

function deleteProvince(provinceName, region, isCustom) {
    if (!confirm(`Are you sure you want to delete "${provinceName}" from ${region}?`)) {
        return;
    }
    
    // Submit the form to delete the province
    const form = document.querySelector('form');
    
    // Create hidden inputs for deletion
    const provinceInput = document.createElement('input');
    provinceInput.type = 'hidden';
    provinceInput.name = 'delete_province';
    provinceInput.value = provinceName;
    form.appendChild(provinceInput);
    
    const regionInput = document.createElement('input');
    regionInput.type = 'hidden';
    regionInput.name = 'delete_province_region';
    regionInput.value = region;
    form.appendChild(regionInput);
    
    const isCustomInput = document.createElement('input');
    isCustomInput.type = 'hidden';
    isCustomInput.name = 'delete_province_is_custom';
    isCustomInput.value = isCustom ? '1' : '0';
    form.appendChild(isCustomInput);
    
    // Submit form
    form.submit();
}

function selectAllProvinces() {
    document.querySelectorAll('.province-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    updateSelectedProvincesCount();
}

function deselectAllProvinces() {
    document.querySelectorAll('.province-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    updateSelectedProvincesCount();
}

function selectRegionProvinces(region) {
    const regionDiv = document.querySelector(`.province-region[data-region="${region.toLowerCase()}"]`);
    if (regionDiv) {
        regionDiv.querySelectorAll('.province-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
        updateSelectedProvincesCount();
    }
}

function deselectRegionProvinces(region) {
    const regionDiv = document.querySelector(`.province-region[data-region="${region.toLowerCase()}"]`);
    if (regionDiv) {
        regionDiv.querySelectorAll('.province-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateSelectedProvincesCount();
    }
}

function updateSelectedProvincesCount() {
    const count = document.querySelectorAll('.province-checkbox:checked').length;
    const countElement = document.getElementById('selectedProvincesCount');
    if (countElement) {
        countElement.textContent = count;
    }
}

// Province search functionality
document.addEventListener('DOMContentLoaded', function() {
    const provinceSearch = document.getElementById('provinceSearch');
    if (provinceSearch) {
        provinceSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const provinceItems = document.querySelectorAll('.province-item');
            const regions = document.querySelectorAll('.province-region');
            
            let hasVisibleItems = false;
            
            regions.forEach(region => {
                const items = region.querySelectorAll('.province-item');
                let regionHasVisible = false;
                
                items.forEach(item => {
                    const provinceName = item.querySelector('.province-name').textContent.toLowerCase();
                    if (provinceName.includes(searchTerm)) {
                        item.style.display = '';
                        regionHasVisible = true;
                        hasVisibleItems = true;
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                region.style.display = regionHasVisible ? '' : 'none';
            });
        });
    }
    
    // Update count on checkbox change
    document.querySelectorAll('.province-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedProvincesCount);
    });
    
    // Initial count
    updateSelectedProvincesCount();
    
    // Handle new province region option
    const provinceRegionSelect = document.getElementById('newProvinceRegion');
    const newProvinceRegionInput = document.getElementById('newProvinceRegionName');
    
    if (provinceRegionSelect && newProvinceRegionInput) {
        provinceRegionSelect.addEventListener('change', function() {
            if (this.value === '__new_region__') {
                newProvinceRegionInput.classList.remove('hidden');
                newProvinceRegionInput.required = true;
            } else {
                newProvinceRegionInput.classList.add('hidden');
                newProvinceRegionInput.required = false;
                newProvinceRegionInput.value = '';
            }
        });
    }
    
    // Handle edit province region option
    const editProvinceRegionSelect = document.getElementById('editProvinceRegion');
    const editProvinceNewRegionInput = document.getElementById('editProvinceNewRegionName');
    
    if (editProvinceRegionSelect && editProvinceNewRegionInput) {
        editProvinceRegionSelect.addEventListener('change', function() {
            if (this.value === '__new_region__') {
                editProvinceNewRegionInput.classList.remove('hidden');
                editProvinceNewRegionInput.required = true;
            } else {
                editProvinceNewRegionInput.classList.add('hidden');
                editProvinceNewRegionInput.required = false;
                editProvinceNewRegionInput.value = '';
            }
        });
    }
});

function exportDatabase() {
    // Show modal
    const modal = document.getElementById('exportDatabaseModal');
    const modalMessage = document.getElementById('exportDatabaseModalMessage');
    const modalClose = document.getElementById('exportDatabaseModalClose');
    
    if (!modal || !modalMessage || !modalClose) {
        console.error('Export modal elements not found');
        return;
    }
    
    modal.classList.remove('hidden');
    modalMessage.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Exporting database... Please wait.';
    modalMessage.className = 'text-gray-700';
    modalClose.disabled = true;
    
    // Disable export button
    const exportBtn = document.getElementById('exportDatabaseBtn');
    if (exportBtn) exportBtn.disabled = true;
    
    // Fetch database export
    fetch('{{ route("settings.export-database") }}?json=1', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Decode base64 content
            const sqlContent = atob(data.content);
            
            // Create blob and download
            const blob = new Blob([sqlContent], { type: 'application/sql' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = data.filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            // Update modal message
            modalMessage.innerHTML = '<i class="fas fa-check-circle text-green-500 mr-2"></i>Database exported successfully! Download started.';
            modalMessage.className = 'text-green-700';
            modalClose.disabled = false;
            
            // Close modal after 2 seconds
            setTimeout(() => {
                modal.classList.add('hidden');
                if (exportBtn) exportBtn.disabled = false;
            }, 2000);
        } else {
            // Show error
            modalMessage.innerHTML = '<i class="fas fa-exclamation-circle text-red-500 mr-2"></i>' + (data.message || 'Failed to export database');
            modalMessage.className = 'text-red-700';
            modalClose.disabled = false;
            if (exportBtn) exportBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Export error:', error);
        modalMessage.innerHTML = '<i class="fas fa-exclamation-circle text-red-500 mr-2"></i>Failed to export database: ' + (error.message || 'Unknown error');
        modalMessage.className = 'text-red-700';
        modalClose.disabled = false;
        if (exportBtn) exportBtn.disabled = false;
    });
}
</script>

<!-- Edit City Modal -->
<div id="editCityModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="z-index: 9999;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit City</h3>
                <button onclick="closeEditCityModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City Name</label>
                    <input type="text" id="editCityName" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                    <input type="hidden" id="editCityOriginalName">
                    <input type="hidden" id="editCityOriginalRegion">
                    <input type="hidden" id="editCityIsCustom">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                    <select id="editCityRegion" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                        <option value="">Select Region</option>
                        @foreach($allRegions as $region)
                        <option value="{{ $region }}">{{ $region }}</option>
                        @endforeach
                        <option value="__new_region__">+ Add New Region</option>
                    </select>
                    <input type="text" id="editNewRegionName" 
                           placeholder="Enter new region name"
                           class="hidden w-full mt-2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button onclick="saveCityEdit()" 
                        class="flex-1 px-4 py-2 bg-purple-primary text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
                <button onclick="closeEditCityModal()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Province Modal -->
<div id="editProvinceModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="z-index: 9999;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit Province</h3>
                <button onclick="closeEditProvinceModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Province Name</label>
                    <input type="text" id="editProvinceName" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                    <input type="hidden" id="editProvinceOriginalName">
                    <input type="hidden" id="editProvinceOriginalRegion">
                    <input type="hidden" id="editProvinceIsCustom">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                    <select id="editProvinceRegion" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                        <option value="">Select Region</option>
                        @foreach($allProvinceRegions as $region)
                        <option value="{{ $region }}">{{ $region }}</option>
                        @endforeach
                        <option value="__new_region__">+ Add New Region</option>
                    </select>
                    <input type="text" id="editProvinceNewRegionName" 
                           placeholder="Enter new region name"
                           class="hidden w-full mt-2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button onclick="saveProvinceEdit()" 
                        class="flex-1 px-4 py-2 bg-purple-primary text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
                <button onclick="closeEditProvinceModal()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Export Database Modal -->
<div id="exportDatabaseModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="z-index: 9999;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <i class="fas fa-database text-blue-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-5">Exporting Database</h3>
            <div class="mt-2 px-7 py-3">
                <p id="exportDatabaseModalContent" class="text-sm text-gray-500">
                    <span id="exportDatabaseModalMessage">Preparing database export...</span>
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="exportDatabaseModalClose" 
                        class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

