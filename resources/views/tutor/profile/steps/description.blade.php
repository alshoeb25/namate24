@extends('layouts.app')

@section('title', 'Profile Description')

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
            <h1 class="text-3xl font-bold mb-2 text-gray-800">Profile Description</h1>
            <p class="text-gray-600 mb-8">Create a compelling profile to attract students</p>

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

            <form action="{{ route('tutor.profile.update-description') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Headline -->
                <div>
                    <label for="headline" class="block text-sm font-medium text-gray-700 mb-2">Headline (Professional Title)</label>
                    <input type="text" id="headline" name="headline" 
                           value="{{ old('headline', $tutor->headline ?? '') }}"
                           maxlength="255"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                           placeholder="e.g., Experienced Mathematics Tutor | Grade 10-12 Specialist"
                           required>
                    <p class="text-xs text-gray-500 mt-1"><span id="headlineCount">0</span>/255 characters</p>
                </div>

                <!-- About/Bio -->
                <div>
                    <label for="about" class="block text-sm font-medium text-gray-700 mb-2">About You</label>
                    <textarea id="about" name="about" rows="6"
                              minlength="50"
                              maxlength="2000"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                              placeholder="Tell students about yourself, your background, and why you're passionate about tutoring. Minimum 50 characters."
                              required>{{ old('about', $tutor->about ?? '') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1"><span id="aboutCount">0</span>/2000 characters (min 50)</p>
                </div>

                <!-- Teaching Methodology -->
                <div>
                    <label for="teaching_methodology" class="block text-sm font-medium text-gray-700 mb-2">Teaching Methodology</label>
                    <textarea id="teaching_methodology" name="teaching_methodology" rows="5"
                              maxlength="1000"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                              placeholder="Describe your teaching approach, methods, and what makes your tutoring unique..."
                              required>{{ old('teaching_methodology', $tutor->teaching_methodology ?? '') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1"><span id="methodologyCount">0</span>/1000 characters</p>
                </div>

                <!-- Preview Card -->
                <div class="bg-teal-50 border border-teal-200 rounded-lg p-6">
                    <h4 class="font-medium text-teal-900 mb-4">Profile Preview</h4>
                    <div class="bg-white border border-teal-100 rounded p-4">
                        <h3 class="text-xl font-bold text-gray-800" id="previewHeadline">Your Headline</h3>
                        <p class="text-gray-600 text-sm mt-2" id="previewAbout">Your about section will appear here...</p>
                        <p class="text-gray-600 text-sm mt-2" id="previewMethodology">Your teaching methodology will appear here...</p>
                    </div>
                </div>

                <!-- Tips Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-medium text-blue-900 mb-2">Tips for a Great Profile</h4>
                    <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                        <li>Be specific about your expertise and experience</li>
                        <li>Highlight your achievements and successes with students</li>
                        <li>Mention your unique teaching style</li>
                        <li>Be authentic and friendly in your tone</li>
                        <li>Proofread carefully for grammar and spelling</li>
                    </ul>
                </div>

                <!-- Do Not Share Contact Checkbox -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="do_not_share_contact" name="do_not_share_contact" value="1" class="form-checkbox h-5 w-5 text-yellow-600" {{ old('do_not_share_contact', $tutor->settings['no_contact'] ?? false) ? 'checked' : '' }}>
                        <span class="ml-3 text-sm text-yellow-900">Do not share any contact details in my profile (we will remove emails and phone numbers from your description)</span>
                    </label>
                    <p class="text-xs text-yellow-700 mt-2">Check this box if you don't want your phone number or email to appear anywhere in your public profile. We will sanitize your text to remove contact-like strings.</p>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
                        <i class="fas fa-save mr-2"></i> Save Description
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
// Character counters
document.getElementById('headline').addEventListener('input', function() {
    document.getElementById('headlineCount').textContent = this.value.length;
    updatePreview();
});

document.getElementById('about').addEventListener('input', function() {
    document.getElementById('aboutCount').textContent = this.value.length;
    updatePreview();
});

document.getElementById('teaching_methodology').addEventListener('input', function() {
    document.getElementById('methodologyCount').textContent = this.value.length;
    updatePreview();
});

function updatePreview() {
    document.getElementById('previewHeadline').textContent = document.getElementById('headline').value || 'Your Headline';
    document.getElementById('previewAbout').textContent = document.getElementById('about').value || 'Your about section will appear here...';
    document.getElementById('previewMethodology').textContent = document.getElementById('teaching_methodology').value || 'Your teaching methodology will appear here...';
}

// Initialize counters on page load
window.addEventListener('DOMContentLoaded', function() {
    document.getElementById('headlineCount').textContent = document.getElementById('headline').value.length;
    document.getElementById('aboutCount').textContent = document.getElementById('about').value.length;
    document.getElementById('methodologyCount').textContent = document.getElementById('teaching_methodology').value.length;
    updatePreview();
});
</script>
@endsection
