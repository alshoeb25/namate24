@extends('layouts.app')

@section('title', 'View Profile')

@section('content')
<div class="container mx-auto px-4 py-8">
    @if (Auth::check() && Auth::id() === $tutor->user_id)
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('tutor.profile.dashboard') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>
    @endif

    <!-- Profile Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-8 text-white mb-8">
        <div class="flex items-center justify-between flex-wrap gap-6">
            <div class="flex items-center">
                <img src="{{ $tutor->user->avatar ? asset('storage/' . $tutor->user->avatar) : 'https://via.placeholder.com/150' }}" 
                     alt="{{ $tutor->user->name }}" 
                     class="w-32 h-32 rounded-full border-4 border-white mr-8 object-cover">
                <div>
                    <h1 class="text-4xl font-bold mb-2">{{ $tutor->user->name }}</h1>
                    <p class="text-blue-100 text-lg mb-2">{{ $tutor->headline ?? 'Tutor' }}</p>
                    @if ($tutor->current_role || $tutor->speciality)
                        <p class="text-blue-100 text-sm mb-3">
                            @if ($tutor->current_role)
                                <span class="font-semibold">{{ $tutor->current_role }}</span>
                                @if ($tutor->speciality)
                                    • {{ $tutor->speciality }}
                                @endif
                            @endif
                        </p>
                    @endif
                    <div class="flex items-center gap-4 flex-wrap">
                        <div class="flex items-center">
                            <span class="text-yellow-300 text-xl">★ {{ $tutor->rating_avg ?? 'New' }}</span>
                            <span class="text-blue-100 ml-2">({{ $tutor->rating_count ?? 0 }} reviews)</span>
                        </div>
                        @if ($tutor->verified)
                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium">✓ Verified</span>
                        @endif
                        @if ($tutor->user->phone_verified)
                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium">✓ Phone Verified</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Personal Strengths -->
            @if ($tutor->strength)
                <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg shadow-md p-6 border-l-4 border-green-500">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">My Strengths</h2>
                    <p class="text-gray-700 leading-relaxed">
                        {{ $tutor->strength }}
                    </p>
                </div>
            @endif

            <!-- About Section -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">About Me</h2>
                <p class="text-gray-700 leading-relaxed">
                    {{ $tutor->about ?? 'No about information provided yet.' }}
                </p>
            </div>

            <!-- Introduction Video (Uploaded or YouTube) -->
            @if ($tutor->introductory_video || $tutor->youtube_intro_url)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Introduction Video</h2>
                    @if ($tutor->introductory_video)
                        <video class="w-full rounded-lg" controls>
                            <source src="{{ asset('storage/' . $tutor->introductory_video) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    @else
                        @php
                            $ytId = null;
                            if (isset($tutor->youtube_intro_url) && preg_match('/(?:v=|be\/|embed\/)([A-Za-z0-9_-]{6,11})/', $tutor->youtube_intro_url, $m)) {
                                $ytId = $m[1];
                            }
                        @endphp
                        @if($ytId)
                            <iframe class="w-full rounded-lg" height="360" src="https://www.youtube.com/embed/{{ $ytId }}" frameborder="0" allowfullscreen></iframe>
                        @else
                            <p class="text-sm text-gray-600">YouTube link: <a href="{{ $tutor->youtube_intro_url }}" target="_blank" class="text-purple-600">Open on YouTube</a></p>
                        @endif
                    @endif
                    @if ($tutor->video_title)
                        <p class="text-gray-600 mt-2">{{ $tutor->video_title }}</p>
                    @endif
                </div>
            @endif

            <!-- Teaching Methodology -->
            @if ($tutor->teaching_methodology)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">My Teaching Approach</h2>
                    <p class="text-gray-700 leading-relaxed">
                        {{ $tutor->teaching_methodology }}
                    </p>
                </div>
            @endif

            <!-- Subjects -->
            @if ($tutor->subjects()->count() > 0)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Subjects I Teach</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach ($tutor->subjects as $subject)
                            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-3">
                                <p class="font-medium text-indigo-900">{{ $subject->name }}</p>
                                <p class="text-xs text-indigo-600 mt-1 capitalize">{{ $subject->pivot->level ?? 'Not specified' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Education -->
            @if (count($tutor->educations ?? []) > 0)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Education</h2>
                    <div class="space-y-5">
                        @foreach ($tutor->educations as $education)
                            <div class="border-l-4 border-yellow-500 pl-4 pb-4">
                                <!-- Degree Type Badge -->
                                @if ($education['degree_type'] ?? false)
                                    <div class="mb-2">
                                        <span class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">{{ ucfirst(str_replace('_', ' ', $education['degree_type'])) }}</span>
                                    </div>
                                @endif
                                
                                <!-- Degree Name -->
                                <h4 class="font-bold text-gray-800 text-lg">{{ $education['degree_name'] ?? 'Degree' }}</h4>
                                
                                <!-- Institution and City -->
                                <p class="text-gray-600 font-medium">
                                    {{ $education['institution'] ?? 'Institution' }}
                                    @if ($education['city'] ?? false)
                                        <span class="text-gray-500">• {{ $education['city'] }}</span>
                                    @endif
                                </p>
                                
                                <!-- Study Mode -->
                                @if ($education['study_mode'] ?? false)
                                    <p class="text-sm text-gray-500"><strong>Study Mode:</strong> {{ ucfirst(str_replace('_', ' ', $education['study_mode'])) }}</p>
                                @endif
                                
                                <!-- Duration -->
                                <p class="text-sm text-gray-500">
                                    @php
                                        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                        $startMonth = isset($education['start_month']) ? $months[(int)$education['start_month'] - 1] ?? '' : '';
                                        $endMonth = isset($education['end_month']) ? $months[(int)$education['end_month'] - 1] ?? '' : '';
                                    @endphp
                                    {{ $startMonth }} {{ $education['start_year'] ?? '' }} - 
                                    @if ($education['end_year'] ?? false)
                                        {{ $endMonth }} {{ $education['end_year'] }}
                                    @else
                                        Present
                                    @endif
                                </p>
                                
                                <!-- Speciality -->
                                @if ($education['speciality'] ?? false)
                                    <p class="text-sm text-gray-600 mt-2"><strong>Speciality:</strong> {{ $education['speciality'] }}</p>
                                @endif
                                
                                <!-- Score -->
                                @if ($education['score'] ?? false)
                                    <p class="text-sm text-gray-600"><strong>Score:</strong> {{ $education['score'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Experience -->
            @if (count($tutor->experiences ?? []) > 0)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Professional Experience</h2>
                    <div class="space-y-5">
                        @foreach ($tutor->experiences as $experience)
                            <div class="border-l-4 border-cyan-500 pl-4 pb-4">
                                <!-- Title and Company -->
                                <h4 class="font-bold text-gray-800 text-lg">{{ $experience['title'] ?? 'Position' }}</h4>
                                <p class="text-gray-600 font-medium">
                                    {{ $experience['company'] ?? 'Company' }}
                                    @if ($experience['city'] ?? false)
                                        <span class="text-gray-500">• {{ $experience['city'] }}</span>
                                    @endif
                                </p>
                                
                                <!-- Designation -->
                                @if ($experience['designation'] ?? false)
                                    <p class="text-sm text-gray-600"><strong>Designation:</strong> {{ $experience['designation'] }}</p>
                                @endif
                                
                                <!-- Association Type -->
                                @if ($experience['association'] ?? false)
                                    <p class="text-sm">
                                        <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">{{ $experience['association'] }}</span>
                                    </p>
                                @endif
                                
                                <!-- Duration -->
                                <p class="text-sm text-gray-500 mt-1">
                                    @if ($experience['currently_working'])
                                        {{ date('M Y', strtotime($experience['start_date'] ?? now())) }} - Present
                                    @else
                                        {{ date('M Y', strtotime($experience['start_date'] ?? now())) }} - {{ date('M Y', strtotime($experience['end_date'] ?? now())) }}
                                    @endif
                                </p>
                                
                                <!-- Roles and Responsibilities -->
                                @if ($experience['roles'] ?? false)
                                    <p class="text-gray-600 mt-2 text-sm whitespace-pre-wrap"><strong>Roles & Responsibilities:</strong><br>{{ $experience['roles'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Courses -->
            @if (count($tutor->courses ?? []) > 0)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">My Courses</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($tutor->courses as $course)
                            <div class="border border-orange-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <!-- Title and Description -->
                                <h4 class="font-bold text-gray-800 mb-2">{{ $course['title'] }}</h4>
                                <p class="text-sm text-gray-600 mb-3">{{ $course['description'] }}</p>

                                <!-- Course Details Grid -->
                                <div class="space-y-2 text-xs text-gray-600 mb-3 border-t pt-3">
                                    <div class="flex justify-between">
                                        <span><strong>Level:</strong></span>
                                        <span class="capitalize">{{ $course['level'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span><strong>Duration:</strong></span>
                                        <span>{{ $course['duration'] ?? '0' }} {{ $course['duration_unit'] ?? 'hours' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span><strong>Price:</strong></span>
                                        <span>{{ $course['currency'] ?? 'USD' }} {{ number_format($course['price'] ?? 0, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span><strong>Mode:</strong></span>
                                        <span class="capitalize">
                                            @switch($course['mode_of_delivery'] ?? 'online')
                                                @case('online')
                                                    Online
                                                    @break
                                                @case('institute')
                                                    At Institute
                                                    @break
                                                @case('student_home')
                                                    At Student's Home
                                                    @break
                                                @case('flexible')
                                                    Flexible
                                                    @break
                                                @default
                                                    {{ $course['mode_of_delivery'] }}
                                            @endswitch
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span><strong>Group Size:</strong></span>
                                        <span>{{ $course['group_size'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span><strong>Certificate:</strong></span>
                                        <span>
                                            @if ($course['certificate_provided'] === 'yes' || $course['certificate_provided'] === true)
                                                <span class="text-green-600">✓ Yes</span>
                                            @else
                                                <span class="text-gray-500">No</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span><strong>Language:</strong></span>
                                        <span>{{ $course['language'] ?? 'Not specified' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Teaching Details Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4"><i class="fas fa-graduation-cap mr-2 text-blue-600"></i>Teaching Details</h3>
                <div class="space-y-3 border-t pt-3">
                    <div>
                        <p class="text-gray-600 text-sm">Experience</p>
                        <p class="font-bold text-lg text-gray-800">{{ $tutor->experience_years ?? 0 }} years</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Hourly Rate</p>
                        <p class="font-bold text-lg text-green-600">${{ number_format($tutor->price_per_hour ?? 0, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Teaching Mode</p>
                        <div class="flex gap-1 mt-1 flex-wrap">
                            @foreach ($tutor->teaching_mode ?? [] as $mode)
                                <span class="bg-pink-100 text-pink-800 px-2 py-1 rounded text-xs font-medium capitalize">
                                    {{ $mode }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Card -->
            @if ($tutor->city)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Location</h3>
                    <p class="text-gray-700 font-medium">{{ $tutor->city }}, {{ $tutor->state ?? '' }}</p>
                    <p class="text-gray-600 text-sm">{{ $tutor->country ?? '' }}</p>
                </div>
            @endif

            <!-- Availability Card -->
            @if ($tutor->availability)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Availability</h3>
                    <p class="text-gray-700 whitespace-pre-wrap text-sm">{{ $tutor->availability }}</p>
                </div>
            @endif

            <!-- Contact & Action Card -->
            @php 
                $noContact = $tutor->settings['no_contact'] ?? false;
                $isOwnProfile = Auth::check() && Auth::id() === $tutor->user_id;
                $isStudent = Auth::check() && Auth::user()->hasRole('student');
                $studentCoins = Auth::check() ? Auth::user()->coins ?? 0 : 0;
            @endphp
            
            @if ($isOwnProfile)
                <!-- Show contact info for own profile -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-bold text-blue-900 mb-4"><i class="fas fa-envelope mr-2"></i>Get in Touch</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-gray-600 text-xs uppercase tracking-wide">Email</p>
                            <p class="text-blue-900 font-medium">{{ $tutor->user->email }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-xs uppercase tracking-wide">Phone</p>
                            <div class="flex items-center">
                                <p class="text-blue-900 font-medium">{{ $tutor->user->phone ?? 'Not provided' }}</p>
                                @if ($tutor->user->phone_verified)
                                    <span class="ml-2 text-green-600 text-sm"><i class="fas fa-check-circle"></i></span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @elseif ($isStudent)
                <!-- Student viewing - Show action buttons -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-bold text-blue-900 mb-4"><i class="fas fa-star mr-2"></i>Connect with Tutor</h3>
                    
                    <!-- Coins Balance -->
                    <div class="mb-4 p-3 bg-white rounded border border-blue-200">
                        <p class="text-xs text-gray-600 uppercase tracking-wide">Your Balance</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $studentCoins }} <span class="text-sm">coins</span></p>
                    </div>
                    
                    <div class="space-y-2">
                        <!-- Message Button -->
                        <button onclick="alert('Message feature coming soon')" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium flex items-center justify-center gap-2 transition">
                            <i class="fas fa-envelope"></i> Message
                        </button>

                        <!-- Phone Button (costs coins) -->
                        <button onclick="if({{ $studentCoins }} > 0) { alert('Reveal phone feature'); } else { alert('Insufficient coins'); }" 
                                class="w-full px-4 py-2 {{ $studentCoins > 0 ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed' }} text-white rounded-lg font-medium flex items-center justify-center gap-2 transition">
                            <i class="fas fa-phone"></i> Reveal Phone
                            <span class="text-xs bg-black bg-opacity-20 px-2 py-1 rounded">5 coins</span>
                        </button>

                        <!-- Pay Button (for booking/session) -->
                        <button onclick="alert('Payment feature coming soon')" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium flex items-center justify-center gap-2 transition">
                            <i class="fas fa-credit-card"></i> Book Session
                        </button>

                        <!-- Review Button -->
                        <button onclick="alert('Review feature coming soon')" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium flex items-center justify-center gap-2 transition">
                            <i class="fas fa-star"></i> Write Review
                        </button>
                    </div>
                </div>
            @else
                <!-- Guest viewing - show the same action buttons (read-only/demo) -->
                @php $studentCoins = 0; @endphp
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-bold text-blue-900 mb-4"><i class="fas fa-star mr-2"></i>Connect with Tutor</h3>

                    <div class="mb-4 p-3 bg-white rounded border border-blue-200">
                        <p class="text-xs text-gray-600 uppercase tracking-wide">Your Balance</p>
                        <p class="text-2xl font-bold text-blue-600">0 <span class="text-sm">coins</span></p>
                    </div>

                    <div class="space-y-2">
                        <button onclick="alert('Message feature coming soon')" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium flex items-center justify-center gap-2 transition">
                            <i class="fas fa-envelope"></i> Message
                        </button>

                        <button onclick="alert('Reveal phone feature — create an account to get coins')" 
                                class="w-full px-4 py-2 bg-gray-400 cursor-not-allowed text-white rounded-lg font-medium flex items-center justify-center gap-2 transition">
                            <i class="fas fa-phone"></i> Reveal Phone
                            <span class="text-xs bg-black bg-opacity-20 px-2 py-1 rounded">5 coins</span>
                        </button>

                        <button onclick="alert('Payment feature coming soon')" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium flex items-center justify-center gap-2 transition">
                            <i class="fas fa-credit-card"></i> Book Session
                        </button>

                        <button onclick="alert('Review feature coming soon')" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium flex items-center justify-center gap-2 transition">
                            <i class="fas fa-star"></i> Write Review
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Edit Button for Own Profile -->
    @if (Auth::check() && Auth::id() === $tutor->user_id)
        <div class="mt-8 text-center">
            <a href="{{ route('tutor.profile.dashboard') }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium inline-block">
                <i class="fas fa-edit mr-2"></i> Edit Profile
            </a>
        </div>
    @endif
</div>
@endsection
