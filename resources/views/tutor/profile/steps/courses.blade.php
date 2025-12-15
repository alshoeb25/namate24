@extends('layouts.app')

@section('title', 'Courses')

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
                    <h1 class="text-3xl font-bold text-gray-800">Courses</h1>
                    <p class="text-gray-600">Create and manage your courses</p>
                </div>
                <button type="button" onclick="toggleAddForm()" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                    <i class="fas fa-plus mr-2"></i> Create Course
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

            <!-- Add/Edit Course Form -->
            <div id="courseForm" class="hidden bg-orange-50 border border-orange-200 rounded-lg p-6 mb-8">
                <h3 class="text-lg font-bold mb-4 text-gray-800">Course Details</h3>
                <form action="{{ route('tutor.profile.store-course') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Course Title</label>
                        <input type="text" id="title" name="title" 
                               value="{{ old('title') }}"
                               maxlength="255"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                               placeholder="e.g., Algebra Mastery for Beginners" required>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Course Description</label>
                        <textarea id="description" name="description" rows="4"
                                  maxlength="1000"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                  placeholder="Describe what students will learn..."
                                  required>{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">Duration</label>
                            <input type="number" id="duration" name="duration" 
                                   value="{{ old('duration') }}"
                                   min="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                   placeholder="e.g., 8" required>
                        </div>

                        <div>
                            <label for="duration_unit" class="block text-sm font-medium text-gray-700 mb-2">Duration Unit</label>
                            <select id="duration_unit" name="duration_unit" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <option value="">Select</option>
                                <option value="Hours">Hour(s)</option>
                                <option value="Days">Day(s)</option>
                                <option value="Weeks">Week(s)</option>
                                <option value="Months">Month(s)</option>
                                <option value="Years">Year(s)</option>
                            </select>
                        </div>

                        <div>
                            <label for="level" class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                            <select id="level" name="level" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                    required>
                                <option value="">-- Select Level --</option>
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Course Price</label>
                            <input type="number" id="price" name="price" step="0.01"
                                   value="{{ old('price') }}"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                   placeholder="Enter course price" required>
                            <p class="text-xs text-gray-500 mt-1">Setting price 0 will make this course free.</p>
                        </div>

                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                            <select id="currency" name="currency" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" required>
                                <option value="">Select</option>
                                <option value="USD">USD ($)</option>
                                <option value="INR">INR (₹)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="mode_of_delivery" class="block text-sm font-medium text-gray-700 mb-2">Mode of Delivery</label>
                            <select id="mode_of_delivery" name="mode_of_delivery" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <option value="">Please select</option>
                                <option value="Online">Online</option>
                                <option value="At my institute">At my institute</option>
                                <option value="At student's home">At student's home</option>
                                <option value="Flexible as per the student">Flexible as per the student</option>
                            </select>
                        </div>

                        <div>
                            <label for="group_size" class="block text-sm font-medium text-gray-700 mb-2">Group Size</label>
                            <select id="group_size" name="group_size" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <option value="">Please select</option>
                                <option value="1 - Individual">1 - Individual</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6 - 10">6 - 10</option>
                                <option value="11 - 20">11 - 20</option>
                                <option value="21 - 40">21 - 40</option>
                                <option value="41 or more">41 or more</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="certificate_provided" class="block text-sm font-medium text-gray-700 mb-2">Certificate Provided?</label>
                            <select id="certificate_provided" name="certificate_provided" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <option value="">Please select</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>

                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700 mb-2">Language of Instructions</label>
                            <input type="text" id="language" name="language" value="{{ old('language') }}" placeholder="e.g., English, Hindi" maxlength="100"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-medium">
                            <i class="fas fa-save mr-2"></i> Create Course
                        </button>
                        <button type="button" onclick="toggleAddForm()" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Courses List -->
            @if (count($courses) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($courses as $index => $course)
                        <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4 text-white">
                                <h4 class="text-lg font-bold">{{ $course['title'] }}</h4>
                            </div>
                            <div class="p-6">
                                <p class="text-gray-700 mb-4">{{ $course['description'] }}</p>
                                
                                <div class="space-y-2 mb-4 text-sm text-gray-600">
                                    <p><strong>Duration:</strong> {{ $course['duration'] }}@if(!empty($course['duration_unit'])) {{ $course['duration_unit'] }}@endif</p>
                                    <p><strong>Level:</strong> <span class="capitalize">{{ $course['level'] }}</span></p>
                                    <p><strong>Price:</strong> @if(!empty($course['currency']))@if($course['currency']=='USD')$ @else ₹ @endif@endif{{ number_format($course['price'], 2) }}@if(!empty($course['currency'])) {{ $course['currency'] }}@endif</p>
                                    @if(!empty($course['mode_of_delivery']))
                                        <p><strong>Mode:</strong> {{ $course['mode_of_delivery'] }}</p>
                                    @endif
                                    @if(!empty($course['group_size']))
                                        <p><strong>Group Size:</strong> {{ $course['group_size'] }}</p>
                                    @endif
                                    @if(!empty($course['certificate_provided']))
                                        <p><strong>Certificate:</strong> {{ $course['certificate_provided'] }}</p>
                                    @endif
                                    @if(!empty($course['language']))
                                        <p><strong>Language:</strong> {{ $course['language'] }}</p>
                                    @endif
                                </div>

                                <div class="flex gap-2 pt-4 border-t">
                                    <button type="button" onclick="editCourse({{ $index }})" class="flex-1 px-3 py-2 bg-orange-600 text-white text-sm rounded hover:bg-orange-700">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </button>
                                    <form action="{{ route('tutor.profile.delete-course', $index) }}" method="POST" style="display: inline; flex: 1;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Are you sure?')" class="w-full px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                                            <i class="fas fa-trash mr-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-gray-50 rounded-lg">
                    <i class="fas fa-presentation text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 mb-4">No courses yet. Create one to get started!</p>
                    <button type="button" onclick="toggleAddForm()" class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-medium">
                        <i class="fas fa-plus mr-2"></i> Create Your First Course
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleAddForm() {
    const form = document.getElementById('courseForm');
    form.classList.toggle('hidden');
}

function editCourse(index) {
    alert('Edit functionality - Index: ' + index);
}
</script>
@endsection
