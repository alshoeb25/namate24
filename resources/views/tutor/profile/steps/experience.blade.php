@extends('layouts.app')

@section('title', 'Experience')

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
                    <h1 class="text-3xl font-bold text-gray-800">Experience</h1>
                    <p class="text-gray-600">Add and manage your professional experience</p>
                </div>
                <button type="button" onclick="toggleAddForm()" class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700">
                    <i class="fas fa-plus mr-2"></i> Add Experience
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

            <!-- Add/Edit Experience Form -->
            <div id="experienceForm" class="hidden bg-cyan-50 border border-cyan-200 rounded-lg p-6 mb-8">
                <h3 class="text-lg font-bold mb-4 text-gray-800">Experience Entry</h3>
                <form action="{{ route('tutor.profile.store-experience') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Job Title / Designation</label>
                            <input type="text" id="title" name="title" 
                                   value="{{ old('title') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                                   placeholder="e.g., Senior Math Instructor" required>
                        </div>

                        <div>
                            <label for="company" class="block text-sm font-medium text-gray-700 mb-2">Organization / Institute (with city)</label>
                            <input type="text" id="company" name="company" 
                                   value="{{ old('company') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                                   placeholder="e.g., Aligarh Public School, Aligarh" required>
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <input type="text" id="city" name="city" value="{{ old('city') }}" placeholder="City"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="designation" class="block text-sm font-medium text-gray-700 mb-2">Designation (optional)</label>
                            <input type="text" id="designation" name="designation" value="{{ old('designation') }}" placeholder="e.g., Lecturer"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input type="date" id="start_date" name="start_date" 
                                   value="{{ old('start_date') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                                   required>
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <input type="date" id="end_date" name="end_date" 
                                   value="{{ old('end_date') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                        </div>

                    <div class="flex items-center gap-6">
                        <div class="flex items-center">
                            <input type="checkbox" id="currently_working" name="currently_working" value="1"
                                   class="w-4 h-4 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500"
                                   onchange="toggleEndDate()">
                            <label for="currently_working" class="ml-2 block text-sm text-gray-700">I currently work here</label>
                        </div>

                        <div>
                            <label for="association" class="block text-sm font-medium text-gray-700 mb-2">Association</label>
                            <select id="association" name="association" class="px-3 py-2 border border-gray-300 rounded">
                                <option value="">Please select</option>
                                <option value="Full Time">Full Time</option>
                                <option value="Part Time">Part Time</option>
                                <option value="Contract">Contract</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="roles" class="block text-sm font-medium text-gray-700 mb-2">Roles & Responsibilities</label>
                        <textarea id="roles" name="roles" rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                                  placeholder="Describe your roles and responsibilities at this job...">{{ old('roles') }}</textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="px-6 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 font-medium">
                            <i class="fas fa-save mr-2"></i> Add Entry
                        </button>
                        <button type="button" onclick="toggleAddForm()" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Experience List -->
            @if (count($experiences) > 0)
                <div class="space-y-4">
                    @foreach ($experiences as $index => $experience)
                        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                            <h4 class="text-lg font-bold text-gray-800">{{ $experience['title'] }} @if(!empty($experience['designation'])) <span class="text-sm font-medium text-gray-600">({{ $experience['designation'] }})</span> @endif</h4>
                                            <p class="text-gray-600">{{ $experience['company'] }}@if(!empty($experience['city'])), {{ $experience['city'] }}@endif</p>
                                            <p class="text-sm text-gray-500">
                                                @if (!empty($experience['start_date']))
                                                    {{ date('M Y', strtotime($experience['start_date'])) }} - @if(!empty($experience['end_date'])) {{ date('M Y', strtotime($experience['end_date'])) }} @else Present @endif
                                                @endif
                                            </p>
                                            @if(!empty($experience['association']))
                                                <p class="text-sm text-gray-500 mt-1"><strong>Association:</strong> {{ $experience['association'] }}</p>
                                            @endif
                                            @if(!empty($experience['roles']))
                                                <p class="text-gray-700 mt-2">{{ $experience['roles'] }}</p>
                                            @elseif(!empty($experience['description']))
                                                <p class="text-gray-700 mt-2">{{ $experience['description'] }}</p>
                                            @endif
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" onclick="editExperience({{ $index }})" class="px-3 py-1 bg-cyan-600 text-white text-sm rounded hover:bg-cyan-700">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form action="{{ route('tutor.profile.delete-experience', $index) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Are you sure?')" class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @if ($experience['description'] ?? false)
                                <p class="text-gray-700">{{ $experience['description'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-gray-50 rounded-lg">
                    <i class="fas fa-briefcase text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600">No experience entries yet.</p>
                    <button type="button" onclick="toggleAddForm()" class="mt-4 px-6 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 font-medium">
                        <i class="fas fa-plus mr-2"></i> Add Your First Experience
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleAddForm() {
    const form = document.getElementById('experienceForm');
    form.classList.toggle('hidden');
}

function toggleEndDate() {
    const endDateInput = document.getElementById('end_date');
    const checkbox = document.getElementById('currently_working');
    if (checkbox.checked) {
        endDateInput.disabled = true;
        endDateInput.value = '';
    } else {
        endDateInput.disabled = false;
    }
}

function editExperience(index) {
    alert('Edit functionality - Index: ' + index);
}
</script>
@endsection
