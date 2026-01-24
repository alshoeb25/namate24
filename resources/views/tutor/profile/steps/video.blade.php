@extends('layouts.app')

@section('title', 'Introductory Video')

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
            <h1 class="text-3xl font-bold mb-2 text-gray-800">Introductory Video</h1>
            <p class="text-gray-600 mb-8">Add your introduction for students. You may either upload a file (MP4, MOV, AVI, WMV - Max 100MB) or provide a YouTube link (preferred). Students find YouTube-hosted intros more trustworthy — upload to your YouTube account and paste the URL below.</p>

            <!-- Video Status Alert -->
            @if ($tutor->video_approval_status === 'pending')
                <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-clock text-yellow-600 text-xl mr-3"></i>
                        <div>
                            <h4 class="font-bold text-yellow-800 mb-1">Video Pending Approval</h4>
                            <p class="text-sm text-yellow-700">Your video has been submitted and is awaiting admin approval. You will be notified once it's reviewed.</p>
                        </div>
                    </div>
                </div>
            @elseif ($tutor->video_approval_status === 'approved')
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 text-xl mr-3"></i>
                            <div>
                                <h4 class="font-bold text-green-800 mb-1">Video Approved ✓</h4>
                                <p class="text-sm text-green-700">Your introduction video has been approved and is visible to students on your profile.</p>
                            </div>
                        </div>
                        <a href="{{ route('tutor.profile.view') }}" class="ml-4 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium whitespace-nowrap">
                            <i class="fas fa-eye mr-2"></i> View in Profile
                        </a>
                    </div>
                </div>
            @elseif ($tutor->video_approval_status === 'rejected')
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start flex-1">
                            <i class="fas fa-times-circle text-red-600 text-xl mr-3 mt-1"></i>
                            <div class="flex-1">
                                <h4 class="font-bold text-red-800 mb-3">Video Rejected - Action Required</h4>
                                <div class="bg-white rounded-lg border border-red-300 p-4 mb-3">
                                    <p class="text-sm text-red-700 font-semibold mb-2"><i class="fas fa-exclamation-circle mr-2"></i>Reason for Rejection:</p>
                                    <p class="text-sm text-red-800 leading-relaxed">{{ $tutor->video_rejection_reason }}</p>
                                </div>
                                <p class="text-sm text-red-700 mb-3">Please address the feedback above and upload a new video. You can use the form below to resubmit.</p>
                                <button type="button" onclick="document.getElementById('delete-video-form').style.display='block'" class="inline-flex items-center px-3 py-1 bg-red-600 text-white rounded text-sm font-medium hover:bg-red-700">
                                    <i class="fas fa-trash mr-1"></i> Delete Rejected Video
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Video Form -->
                <div id="delete-video-form" style="display: none;" class="mb-6 bg-red-100 border border-red-300 rounded-lg p-4">
                    <h4 class="font-bold text-red-800 mb-3">Confirm Deletion</h4>
                    <p class="text-sm text-red-700 mb-4">Are you sure you want to delete this rejected video? You can upload a new one after deletion.</p>
                    <form action="{{ route('tutor.profile.delete-video') }}" method="POST" class="flex gap-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 font-medium text-sm">
                            <i class="fas fa-check mr-1"></i> Yes, Delete Video
                        </button>
                        <button type="button" onclick="document.getElementById('delete-video-form').style.display='none'" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500 font-medium text-sm">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                    </form>
                </div>
            @endif

            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-bold mb-2">Intro Video Tips</h4>
                <ul class="list-disc list-inside text-sm text-gray-700">
                    <li>Include your name, location, subjects, languages, qualifications, and what students can expect.</li>
                    <li>Record in landscape, well-lit, neutral background; clear audio; max 3 minutes.</li>
                    <li>Add a YouTube description of 100+ words and avoid sharing contact details.</li>
                    <li>DO NOT include other people, logos, slides, or contact details.</li>
                </ul>
            </div>

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

            <!-- Current Video Display or YouTube Link -->
            @if ($tutor->introductory_video || $tutor->youtube_intro_url)
                @if ($tutor->introductory_video)
                    <div class="mb-8 bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                @if ($tutor->video_approval_status === 'approved')
                                    <p class="text-sm font-medium text-green-700"><i class="fas fa-check-circle mr-2"></i>✓ Your Approved Video</p>
                                @elseif ($tutor->video_approval_status === 'pending')
                                    <p class="text-sm font-medium text-yellow-700"><i class="fas fa-clock mr-2"></i>Video Pending Approval</p>
                                @elseif ($tutor->video_approval_status === 'rejected')
                                    <p class="text-sm font-medium text-red-700"><i class="fas fa-times-circle mr-2"></i>Video Rejected</p>
                                @else
                                    <p class="text-sm font-medium text-gray-700">Your Uploaded Video</p>
                                @endif
                            </div>
                            <form action="{{ route('tutor.profile.delete-video') }}" method="POST" onsubmit="return confirm('Delete this video?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 font-medium">
                                    <i class="fas fa-trash mr-1"></i> Delete
                                </button>
                            </form>
                        </div>
                        <video class="w-full max-w-md rounded-lg border-4 border-purple-200" controls>
                            <source src="{{ asset('storage/' . $tutor->introductory_video) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        @if ($tutor->video_title)
                            <p class="text-sm text-gray-600 mt-3"><strong>Title:</strong> {{ $tutor->video_title }}</p>
                        @endif
                        <p class="text-xs text-gray-500 mt-2">Status: 
                            <span class="inline-block px-2 py-1 rounded-full text-white
                                @if ($tutor->video_approval_status === 'approved') bg-green-500
                                @elseif ($tutor->video_approval_status === 'pending') bg-yellow-500
                                @elseif ($tutor->video_approval_status === 'rejected') bg-red-500
                                @else bg-gray-500
                                @endif">
                                {{ ucfirst($tutor->video_approval_status ?? 'Not submitted') }}
                            </span>
                        </p>
                    </div>
                @elseif($tutor->youtube_intro_url)
                    <div class="mb-8 bg-white rounded-lg shadow-lg p-6 border-l-4 border-red-500">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                @if ($tutor->video_approval_status === 'approved')
                                    <p class="text-sm font-medium text-green-700"><i class="fas fa-check-circle mr-2"></i>✓ Your Approved YouTube Video</p>
                                @elseif ($tutor->video_approval_status === 'pending')
                                    <p class="text-sm font-medium text-yellow-700"><i class="fas fa-clock mr-2"></i>YouTube Video Pending Approval</p>
                                @elseif ($tutor->video_approval_status === 'rejected')
                                    <p class="text-sm font-medium text-red-700"><i class="fas fa-times-circle mr-2"></i>YouTube Video Rejected</p>
                                @else
                                    <p class="text-sm font-medium text-gray-700">Your YouTube Video</p>
                                @endif
                            </div>
                            <form action="{{ route('tutor.profile.delete-video') }}" method="POST" onsubmit="return confirm('Delete this video?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 font-medium">
                                    <i class="fas fa-trash mr-1"></i> Delete
                                </button>
                            </form>
                        </div>
                        @php
                            // Extract YouTube ID for embed
                            $ytId = null;
                            if (preg_match('/(?:v=|be\/|embed\/)([A-Za-z0-9_-]{6,11})/', $tutor->youtube_intro_url, $m)) {
                                $ytId = $m[1];
                            }
                        @endphp
                        @if($ytId)
                            <iframe class="w-full max-w-md rounded-lg border-4 border-red-200" height="320" src="https://www.youtube.com/embed/{{ $ytId }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        @else
                            <p class="text-sm text-gray-600 mt-4">
                                YouTube Link: <a href="{{ $tutor->youtube_intro_url }}" target="_blank" class="text-red-600 hover:text-red-700 font-semibold">{{ $tutor->youtube_intro_url }}</a>
                            </p>
                        @endif
                        @if ($tutor->video_title)
                            <p class="text-sm text-gray-600 mt-3"><strong>Title:</strong> {{ $tutor->video_title }}</p>
                        @endif
                        <p class="text-xs text-gray-500 mt-2">Status: 
                            <span class="inline-block px-2 py-1 rounded-full text-white
                                @if ($tutor->video_approval_status === 'approved') bg-green-500
                                @elseif ($tutor->video_approval_status === 'pending') bg-yellow-500
                                @elseif ($tutor->video_approval_status === 'rejected') bg-red-500
                                @else bg-gray-500
                                @endif">
                                {{ ucfirst($tutor->video_approval_status ?? 'Not submitted') }}
                            </span>
                        </p>
                    </div>
                @endif
            @else
                <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <p class="text-sm text-blue-700"><i class="fas fa-info-circle mr-2"></i>You haven't uploaded an introduction video yet. Add one using the form below.</p>
                </div>
            @endif

            <form action="{{ route('tutor.profile.update-video') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Video Title -->
                <div>
                    <label for="video_title" class="block text-sm font-medium text-gray-700 mb-2">Video Title</label>
                    <input type="text" id="video_title" name="video_title" 
                           value="{{ old('video_title', $tutor->video_title ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="e.g., Welcome to my tutoring journey" required>
                </div>

                <!-- YouTube URL (preferred) -->
                <div>
                    <label for="youtube_url" class="block text-sm font-medium text-gray-700 mb-2">YouTube URL (preferred)</label>
                    <input type="url" id="youtube_url" name="youtube_url" value="{{ old('youtube_url', $tutor->youtube_intro_url ?? '') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="https://www.youtube.com/watch?v=...">
                    <p class="text-sm text-gray-500 mt-2">Preferable: upload to YouTube and paste the URL. This improves reach and trust.</p>
                </div>

                <!-- Video Upload -->
                <div>
                    <label for="video" class="block text-sm font-medium text-gray-700 mb-2">Upload Video</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-purple-400 transition-colors">
                        <input type="file" id="video" name="video" 
                               class="hidden" 
                               accept="video/*"
                               onchange="previewVideo(event)">
                        <label for="video" class="cursor-pointer">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-gray-600">Drag and drop your video here, or <span class="text-purple-600 font-medium">click to select</span></p>
                                <p class="text-sm text-gray-500 mt-2">MP4, MOV, AVI, WMV (Max 100MB). If you provide a YouTube URL, file upload is optional.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Video Preview -->
                <div id="previewContainer" class="hidden">
                    <p class="text-sm font-medium text-gray-700 mb-3">Preview</p>
                    <video id="videoPreview" class="w-full max-w-sm rounded-lg border-4 border-purple-200" controls>
                        Your browser does not support the video tag.
                    </video>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium">
                        <i class="fas fa-upload mr-2"></i> Upload Video
                    </button>
                    <a href="{{ route('tutor.profile.dashboard') }}" class="flex-1 px-6 py-3 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 font-medium text-center">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewVideo(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('videoPreview');
            preview.src = e.target.result;
            document.getElementById('previewContainer').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
