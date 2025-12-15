<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Video</h2>
    
    <div class="space-y-4">
      <div v-if="currentVideo" class="mb-4">
        <video :src="currentVideo" class="w-full max-w-md rounded-lg" controls></video>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Upload Video</label>
        <input 
          @change="handleFileChange" 
          type="file" 
          accept="video/*"
          class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"
        />
        <p class="text-xs text-gray-500 mt-1">Max 50MB. Formats: MP4, AVI, MOV, WMV</p>
      </div>

      <button 
        @click="uploadVideo" 
        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        :disabled="!selectedFile || loading"
      >
        {{ loading ? 'Uploading...' : 'Upload Video' }}
      </button>

      <!-- Messages -->
      <div v-if="message" class="p-4 bg-green-100 text-green-700 rounded-lg">
        {{ message }}
      </div>
      <div v-if="error" class="p-4 bg-red-100 text-red-700 rounded-lg">
        {{ error }}
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
      currentVideo: '',
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
    },
    async fetchVideo() {
      try {
        const response = await axios.get('/api/tutor/profile/video');
        this.currentVideo = response.data.video;
      } catch (err) {
        console.error('Failed to load video');
      }
    },
    async uploadVideo() {
      if (!this.selectedFile) return;

      try {
        this.loading = true;
        this.error = '';
        const formData = new FormData();
        formData.append('video', this.selectedFile);
        const response = await axios.post('/api/tutor/profile/video', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        this.message = response.data.message;
        this.currentVideo = response.data.video_url;
        this.selectedFile = null;
        setTimeout(() => this.message = '', 3000);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to upload video';
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
