@extends('layouts.app')

@section('title', 'Education')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('tutor.profile.dashboard') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Education</h1>
                    <p class="text-gray-600">Add and manage your educational background</p>
                </div>
                <button type="button" onclick="toggleAddForm()" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    <i class="fas fa-plus mr-2"></i> Add Education
                </button>
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

            <!-- Add/Edit Education Form -->
            <div id="educationForm" class="hidden bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                <h3 class="text-lg font-bold mb-4 text-gray-800">Education Entry</h3>
                <form action="{{ route('tutor.profile.store-education') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="degree" class="block text-sm font-medium text-gray-700 mb-2">Degree/Certification Name</label>
                            <input type="text" id="degree" name="degree" 
                                   value="{{ old('degree') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                   placeholder="e.g., MCA" required>
                        </div>

                        <div>
                            <label for="degree_type" class="block text-sm font-medium text-gray-700 mb-2">Degree Type</label>
                            <select id="degree_type" name="degree_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                <option value="">-- Please select --</option>
                                <option value="Secondary">Secondary</option>
                                <option value="Higher Secondary">Higher Secondary</option>
                                <option value="Diploma">Diploma</option>
                                <option value="Graduation">Graduation</option>
                                <option value="Advanced Diploma">Advanced Diploma</option>
                                <option value="Post Graduation">Post Graduation</option>
                                <option value="Doctorate/PhD">Doctorate/PhD</option>
                                <option value="Certification">Certification</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="institution" class="block text-sm font-medium text-gray-700 mb-2">Institution</label>
                            <input type="text" id="institution" name="institution" 
                                   value="{{ old('institution') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                   placeholder="e.g., Aligarh Muslim University" required>
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <input type="text" id="city" name="city" value="{{ old('city') }}" placeholder="e.g., Aligarh"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="field_of_study" class="block text-sm font-medium text-gray-700 mb-2">Degree Major / Field of Study</label>
                            <input type="text" id="field_of_study" name="field_of_study" 
                                   value="{{ old('field_of_study') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                   placeholder="e.g., Computer Science" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <div class="flex gap-2">
                                <select name="start_month" class="w-1/2 px-3 py-2 border border-gray-300 rounded">
                                    <option value="">Month</option>
                                    @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="start_year" min="1950" max="{{ date('Y') }}" placeholder="Year" class="w-1/2 px-3 py-2 border border-gray-300 rounded" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Date (leave blank if ongoing)</label>
                            <div class="flex gap-2">
                                <select name="end_month" class="w-1/2 px-3 py-2 border border-gray-300 rounded">
                                    <option value="">Month</option>
                                    @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="end_year" min="1950" max="{{ date('Y') + 5 }}" placeholder="Year" class="w-1/2 px-3 py-2 border border-gray-300 rounded">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="study_mode" class="block text-sm font-medium text-gray-700 mb-2">Study Mode</label>
                            <select id="study_mode" name="study_mode" class="w-full px-3 py-2 border border-gray-300 rounded">
                                <option value="">Please select</option>
                                <option value="Full Time">Full Time</option>
                                <option value="Part Time">Part Time</option>
                                <option value="Correspondence / Distance Learning">Correspondence / Distance Learning</option>
                            </select>
                        </div>

                        <div>
                            <label for="speciality" class="block text-sm font-medium text-gray-700 mb-2">Speciality (optional)</label>
                            <input type="text" id="speciality" name="speciality" value="{{ old('speciality') }}" class="w-full px-3 py-2 border border-gray-300 rounded" placeholder="e.g., Software Accounting">
                        </div>

                        <div>
                            <label for="score" class="block text-sm font-medium text-gray-700 mb-2">Score / Grade (optional)</label>
                            <input type="text" id="score" name="score" value="{{ old('score') }}" class="w-full px-3 py-2 border border-gray-300 rounded" placeholder="e.g., 7.8 CGPA or 82%">
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                  placeholder="Add any relevant details about your education...">{{ old('description') }}</textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 font-medium">
                            <i class="fas fa-save mr-2"></i> Add Entry
                        </button>
                        <button type="button" onclick="toggleAddForm()" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Education List -->
            @if (count($educations) > 0)
                <div class="space-y-4">
                    @foreach ($educations as $index => $education)
                        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-800">{{ $education['degree'] }} @if(!empty($education['degree_type'])) <span class="text-sm font-medium text-gray-600">({{ $education['degree_type'] }})</span> @endif</h4>
                                    <p class="text-gray-600">{{ $education['institution'] }}@if(!empty($education['city'])), {{ $education['city'] }}@endif</p>
                                    <p class="text-sm text-gray-500">{{ $education['field_of_study'] }} • @if(!empty($education['start_month'])){{ $education['start_month'] . ' ' }}@endif{{ $education['start_year'] }} - @if(!empty($education['end_year']))@if(!empty($education['end_month'])){{ $education['end_month'] . ' ' }}@endif{{ $education['end_year'] }}@else Present @endif</p>
                                    @if(!empty($education['study_mode']) || !empty($education['speciality']) || !empty($education['score']))
                                        <p class="text-sm text-gray-500 mt-1">
                                            @if(!empty($education['study_mode']))<strong>Mode:</strong> {{ $education['study_mode'] }} @endif
                                            @if(!empty($education['speciality'])) • <strong>Speciality:</strong> {{ $education['speciality'] }} @endif
                                            @if(!empty($education['score'])) • <strong>Score:</strong> {{ $education['score'] }} @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" onclick="editEducation({{ $index }})" class="px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form action="{{ route('tutor.profile.delete-education', $index) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Are you sure?')" class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @if ($education['description'] ?? false)
                                <p class="text-gray-700">{{ $education['description'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-gray-50 rounded-lg">
                    <i class="fas fa-graduation-cap text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600">No education entries yet.</p>
                    <button type="button" onclick="toggleAddForm()" class="mt-4 px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 font-medium">
                        <i class="fas fa-plus mr-2"></i> Add Your First Education
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleAddForm() {
    const form = document.getElementById('educationForm');
    form.classList.toggle('hidden');
}

function editEducation(index) {
    // For now, show an alert. In a real app, you'd load the data into the form
    alert('Edit functionality - Index: ' + index);
}
</script>
@endsection
