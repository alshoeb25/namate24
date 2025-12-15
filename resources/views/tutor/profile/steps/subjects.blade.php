@extends('layouts.app')

@section('title', 'Subjects')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('tutor.profile.dashboard') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-8">
            <h1 class="text-3xl font-bold mb-2 text-gray-800">Subjects</h1>
            <p class="text-gray-600 mb-8">Select the subjects you can teach and specify your expertise level</p>

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

            <form action="{{ route('tutor.profile.update-subjects') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Add new subject (one at a time) -->
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-medium text-green-900 mb-2">Add a new subject</h3>
                    <p class="text-sm text-green-800 mb-3">Not seeing your subject? Add it here (one at a time). Example: "Software accounting"</p>
                    <div class="flex gap-2">
                        <input type="text" name="name" placeholder="Enter subject name" required
                               class="flex-1 px-3 py-2 border border-green-300 rounded-md focus:ring-green-500 focus:border-green-500">
                        <button formaction="{{ route('tutor.profile.subjects.add') }}" formmethod="post" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Add
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($allSubjects as $index => $subject)
                        <div class="border border-gray-200 rounded-lg p-6 hover:border-indigo-400 hover:shadow-md transition-all">
                            <div class="flex items-start">
                                <input type="checkbox" 
                                       id="subject_{{ $subject->id }}" 
                                       name="subject_select_{{ $subject->id }}"
                                       value="{{ $subject->id }}"
                                       {{ isset($selectedSubjects[$subject->id]) ? 'checked' : '' }}
                                       class="mt-1 w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                       onchange="toggleSubjectLevel({{ $subject->id }})">
                                <div class="ml-4 flex-1">
                                    <label for="subject_{{ $subject->id }}" class="font-medium text-gray-700 cursor-pointer">
                                        {{ $subject->name }}
                                    </label>
                                </div>
                            </div>

                            <!-- Level Selection -->
                            <div id="level_{{ $subject->id }}" class="mt-4 hidden">
                                <label class="block text-sm font-medium text-gray-600 mb-2">Your expertise level:</label>
                                <select name="subjects[{{ $index }}][level]" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Select Level --</option>
                                    <option value="beginner" {{ (isset($selectedSubjects[$subject->id]) && $selectedSubjects[$subject->id]['pivot']['level'] === 'beginner') ? 'selected' : '' }}>
                                        Beginner
                                    </option>
                                    <option value="intermediate" {{ (isset($selectedSubjects[$subject->id]) && $selectedSubjects[$subject->id]['pivot']['level'] === 'intermediate') ? 'selected' : '' }}>
                                        Intermediate
                                    </option>
                                    <option value="advanced" {{ (isset($selectedSubjects[$subject->id]) && $selectedSubjects[$subject->id]['pivot']['level'] === 'advanced') ? 'selected' : '' }}>
                                        Advanced
                                    </option>
                                </select>
                                <input type="hidden" name="subjects[{{ $index }}][id]" value="{{ $subject->id }}">
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Selected Subjects Summary -->
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6">
                    <h3 class="font-medium text-indigo-900 mb-3">Selected Subjects (<span id="selectedCount">0</span>)</h3>
                    <div id="selectedSubjectsList" class="space-y-2">
                        <!-- Dynamically populated -->
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                        <i class="fas fa-save mr-2"></i> Save Subjects
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
// Initialize subject levels based on selected subjects
function initializeSubjectLevels() {
    const selectedSubjects = {!! json_encode(array_keys($selectedSubjects)) !!};
    selectedSubjects.forEach(id => {
        const checkbox = document.getElementById(`subject_${id}`);
        const levelDiv = document.getElementById(`level_${id}`);
        if (checkbox && levelDiv) {
            levelDiv.classList.remove('hidden');
        }
    });
    updateSelectedCount();
}

function toggleSubjectLevel(subjectId) {
    const checkbox = document.getElementById(`subject_${subjectId}`);
    const levelDiv = document.getElementById(`level_${subjectId}`);
    
    if (checkbox.checked) {
        levelDiv.classList.remove('hidden');
    } else {
        levelDiv.classList.add('hidden');
    }
    
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('input[name^="subject_select_"]:checked');
    document.getElementById('selectedCount').textContent = checkboxes.length;
    
    const list = document.getElementById('selectedSubjectsList');
    list.innerHTML = '';
    
    checkboxes.forEach(checkbox => {
        const label = checkbox.nextElementSibling?.textContent || checkbox.value;
        const subjectId = checkbox.value;
        const levelSelect = document.querySelector(`select[name*="subjects["][name*="][level]"]`);
        const selectedLevel = document.querySelector(`select[name="subjects[\${getIndexForSubject(${subjectId})}][level]"]`)?.value || 'Not set';
        
        const item = document.createElement('div');
        item.className = 'text-sm text-indigo-900';
        item.textContent = `${label}`;
        list.appendChild(item);
    });
}

function getIndexForSubject(subjectId) {
    // Find the index of the subject in the subjects array
    const allSubjects = {!! json_encode($allSubjects->pluck('id')->toArray()) !!};
    return allSubjects.indexOf(parseInt(subjectId));
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initializeSubjectLevels);
</script>
@endsection
