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

        <!-- Languages -->
        <div>
          <label class="block text-gray-700 mb-2">Languages you can communicate in</label>
          <div class="relative">
            <input
              v-model="languageSearch"
              type="text"
              class="w-full border px-3 py-2 rounded-sm"
              placeholder="Type to search languages"
              @focus="languageDropdown = true"
              @blur="closeLanguageDropdown"
            />
            <div v-if="languageDropdown" class="absolute z-10 mt-1 w-full bg-white border rounded shadow max-h-56 overflow-y-auto">
              <div
                v-for="lang in filteredLanguages"
                :key="lang"
                class="px-3 py-2 hover:bg-blue-50 flex items-center gap-2 cursor-pointer"
                @mousedown.prevent="toggleLanguage(lang)"
              >
                <input type="checkbox" class="rounded" :checked="form.languages.includes(lang)" />
                <span>{{ lang }}</span>
              </div>
              <div v-if="filteredLanguages.length === 0 && languageSearch" class="px-3 py-2 text-sm text-gray-500">No results</div>
              <div v-if="filteredLanguages.length === 0 && !languageSearch" class="px-3 py-2 text-sm text-gray-500">Type to search languages</div>
            </div>
            <div class="mt-2 flex flex-wrap gap-2">
              <span
                v-for="lang in form.languages"
                :key="lang"
                class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm flex items-center gap-1"
              >
                {{ lang }}
                <button type="button" class="text-blue-700" @click="toggleLanguage(lang)">×</button>
              </span>
            </div>
          </div>
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
        languages: [],
      },
      languageSearch: '',
      languageDropdown: false,
      allLanguages: [
        // Indian Languages
        'Hindi', 'English', 'Tamil', 'Telugu', 'Kannada', 'Malayalam', 'Marathi', 'Bengali', 'Gujarati', 'Punjabi', 'Urdu', 'Assamese', 'Odia', 'Konkani', 'Manipuri', 'Nepali', 'Sindhi', 'Sanskrit',
        // Foreign Languages
        'Spanish', 'French', 'German', 'Chinese (Simplified)', 'Chinese (Traditional)', 'Japanese', 'Korean', 'Portuguese', 'Italian', 'Russian', 'Arabic', 'Dutch', 'Polish', 'Swedish', 'Norwegian', 'Danish', 'Finnish', 'Thai', 'Vietnamese', 'Indonesian', 'Filipino (Tagalog)', 'Turkish', 'Persian', 'Hebrew', 'Greek', 'Czech', 'Hungarian', 'Romanian', 'Bulgarian', 'Serbian', 'Ukrainian', 'Afrikaans'
      ],
    };
  },
  computed: {
    filteredLanguages() {
      if (!this.languageSearch) {
        // Show all languages not already selected
        return this.allLanguages.filter(lang => !this.form.languages.includes(lang));
      }
      const search = this.languageSearch.toLowerCase();
      return this.allLanguages.filter(lang => 
        lang.toLowerCase().includes(search) && 
        !this.form.languages.includes(lang)
      );
    }
  },
  mounted() {
    this.fetchPersonalDetails();
  },
  methods: {
    closeLanguageDropdown() {
      setTimeout(() => {
        this.languageDropdown = false;
      }, 100);
    },
    toggleLanguage(lang) {
      const index = this.form.languages.indexOf(lang);
      if (index > -1) {
        this.form.languages.splice(index, 1);
      } else {
        this.form.languages.push(lang);
      }
    },
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
          languages: Array.isArray(response.data.tutor.languages) ? response.data.tutor.languages : [],
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
