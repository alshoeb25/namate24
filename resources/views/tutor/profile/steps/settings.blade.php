@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('tutor.profile.dashboard') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-8">
            <h1 class="text-3xl font-bold mb-2 text-gray-800">Settings</h1>
            <p class="text-gray-600 mb-8">Manage your profile preferences and notifications</p>

            @if ($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('tutor.profile.update-settings') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Notification Settings -->
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Notification Preferences</h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="email_notifications" name="email_notifications" value="1"
                                   {{ (old('email_notifications') ?? ($user->settings['email_notifications'] ?? true)) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="email_notifications" class="ml-3 flex flex-col">
                                <span class="font-medium text-gray-700">Email Notifications</span>
                                <span class="text-sm text-gray-500">Receive updates about new bookings and messages</span>
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="sms_notifications" name="sms_notifications" value="1"
                                   {{ (old('sms_notifications') ?? ($user->settings['sms_notifications'] ?? false)) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="sms_notifications" class="ml-3 flex flex-col">
                                <span class="font-medium text-gray-700">SMS Notifications</span>
                                <span class="text-sm text-gray-500">Get important updates via text message</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Profile Visibility -->
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Profile Visibility</h2>
                    
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input type="radio" id="profile_public" name="profile_visibility" value="public"
                                   {{ old('profile_visibility', $user->settings['profile_visibility'] ?? 'public') === 'public' ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <label for="profile_public" class="ml-3 flex flex-col">
                                <span class="font-medium text-gray-700">Public Profile</span>
                                <span class="text-sm text-gray-500">Your profile will be visible to students searching for tutors</span>
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="radio" id="profile_private" name="profile_visibility" value="private"
                                   {{ old('profile_visibility', $user->settings['profile_visibility'] ?? 'public') === 'private' ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <label for="profile_private" class="ml-3 flex flex-col">
                                <span class="font-medium text-gray-700">Private Profile</span>
                                <span class="text-sm text-gray-500">Only visible to students who have your profile link</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Language Preference -->
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Language Preference</h2>
                    
                    <select name="language" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="en" {{ old('language', $user->settings['language'] ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                        <option value="es" {{ old('language', $user->settings['language'] ?? 'en') === 'es' ? 'selected' : '' }}>Spanish</option>
                        <option value="fr" {{ old('language', $user->settings['language'] ?? 'en') === 'fr' ? 'selected' : '' }}>French</option>
                        <option value="de" {{ old('language', $user->settings['language'] ?? 'en') === 'de' ? 'selected' : '' }}>German</option>
                    </select>
                </div>

                <!-- Account Actions -->
                <div class="bg-red-50 rounded-lg p-6 border border-red-200">
                    <h2 class="text-xl font-bold text-red-800 mb-4">Account Actions</h2>
                    
                    <div class="space-y-3">
                        <button type="button" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium text-left">
                            <i class="fas fa-lock mr-2"></i> Change Password
                        </button>
                        
                        <button type="button" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium text-left">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout from All Devices
                        </button>
                        
                        <button type="button" class="w-full px-4 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800 font-medium text-left">
                            <i class="fas fa-trash mr-2"></i> Delete Account
                        </button>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-medium text-blue-900 mb-2">Privacy Notice</h4>
                    <p class="text-sm text-blue-800">
                        We respect your privacy. Your email and phone number will only be shared with students you've accepted to tutor.
                        Learn more in our <a href="#" class="underline font-medium">Privacy Policy</a>.
                    </p>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        <i class="fas fa-save mr-2"></i> Save Settings
                    </button>
                    <a href="{{ route('tutor.profile.dashboard') }}" class="flex-1 px-6 py-3 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 font-medium text-center">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
