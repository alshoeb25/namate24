<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Photo</h2>
    
    <div class="space-y-4">
      <div v-if="currentPhoto" class="mb-4">
        <img :src="currentPhoto" :alt="'Profile photo'" class="w-32 h-32 object-cover rounded-lg" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Upload Photo</label>
        <input 
          @change="handleFileChange" 
          type="file" 
          accept="image/*"
          class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"
        />
        <p class="text-xs text-gray-500 mt-1">Max 2MB. Formats: JPEG, PNG, GIF</p>
      </div>

      <button 
        @click="uploadPhoto" 
        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        :disabled="!selectedFile || loading"
      >
        {{ loading ? 'Uploading...' : 'Upload Photo' }}
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
  name: 'Photo',
  data() {
    return {
      selectedFile: null,
      currentPhoto: '',
      loading: false,
      message: '',
      error: '',
    };
  },
  mounted() {
    this.fetchPhoto();
  },
  methods: {
    handleFileChange(event) {
      this.selectedFile = event.target.files[0];
    },
    async fetchPhoto() {
      try {
        const response = await axios.get('/api/tutor/profile/photo');
        this.currentPhoto = response.data.photo;
      } catch (err) {
        console.error('Failed to load photo');
      }
    },
    async uploadPhoto() {
      if (!this.selectedFile) return;

      try {
        this.loading = true;
        this.error = '';
        const formData = new FormData();
        formData.append('photo', this.selectedFile);
        const response = await axios.post('/api/tutor/profile/photo', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        this.message = response.data?.message || 'Data saved successfully.';
        this.currentPhoto = response.data.photo_url;
        this.selectedFile = null;
        setTimeout(() => this.message = '', 3000);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to upload photo';
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
