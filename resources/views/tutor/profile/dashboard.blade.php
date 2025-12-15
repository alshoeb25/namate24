@extends('layouts.app')

@section('title', 'Tutor Profile Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Profile Header -->
    <div class="mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-8 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <img src="{{ $tutor->user->avatar ? asset('storage/' . $tutor->user->avatar) : 'https://via.placeholder.com/120' }}" 
                         alt="{{ $tutor->user->name }}" 
                         class="w-24 h-24 rounded-full border-4 border-white mr-6">
                    <div>
                        <h1 class="text-3xl font-bold">{{ $tutor->user->name }}</h1>
                        <p class="text-blue-100">{{ $tutor->headline ?? 'Complete your profile to add headline' }}</p>
                        <div class="mt-2 flex items-center">
                            <span class="text-yellow-300">★ {{ $tutor->rating_avg ?? 'N/A' }}</span>
                            <span class="text-blue-100 ml-4">({{ $tutor->rating_count ?? 0 }} reviews)</span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold">{{ $completionPercentage }}%</div>
                    <p class="text-blue-100">Profile Complete</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Completion Progress Bar -->
    <div class="mb-8 bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Profile Completion</h2>
        <div class="w-full bg-gray-200 rounded-full h-4">
            <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-4 rounded-full transition-all duration-300" 
                 style="width: {{ $completionPercentage }}%"></div>
        </div>
        <p class="text-gray-600 mt-2">{{ $completionPercentage }}% complete - Keep going to unlock more features!</p>
    </div>

    <!-- Alert Messages -->
    @if ($errors->any())
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <strong>Please fix the following errors:</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            {{ session('success') }}
        </div>
    @endif

    <!-- Navigation Tabs/Steps -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Personal Details Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Personal Details</h3>
                    <span class="inline-block w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user"></i>
                    </span>
                </div>
                <p class="text-gray-600 text-sm mb-4">Add your name, email, phone, and gender</p>
                <a href="{{ route('tutor.profile.personal-details') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    {{ $tutor->user->name ? 'Edit' : 'Complete' }}
                </a>
            </div>
        </div>

        <!-- Photo Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Profile Photo</h3>
                    <span class="inline-block w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-image"></i>
                    </span>
                </div>
                <p class="text-gray-600 text-sm mb-4">Upload a professional profile picture</p>
                <a href="{{ route('tutor.profile.photo') }}" class="inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    {{ $tutor->user->avatar ? 'Change' : 'Upload' }}
                </a>
            </div>
        </div>

        <!-- Introductory Video Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Video Introduction</h3>
                    <span class="inline-block w-10 h-10 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-video"></i>
                    </span>
                </div>
                <p class="text-gray-600 text-sm mb-4">Record a video introducing yourself</p>
                <a href="{{ route('tutor.profile.video') }}" class="inline-block px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                    {{ $tutor->introductory_video ? 'Replace' : 'Upload' }}
                </a>
            </div>
        </div>

        <!-- Subjects Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Subjects</h3>
                    <span class="inline-block w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-book"></i>
                    </span>
                </div>
                <p class="text-gray-600 text-sm mb-4">{{ $tutor->subjects()->count() }} subjects selected</p>
                <a href="{{ route('tutor.profile.subjects') }}" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    Manage Subjects
                </a>
            </div>
        </div>

        <!-- Address Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Address</h3>
                    <span class="inline-block w-10 h-10 bg-red-100 text-red-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-map-marker-alt"></i>
                    </span>
                </div>
                <p class="text-gray-600 text-sm mb-4">{{ $tutor->city ?? 'Add your location' }}</p>
                <a href="{{ route('tutor.profile.address') }}" class="inline-block px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                    {{ $tutor->city ? 'Edit' : 'Add' }}
                </a>
            </div>
        </div>

        <!-- Education Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Education</h3>
                    <span class="inline-block w-10 h-10 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-graduation-cap"></i>
                    </span>
                </div>
                <p class="text-gray-600 text-sm mb-4">{{ count($tutor->educations ?? []) }} entries added</p>
                <a href="{{ route('tutor.profile.education') }}" class="inline-block px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                    Manage Education
                </a>
            </div>
        </div>

        <!-- Experience Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Experience</h3>
                    <span class="inline-block w-10 h-10 bg-cyan-100 text-cyan-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-briefcase"></i>
                    </span>
                </div>
                <p class="text-gray-600 text-sm mb-4">{{ count($tutor->experiences ?? []) }} entries added</p>
                <a href="{{ route('tutor.profile.experience') }}" class="inline-block px-4 py-2 bg-cyan-600 text-white rounded hover:bg-cyan-700">
                    Manage Experience
                </a>
            </div>
        </div>

        <!-- Teaching Details Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Teaching Details</h3>
                    <span class="inline-block w-10 h-10 bg-pink-100 text-pink-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-chalkboard-user"></i>
                    </span>
                </div>
                <p class="text-gray-600 text-sm mb-4">${{ $tutor->price_per_hour ?? 'Not set' }}/hour</p>
                <a href="{{ route('tutor.profile.teaching-details') }}" class="inline-block px-4 py-2 bg-pink-600 text-white rounded hover:bg-pink-700">
                    {{ $tutor->price_per_hour ? 'Edit' : 'Set' }}
                </a>
            </div>
        </div>

        <!-- Description Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Profile Description</h3>
                    <span class="inline-block w-10 h-10 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-pen-fancy"></i>
                    </span>
                </div>
                <p class="text-gray-600 text-sm mb-4">{{ $tutor->headline ? 'Added' : 'Not added' }}</p>
                <a href="{{ route('tutor.profile.description') }}" class="inline-block px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">
                    {{ $tutor->headline ? 'Edit' : 'Add' }}
                </a>
            </div>
        </div>

        <!-- Courses Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Courses</h3>
                    <span class="inline-block w-10 h-10 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-presentation"></i>
                    </span>
                </div>
                <p class="text-gray-600 text-sm mb-4">{{ count($tutor->courses ?? []) }} courses added</p>
                <a href="{{ route('tutor.profile.courses') }}" class="inline-block px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">
                    Manage Courses
                </a>
            </div>
        </div>

        <!-- View Profile Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">View Profile</h3>
                    <span class="inline-block w-10 h-10 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <p class="text-gray-600 text-sm mb-4">See how your profile looks</p>
                <a href="{{ route('tutor.profile.view', $tutor->id) }}" class="inline-block px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    Preview
                </a>
            </div>
        </div>

        <!-- Settings Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Settings</h3>
                    <span class="inline-block w-10 h-10 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-cog"></i>
                    </span>
                </div>
                <p class="text-gray-600 text-sm mb-4">Manage your preferences</p>
                <a href="{{ route('tutor.profile.settings') }}" class="inline-block px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    Configure
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
