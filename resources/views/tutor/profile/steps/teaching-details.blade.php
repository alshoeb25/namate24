@extends('layouts.app')

@section('title', 'Teaching Details')

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
            <h1 class="text-3xl font-bold mb-2 text-gray-800">Teaching Details</h1>
            <p class="text-gray-600 mb-8">Set your teaching rates and preferences</p>

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

            <form action="{{ route('tutor.profile.update-teaching-details') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Experience Years -->
                <div>
                    <label for="experience_years" class="block text-sm font-medium text-gray-700 mb-2">Years of Experience</label>
                    <input type="number" id="experience_years" name="experience_years" 
                           value="{{ old('experience_years', $tutor->experience_years ?? '') }}"
                           min="0" max="70"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                           placeholder="Enter your years of experience" required>
                </div>

                <!-- Price Per Hour -->
                <div>
                    <label for="price_per_hour" class="block text-sm font-medium text-gray-700 mb-2">Price Per Hour ($)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-2.5 text-gray-500 text-lg">$</span>
                        <input type="number" id="price_per_hour" name="price_per_hour" step="0.01"
                               value="{{ old('price_per_hour', $tutor->price_per_hour ?? '') }}"
                               min="0"
                               class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                               placeholder="Enter your hourly rate" required>
                    </div>
                </div>

                <!-- Teaching Mode -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Teaching Mode</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="checkbox" id="mode_online" name="teaching_mode[]" value="online"
                                   {{ in_array('online', old('teaching_mode', $tutor->teaching_mode ?? [])) ? 'checked' : '' }}
                                   class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
                            <label for="mode_online" class="ml-3 block text-sm text-gray-700">
                                Online
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="mode_offline" name="teaching_mode[]" value="offline"
                                   {{ in_array('offline', old('teaching_mode', $tutor->teaching_mode ?? [])) ? 'checked' : '' }}
                                   class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
                            <label for="mode_offline" class="ml-3 block text-sm text-gray-700">
                                Offline (In-Person)
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="mode_both" name="teaching_mode[]" value="both"
                                   {{ in_array('both', old('teaching_mode', $tutor->teaching_mode ?? [])) ? 'checked' : '' }}
                                   class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
                            <label for="mode_both" class="ml-3 block text-sm text-gray-700">
                                Both (Hybrid)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Availability -->
                <div>
                    <label for="availability" class="block text-sm font-medium text-gray-700 mb-2">Availability</label>
                    <textarea id="availability" name="availability" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                              placeholder="e.g., Monday-Friday 3PM-8PM, Weekends available
Saturday 10AM-4PM
Sunday 2PM-6PM"
                              required>{{ old('availability', $tutor->availability ?? '') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Describe your available teaching hours</p>
                </div>

                <!-- Info Box -->
                <div class="bg-pink-50 border border-pink-200 rounded-lg p-4">
                    <h4 class="font-medium text-pink-900 mb-2">Pricing Tips</h4>
                    <ul class="text-sm text-pink-800 space-y-1 list-disc list-inside">
                        <li>Consider your experience and qualifications</li>
                        <li>Research rates for similar tutors in your area</li>
                        <li>You can adjust pricing later</li>
                    </ul>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-pink-600 text-white rounded-lg hover:bg-pink-700 font-medium">
                        <i class="fas fa-save mr-2"></i> Save Teaching Details
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
