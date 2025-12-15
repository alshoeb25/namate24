<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Personal Details</h2>
    
    <form @submit.prevent="updatePersonalDetails">
      <div class="space-y-4">
        <!-- Headline -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Headline</label>
          <input 
            v-model="form.headline" 
            type="text" 
            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="e.g., Expert Math Tutor with 10 years experience"
          />
        </div>

        <!-- Current Role -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Current Role</label>
          <input 
            v-model="form.current_role" 
            type="text" 
            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="e.g., Professional Tutor"
          />
        </div>

        <!-- Speciality -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Speciality</label>
          <input 
            v-model="form.speciality" 
            type="text" 
            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="e.g., Mathematics, Physics"
          />
        </div>

        <!-- Gender -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Gender</label>
          <select 
            v-model="form.gender" 
            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
            <option value="Prefer not to say">Prefer not to say</option>
          </select>
        </div>

        <!-- Strength -->
        <div>
          <label class="block text-sm font-medium text-gray-700">My Strengths</label>
          <textarea 
            v-model="form.strength" 
            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            rows="3"
            placeholder="Describe your key strengths as a tutor..."
          ></textarea>
        </div>

        <!-- YouTube URL -->
        <div>
          <label class="block text-sm font-medium text-gray-700">YouTube Intro Link</label>
          <input 
            v-model="form.youtube_url" 
            type="url" 
            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="https://youtube.com/..."
          />
        </div>

        <!-- Submit Button -->
        <div class="flex gap-4 pt-4">
          <button 
            type="submit" 
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            :disabled="loading"
          >
            {{ loading ? 'Saving...' : 'Save Details' }}
          </button>
          <button 
            type="button" 
            @click="resetForm" 
            class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition"
          >
            Cancel
          </button>
        </div>

        <!-- Success/Error Messages -->
        <div v-if="message" class="mt-4 p-4 bg-green-100 text-green-700 rounded-lg">
          {{ message }}
        </div>
        <div v-if="error" class="mt-4 p-4 bg-red-100 text-red-700 rounded-lg">
          {{ error }}
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'PersonalDetails',
  data() {
    return {
      form: {
        headline: '',
        current_role: '',
        speciality: '',
        gender: '',
        strength: '',
        youtube_url: '',
      },
      loading: false,
      message: '',
      error: '',
    };
  },
  mounted() {
    this.fetchPersonalDetails();
  },
  methods: {
    async fetchPersonalDetails() {
      try {
        console.log('Fetching personal details...');
        this.loading = true;
        const response = await axios.get('/api/tutor/profile/personal-details');
        console.log('Response:', response.data);
        this.form = {
          headline: response.data.tutor.headline || '',
          current_role: response.data.tutor.current_role || '',
          speciality: response.data.tutor.speciality || '',
          gender: response.data.tutor.gender || '',
          strength: response.data.tutor.strength || '',
          youtube_url: response.data.tutor.youtube_url || '',
        };
      } catch (err) {
        console.error('Error fetching personal details:', err);
        this.error = 'Failed to load personal details';
      } finally {
        this.loading = false;
      }
    },
    async updatePersonalDetails() {
      try {
        this.loading = true;
        this.error = '';
        this.message = '';
        const response = await axios.post('/api/tutor/profile/personal-details', this.form);
        this.message = response.data.message;
        setTimeout(() => this.message = '', 3000);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to update details';
      } finally {
        this.loading = false;
      }
    },
    resetForm() {
      this.fetchPersonalDetails();
      this.message = '';
      this.error = '';
    },
  },
};
</script>
