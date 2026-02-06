<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Introduction Video</h2>
    
    <!-- Approval Status Alerts -->
    <div v-if="videoData.video_approval_status === 'pending'" class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
      <p class="text-sm font-medium text-yellow-800">
        <i class="fas fa-clock mr-2"></i>⏱️ Video Pending Approval
      </p>
      <p class="text-xs text-yellow-700 mt-1">Your video is under review by our admin team.</p>
    </div>

    <div v-if="videoData.video_approval_status === 'approved'" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
      <p class="text-sm font-medium text-green-800">
        <i class="fas fa-check-circle mr-2"></i>✓ Video Approved
      </p>
      <p class="text-xs text-green-700 mt-1">Your video is now visible on your public profile.</p>
    </div>

    <div v-if="videoData.video_approval_status === 'rejected'" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-sm font-medium text-red-800">
        <i class="fas fa-times-circle mr-2"></i>✗ Video Rejected
      </p>
      <p class="text-xs text-red-700 mt-2"><strong>Reason:</strong> {{ videoData.video_rejection_reason || 'No reason provided' }}</p>
    </div>

    <div class="space-y-4">
      <!-- Current Video Link -->
      <div v-if="currentVideoUrl || videoData.youtube_intro_url" class="mb-6 bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between mb-4">
          <div>
            <p class="text-sm font-medium text-gray-700">
              <i class="fas fa-video mr-2"></i>Your {{ videoData.youtube_intro_url ? 'YouTube' : 'Uploaded' }} Video
            </p>
            <span v-if="videoData.video_approval_status" 
                  :class="{
                    'bg-green-500': videoData.video_approval_status === 'approved',
                    'bg-yellow-500': videoData.video_approval_status === 'pending',
                    'bg-red-500': videoData.video_approval_status === 'rejected'
                  }"
                  class="inline-block px-2 py-1 rounded-full text-white text-xs font-bold mt-2">
              {{ videoData.video_approval_status ? videoData.video_approval_status.toUpperCase() : 'NOT SUBMITTED' }}
            </span>
          </div>
          <button 
            @click="confirmDelete"
            class="text-sm px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 font-medium transition"
          >
            <i class="fas fa-trash mr-1"></i> Delete
          </button>
        </div>
        
        <div class="mt-3">
          <a 
            :href="currentVideoUrl || videoData.youtube_intro_url" 
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium"
          >
            <i class="fas fa-external-link-alt mr-2"></i>
            Open Video in New Tab
          </a>
        </div>

        <p v-if="videoData.video_title" class="text-sm text-gray-600 mt-3">
          <strong>Title:</strong> {{ videoData.video_title }}
        </p>
      </div>

      <!-- Upload Form -->
      <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
        <h3 class="text-lg font-semibold mb-4">Upload New Video</h3>
        
        <!-- Video Title -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Video Title (Optional)</label>
          <input 
            v-model="videoTitle"
            type="text" 
            placeholder="e.g., Introduction to Math Teaching"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
          />
        </div>

        <!-- File Upload or YouTube URL -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Upload Video File</label>
          <input 
            @change="handleFileChange" 
            type="file" 
            accept="video/*"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg"
          />
          <p class="text-xs text-gray-500 mt-1">Max 100MB. Formats: MP4, AVI, MOV, WMV</p>
        </div>

        <div class="mb-4 text-center text-sm text-gray-500 font-medium">OR</div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">YouTube URL</label>
          <input 
            v-model="youtubeUrl"
            type="url" 
            placeholder="https://youtube.com/watch?v=..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
          />
          <p class="text-xs text-gray-500 mt-1">Paste your YouTube video link</p>
        </div>

        <button 
          @click="uploadVideo" 
          class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium"
          :disabled="(!selectedFile && !youtubeUrl) || loading"
          :class="{ 'opacity-50 cursor-not-allowed': (!selectedFile && !youtubeUrl) || loading }"
        >
          <i class="fas fa-upload mr-2"></i>
          {{ loading ? 'Uploading...' : 'Submit for Approval' }}
        </button>
      </div>

      <!-- Messages -->
      <div v-if="message" class="p-4 bg-green-100 text-green-700 rounded-lg border border-green-300">
        <i class="fas fa-check-circle mr-2"></i>{{ message }}
      </div>
      <div v-if="error" class="p-4 bg-red-100 text-red-700 rounded-lg border border-red-300">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ error }}
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Video',
  data() {
    return {
      selectedFile: null,
      youtubeUrl: '',
      videoTitle: '',
      currentVideoUrl: '',
      videoData: {
        introductory_video: null,
        youtube_intro_url: null,
        video_title: null,
        video_approval_status: null,
        video_rejection_reason: null,
        video_url: null,
      },
      loading: false,
      message: '',
      error: '',
    };
  },
  mounted() {
    this.fetchVideo();
  },
  methods: {
    handleFileChange(event) {
      this.selectedFile = event.target.files[0];
      this.youtubeUrl = ''; // Clear YouTube URL if file is selected
    },
    async fetchVideo() {
      try {
        const response = await axios.get('/api/tutor/profile/video');
        this.videoData = response.data;
        this.currentVideoUrl = response.data.video_url;
        this.videoTitle = response.data.video_title || '';
      } catch (err) {
        console.error('Failed to load video', err);
      }
    },
    async uploadVideo() {
      if (!this.selectedFile && !this.youtubeUrl) return;

      try {
        this.loading = true;
        this.error = '';
        this.message = '';
        
        const formData = new FormData();
        
        if (this.selectedFile) {
          formData.append('video', this.selectedFile);
        }
        
        if (this.youtubeUrl) {
          formData.append('youtube_url', this.youtubeUrl);
        }
        
        if (this.videoTitle) {
          formData.append('video_title', this.videoTitle);
        }
        
        const response = await axios.post('/api/tutor/profile/video', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        
        this.message = response.data?.message || 'Data saved successfully.';
        this.currentVideoUrl = response.data.video_url;
        this.videoData.video_approval_status = response.data.video_approval_status;
        this.videoData.youtube_intro_url = response.data.youtube_intro_url;
        this.videoData.video_title = response.data.video_title;
        
        // Clear form
        this.selectedFile = null;
        this.youtubeUrl = '';
        
        // Refresh to get complete data
        await this.fetchVideo();
        
        setTimeout(() => this.message = '', 5000);
      } catch (err) {
        this.error = err.response?.data?.message || err.response?.data?.errors?.video?.[0] || err.response?.data?.errors?.youtube_url?.[0] || 'Failed to upload video';
        setTimeout(() => this.error = '', 5000);
      } finally {
        this.loading = false;
      }
    },
    confirmDelete() {
      if (confirm('Are you sure you want to delete this video? This action cannot be undone.')) {
        this.deleteVideo();
      }
    },
    async deleteVideo() {
      try {
        this.loading = true;
        this.error = '';
        this.message = '';
        
        const response = await axios.delete('/api/tutor/profile/video');
        
        this.message = response.data?.message || 'Data saved successfully.';
        this.currentVideoUrl = '';
        this.videoData = {
          introductory_video: null,
          youtube_intro_url: null,
          video_title: null,
          video_approval_status: null,
          video_rejection_reason: null,
          video_url: null,
        };
        
        setTimeout(() => this.message = '', 3000);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to delete video';
        setTimeout(() => this.error = '', 5000);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
