@extends('layouts.app')

@section('title', 'Personal Details')

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
            <h1 class="text-3xl font-bold mb-2 text-gray-800">Personal Details</h1>
            <p class="text-gray-600 mb-8">Update your personal information</p>

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

            <form action="{{ route('tutor.profile.update-personal-details') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Enter your full name" required>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Enter your email" required>
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <div class="flex gap-2">
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" 
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter your phone number" required>

                        <button type="submit" formaction="{{ route('tutor.profile.phone.send-otp') }}" formmethod="post" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Send OTP
                        </button>
                    </div>
                    @if(session('otp_debug'))
                        <div class="mt-2 text-sm text-yellow-700">(DEV OTP: {{ session('otp_debug') }})</div>
                    @endif
                </div>

                <!-- OTP Verification -->
                <div class="mt-4">
                    <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">Enter OTP</label>
                    <div class="flex gap-2">
                        <input type="text" id="otp" name="otp" placeholder="6-digit code"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="submit" formaction="{{ route('tutor.profile.phone.verify-otp') }}" formmethod="post" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Verify
                        </button>
                    </div>
                </div>

                <!-- Gender -->
                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                    <select id="gender" name="gender" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        <option value="">-- Select Gender --</option>
                        <option value="male" {{ old('gender', $tutor->gender) === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $tutor->gender) === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', $tutor->gender) === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Speciality / Current Role -->
                <div>
                    <label for="current_role" class="block text-sm font-medium text-gray-700 mb-2">Current Role / Speciality</label>
                    <input type="text" id="current_role" name="current_role" value="{{ old('current_role', $tutor->current_role ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., Senior Math Instructor / Machine Learning Specialist">
                </div>

                <div>
                    <label for="speciality" class="block text-sm font-medium text-gray-700 mb-2">Speciality (optional)</label>
                    <input type="text" id="speciality" name="speciality" value="{{ old('speciality', $tutor->speciality ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., Calculus, Data Science, Spoken English">
                </div>

                <div>
                    <label for="strength" class="block text-sm font-medium text-gray-700 mb-2">Strength / Short summary (optional)</label>
                    <textarea id="strength" name="strength" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Short strengths or areas you excel in">{{ old('strength', $tutor->strength ?? '') }}</textarea>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        <i class="fas fa-save mr-2"></i> Save Changes
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
