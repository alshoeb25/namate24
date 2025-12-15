@extends('layouts.app')

@section('title', 'Profile Photo')

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
            <h1 class="text-3xl font-bold mb-2 text-gray-800">Profile Photo</h1>
            <p class="text-gray-600 mb-8">Upload a professional profile picture (JPEG, PNG, GIF - Max 2MB)</p>

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

            <!-- Current Photo Display -->
            @if ($tutor->user->avatar)
                <div class="mb-8">
                    <p class="text-sm font-medium text-gray-700 mb-3">Current Photo</p>
                    <img src="{{ asset('storage/' . $tutor->user->avatar) }}" 
                         alt="{{ $tutor->user->name }}" 
                         class="w-40 h-40 rounded-full object-cover border-4 border-blue-200">
                </div>
            @endif

            <form action="{{ route('tutor.profile.update-photo') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Photo Upload -->
                <div>
                    <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Upload Photo</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition-colors">
                        <input type="file" id="photo" name="photo" 
                               class="hidden" 
                               accept="image/*"
                               onchange="previewImage(event)"
                               required>
                        <label for="photo" class="cursor-pointer">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-gray-600">Drag and drop your photo here, or <span class="text-blue-600 font-medium">click to select</span></p>
                                <p class="text-sm text-gray-500 mt-2">JPEG, PNG, GIF (Max 2MB)</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Image Preview -->
                <div id="previewContainer" class="hidden">
                    <p class="text-sm font-medium text-gray-700 mb-3">Preview</p>
                    <img id="imagePreview" src="" alt="Preview" class="w-40 h-40 rounded-full object-cover border-4 border-blue-200">
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                        <i class="fas fa-upload mr-2"></i> Upload Photo
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
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            preview.src = e.target.result;
            document.getElementById('previewContainer').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
