@extends('layouts.app')

@section('title', 'Address')

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
            <h1 class="text-3xl font-bold mb-2 text-gray-800">Address</h1>
            <p class="text-gray-600 mb-8">Update your location information</p>

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

            <form action="{{ route('tutor.profile.update-address') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Street Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Street Address</label>
                    <input type="text" id="address" name="address" 
                           value="{{ old('address', $tutor->address ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                           placeholder="Enter your street address" required>
                </div>

                <!-- City -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City</label>
                    <input type="text" id="city" name="city" 
                           value="{{ old('city', $tutor->city ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                           placeholder="Enter your city" required>
                </div>

                <!-- State -->
                <div>
                    <label for="state" class="block text-sm font-medium text-gray-700 mb-2">State/Province</label>
                    <input type="text" id="state" name="state" 
                           value="{{ old('state', $tutor->state ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                           placeholder="Enter your state/province" required>
                </div>

                <!-- Country -->
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                    <input type="text" id="country" name="country" 
                           value="{{ old('country', $tutor->country ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                           placeholder="Enter your country" required>
                </div>

                <!-- Postal Code -->
                <div>
                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code" 
                           value="{{ old('postal_code', $tutor->postal_code ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                           placeholder="Enter your postal code" required>
                </div>

                <!-- Latitude -->
                <div>
                    <label for="lat" class="block text-sm font-medium text-gray-700 mb-2">Latitude</label>
                    <input type="number" id="lat" name="lat" step="0.0001"
                           value="{{ old('lat', $tutor->lat ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                           placeholder="Enter latitude (e.g., 40.7128)" required>
                    <p class="text-xs text-gray-500 mt-1">Use a map tool to find your coordinates</p>
                </div>

                <!-- Longitude -->
                <div>
                    <label for="lng" class="block text-sm font-medium text-gray-700 mb-2">Longitude</label>
                    <input type="number" id="lng" name="lng" step="0.0001"
                           value="{{ old('lng', $tutor->lng ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                           placeholder="Enter longitude (e.g., -74.0060)" required>
                    <p class="text-xs text-gray-500 mt-1">Use a map tool to find your coordinates</p>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-medium text-blue-900 mb-2">How to find coordinates?</h4>
                    <p class="text-sm text-blue-800">Visit <a href="https://www.google.com/maps" target="_blank" class="underline font-medium">Google Maps</a>, search for your address, and right-click to see the coordinates (latitude, longitude).</p>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                        <i class="fas fa-save mr-2"></i> Save Address
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
