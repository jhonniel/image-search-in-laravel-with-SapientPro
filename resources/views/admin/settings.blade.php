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

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Settings -->
            <div class="lg:col-span-2 space-y-6">
                <!-- General Settings -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">General Settings</h2>
                        <p class="text-sm text-gray-600 mt-1">Basic system configuration</p>
                    </div>
                    <div class="p-6 space-y-6">
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

                <!-- Email Configuration -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Email Configuration</h2>
                        <p class="text-sm text-gray-600 mt-1">Configure SMTP settings for email notifications</p>
                    </div>
                    <div class="p-6 space-y-6">
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
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Notification Settings</h2>
                        <p class="text-sm text-gray-600 mt-1">Configure email and system notifications</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="flex items-center justify-between">
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

                        <div class="flex items-center justify-between">
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

                <!-- Image Processing Settings -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Image Processing</h2>
                        <p class="text-sm text-gray-600 mt-1">Configure image upload and processing</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Max File Size (MB)
                            </label>
                            <input type="number" name="max_file_size" value="10" min="1" max="100"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Allowed Image Types
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="allowed_types[]" value="jpg" checked class="rounded border-gray-300 text-purple-primary focus:ring-purple-primary">
                                    <span class="ml-2 text-sm text-gray-700">JPG</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="allowed_types[]" value="png" checked class="rounded border-gray-300 text-purple-primary focus:ring-purple-primary">
                                    <span class="ml-2 text-sm text-gray-700">PNG</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="allowed_types[]" value="gif" checked class="rounded border-gray-300 text-purple-primary focus:ring-purple-primary">
                                    <span class="ml-2 text-sm text-gray-700">GIF</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="allowed_types[]" value="webp" class="rounded border-gray-300 text-purple-primary focus:ring-purple-primary">
                                    <span class="ml-2 text-sm text-gray-700">WEBP</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Similarity Threshold (%)
                            </label>
                            <input type="number" name="similarity_threshold" value="80" min="0" max="100"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Minimum similarity percentage to trigger alerts</p>
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Security Settings</h2>
                        <p class="text-sm text-gray-600 mt-1">Manage security and access controls</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Session Timeout (minutes)
                            </label>
                            <input type="number" name="session_timeout" value="120" min="15" max="1440"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Require Email Verification
                                </label>
                                <p class="text-xs text-gray-500">Users must verify email before posting</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="require_email_verification" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-primary"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Enable Two-Factor Authentication
                                </label>
                                <p class="text-xs text-gray-500">Add extra security for admin accounts</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_2fa" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-primary"></div>
                            </label>
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
                            <a href="{{ route('admin.settings.export-database') }}" 
                               class="w-full flex items-center justify-between px-4 py-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border border-blue-200">
                                <div class="flex items-center">
                                    <i class="fas fa-database text-blue-600 mr-3"></i>
                                    <span class="text-sm font-medium text-blue-700">Export Database (SQL)</span>
                                </div>
                                <i class="fas fa-download text-blue-600"></i>
                            </a>
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
document.addEventListener('DOMContentLoaded', function() {
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
        fetch('{{ route("admin.settings.test-email") }}', {
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
    fetch('{{ route("admin.settings.import-database") }}', {
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
</script>
@endsection

