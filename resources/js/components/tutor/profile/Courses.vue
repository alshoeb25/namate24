<template>
  <main class="max-w-3xl mx-auto mt-16 px-4 pb-20">

    <!-- Page Title -->
    <h1 class="text-2xl font-semibold text-gray-800 mb-8">
      Courses I teach
    </h1>

    <!-- Courses List -->
    <div v-if="courses.length > 0" class="mb-10 bg-gray-50 rounded-lg p-6">
      <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Your Courses ({{ courses.length }})</h3>
      <div class="space-y-4">
        <div 
          v-for="(course, index) in courses" 
          :key="index"
          class="bg-white border-2 border-gray-200 rounded-lg p-5 hover:shadow-md transition"
        >
          <div class="flex justify-between items-start gap-4">
            <div class="flex-1">
              <div class="flex items-start justify-between mb-2">
                <h4 class="font-bold text-lg text-gray-900">{{ course.title }}</h4>
                <span class="text-xl font-bold text-green-600">{{ course.currency }} {{ course.price }}</span>
              </div>
              <p class="text-gray-600 text-sm mb-3">{{ course.description }}</p>
              
              <!-- Course Details Grid -->
              <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-3">
                <div class="text-sm">
                  <span class="text-gray-500">Mode:</span>
                  <span class="ml-1 font-medium text-gray-700">{{ course.mode_of_delivery }}</span>
                </div>
                <div class="text-sm">
                  <span class="text-gray-500">Group Size:</span>
                  <span class="ml-1 font-medium text-gray-700">{{ course.group_size }}</span>
                </div>
                <div class="text-sm">
                  <span class="text-gray-500">Certificate:</span>
                  <span class="ml-1 font-medium text-gray-700">{{ course.certificate }}</span>
                </div>
                <div v-if="course.duration" class="text-sm">
                  <span class="text-gray-500">Duration:</span>
                  <span class="ml-1 font-medium text-gray-700">{{ course.duration }} {{ course.duration_unit }}</span>
                </div>
              </div>
              
              <!-- Languages -->
              <div v-if="(course.languages && course.languages.length > 0) || course.language" class="mb-2">
                <span class="text-xs text-gray-500 mr-2">Languages:</span>
                <template v-if="course.languages && course.languages.length > 0">
                  <span v-for="(lang, idx) in course.languages" :key="idx" class="badge badge-gray mr-1">{{ lang }}</span>
                </template>
                <span v-else-if="course.language" class="badge badge-gray">{{ course.language }}</span>
              </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col gap-2 ml-4">
              <button 
                @click="editCourse(index)"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium transition whitespace-nowrap"
              >
                ✏️ Edit
              </button>
              <button 
                @click="deleteCourse(index)"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium transition whitespace-nowrap"
              >
                🗑️ Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Empty State -->
    <div v-else class="mb-10 bg-blue-50 border-2 border-blue-200 rounded-lg p-6 text-center">
      <p class="text-gray-600 text-lg">📚 No courses added yet. Add your first course below!</p>
    </div>

    <!-- Success Message -->
    <div v-if="message" class="p-4 bg-green-100 text-green-700 rounded-lg mb-6">
      {{ message }}
    </div>

    <!-- Form -->
    <form @submit.prevent="saveCourse" class="space-y-6">

      <!-- Course Title -->
      <div>
        <label class="block text-gray-700 mb-1 text-sm font-medium">
          Course title
        </label>
        <input 
          v-model="form.title"
          type="text"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none"
          required
        />
      </div>

      <!-- Description -->
      <div>
        <label class="block text-gray-700 mb-1 text-sm font-medium">
          Description
        </label>
        <textarea 
          v-model="form.description"
          rows="4"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none"
        ></textarea>
      </div>

      <!-- Price -->
      <div>
        <label class="block text-gray-700 mb-1 text-sm font-medium">
          Price
        </label>
        <div class="grid grid-cols-2 gap-4">
          <input 
            v-model.number="form.price"
            type="number"
            step="0.01"
            placeholder="0"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none"
            required
          />
          <select
            v-model="form.currency"
            class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:ring-1 focus:ring-blue-500 focus:outline-none"
            required
          >
            <option value="">Select</option>
            <option value="USD">USD</option>
            <option value="INR">INR</option>
            <option value="EUR">EUR</option>
            <option value="GBP">GBP</option>
            <option value="AUD">AUD</option>
          </select>
        </div>
        <p class="text-xs text-gray-500 mt-1">
          Setting price 0 will make this course free.
        </p>
      </div>

      <!-- Mode of Delivery -->
      <div>
        <label class="block text-gray-700 mb-1 text-sm font-medium">
          Mode of delivery
        </label>
        <select
          v-model="form.mode_of_delivery"
          class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:ring-1 focus:ring-blue-500 focus:outline-none"
          required
        >
          <option value="">Please select</option>
          <option value="Online">Online</option>
          <option value="At my institute">At my institute</option>
          <option value="At student's home">At student's home</option>
          <option value="Flexible as per the student">Flexible as per the student</option>
        </select>
      </div>

      <!-- Group Size -->
      <div>
        <label class="block text-gray-700 mb-1 text-sm font-medium">
          Group size
        </label>
        <select
          v-model="form.group_size"
          class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:ring-1 focus:ring-blue-500 focus:outline-none"
          required
        >
          <option value="">Please select</option>
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5">5</option>
          <option value="6 - 10">6 - 10</option>
          <option value="10 - 15">10 - 15</option>
          <option value="20 - 35">20 - 35</option>
          <option value="40 or more">40 or more</option>
        </select>
      </div>

      <!-- Certificate -->
      <div>
        <label class="block text-gray-700 mb-1 text-sm font-medium">
          Certificate provided?
        </label>
        <select
          v-model="form.certificate"
          class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:ring-1 focus:ring-blue-500 focus:outline-none"
          required
        >
          <option value="">Please select</option>
          <option value="Yes">Yes</option>
          <option value="No">No</option>
        </select>
      </div>

      <!-- Language -->
      <div class="relative">
        <label class="block text-gray-700 mb-1 text-sm font-medium">
          Language of instructions (Select multiple)
        </label>
        
        <!-- Selected Languages Tags -->
        <div v-if="form.languages && form.languages.length > 0" class="flex flex-wrap gap-2 mb-2">
          <span
            v-for="(lang, index) in form.languages"
            :key="index"
            class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm"
          >
            {{ lang }}
            <button
              type="button"
              @click="removeLanguage(index)"
              class="text-blue-600 hover:text-blue-800 font-bold"
            >
              ×
            </button>
          </span>
        </div>
        
        <input
          v-model="languageSearch"
          @input="filterLanguages"
          @focus="showLanguageDropdown = true"
          @blur="hideLanguageDropdown"
          type="text"
          placeholder="Type to search and add languages..."
          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none"
        />
        <div
          v-if="showLanguageDropdown && filteredLanguages.length > 0"
          class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
        >
          <div
            v-for="lang in filteredLanguages"
            :key="lang"
            @mousedown.prevent="selectLanguage(lang)"
            class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm flex justify-between items-center"
          >
            <span>{{ lang }}</span>
            <span v-if="form.languages && form.languages.includes(lang)" class="text-green-600 text-xs">✓ Added</span>
          </div>
        </div>
      </div>

      <!-- Course Duration -->
      <div>
        <label class="block text-gray-700 mb-1 text-sm font-medium">
          Course duration
        </label>
        <div class="grid grid-cols-2 gap-4">
          <input
            v-model.number="form.duration"
            type="number"
            placeholder="e.g., 10"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none"
            required
          />
          <select
            v-model="form.duration_unit"
            class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:ring-1 focus:ring-blue-500 focus:outline-none"
            required
          >
            <option value="">Select</option>
            <option value="Hours">Hours</option>
            <option value="Days">Days</option>
            <option value="Weeks">Weeks</option>
            <option value="Months">Months</option>
            <option value="Years">Years</option>
          </select>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" ref="errorMessage" class="p-4 bg-red-100 text-red-700 rounded-lg">
        {{ error }}
      </div>

      <!-- Save Button -->
      <div class="pt-4">
        <button 
          type="submit" 
          class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
          :disabled="loading"
        >
          {{ loading ? 'Saving...' : (editingIndex !== null ? 'Update Course' : 'Save') }}
        </button>
        <button 
          v-if="editingIndex !== null"
          type="button" 
          @click="resetForm" 
          class="ml-3 px-6 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition"
        >
          Cancel Edit
        </button>
      </div>

    </form>

  </main>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Courses',
  data() {
    return {
      courses: [],
      form: {
        title: '',
        description: '',
        price: '',
        currency: '',
        mode_of_delivery: '',
        group_size: '',
        certificate: '',
        languages: [],
        duration: '',
        duration_unit: '',
      },
      editingIndex: null,
      loading: false,
      message: '',
      error: '',
      languageSearch: '',
      showLanguageDropdown: false,
      languages: [
        'English', 'Spanish', 'French', 'German', 'Chinese', 'Japanese', 'Korean',
        'Arabic', 'Hindi', 'Portuguese', 'Russian', 'Italian', 'Dutch', 'Turkish',
        'Bengali', 'Urdu', 'Tamil', 'Telugu', 'Marathi', 'Vietnamese', 'Thai',
        'Polish', 'Swedish', 'Norwegian', 'Danish', 'Finnish', 'Greek', 'Hebrew',
        'Indonesian', 'Malay', 'Persian', 'Romanian', 'Ukrainian', 'Czech',
        'Hungarian', 'Swahili', 'Tagalog', 'Punjabi', 'Kannada', 'Malayalam',
        'Gujarati', 'Burmese', 'Nepali', 'Sinhala', 'Khmer', 'Lao', 'Amharic'
      ],
      filteredLanguages: [],
    };
  },
  mounted() {
    this.fetchCourses();
    this.filteredLanguages = this.languages;
  },
  methods: {
    filterLanguages() {
      const search = this.languageSearch.toLowerCase();
      if (search === '') {
        this.filteredLanguages = this.languages;
      } else {
        this.filteredLanguages = this.languages.filter(lang => 
          lang.toLowerCase().includes(search)
        );
      }
      this.showLanguageDropdown = true;
    },
    selectLanguage(language) {
      if (!this.form.languages) {
        this.form.languages = [];
      }
      if (!this.form.languages.includes(language)) {
        this.form.languages.push(language);
      }
      this.languageSearch = '';
      this.filteredLanguages = this.languages;
      this.showLanguageDropdown = false;
    },
    removeLanguage(index) {
      this.form.languages.splice(index, 1);
    },
    hideLanguageDropdown() {
      setTimeout(() => {
        this.showLanguageDropdown = false;
      }, 200);
    },
    async fetchCourses() {
      try {
        const response = await axios.get('/api/tutor/profile/courses');
        this.courses = response.data.courses || [];
      } catch (err) {
        this.error = 'Failed to load courses';
        this.scrollToError();
      }
    },
    async saveCourse() {
      try {
        this.loading = true;
        this.error = '';
        this.message = '';
        
        let response;
        
        if (this.editingIndex !== null) {
          // Update existing course
          response = await axios.put(`/api/tutor/profile/courses/${this.editingIndex}`, this.form);
        } else {
          // Create new course
          response = await axios.post('/api/tutor/profile/courses', this.form);
        }
        
        this.message = response.data.message || 'Course saved successfully!';
        await this.fetchCourses();
        this.resetForm();
        setTimeout(() => this.message = '', 3000);
        window.scrollTo(0, 0);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to save course';
        this.scrollToError();
      } finally {
        this.loading = false;
      }
    },
    editCourse(index) {
      const course = this.courses[index];
      this.form = { 
        ...course,
        languages: course.languages ? [...course.languages] : (course.language ? [course.language] : [])
      };
      this.languageSearch = '';
      this.editingIndex = index;
      window.scrollTo(0, document.body.scrollHeight);
    },
    async deleteCourse(index) {
      if (!confirm('Are you sure you want to delete this course?')) return;
      
      try {
        this.loading = true;
        this.error = '';
        this.message = '';
        
        const course = this.courses[index];
        const courseId = course.id || index;
        
        const response = await axios.delete(`/api/tutor/profile/courses/${courseId}`);
        this.message = response.data.message || 'Course deleted successfully!';
        await this.fetchCourses();
        setTimeout(() => this.message = '', 3000);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to delete course';
        this.scrollToError();
      } finally {
        this.loading = false;
      }
    },
    resetForm() {
      this.form = {
        title: '',
        description: '',
        level: '',
        duration: '',
        price: '',
        currencys: [],
        mode_of_delivery: '',
        group_size: '',
        certificate: '',
        language: '',
        duration_unit: '',
      };
      this.languageSearch = '';
      this.filteredLanguages = this.languages;
      this.editingIndex = null;
      this.message = '';
      this.error = '';
    },
    scrollToError() {
      this.$nextTick(() => {
        if (this.$refs.errorMessage) {
          this.$refs.errorMessage.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center' 
          });
        }
      })
}  }
};            
</script>
<style scoped>
.badge {
  @apply px-3 py-1 text-xs font-medium rounded-full;
}
.badge-blue { @apply bg-blue-100 text-blue-800; }
.badge-green { @apply bg-green-100 text-green-800; }
.badge-purple { @apply bg-purple-100 text-purple-800; }
.badge-yellow { @apply bg-yellow-100 text-yellow-800; }
.badge-gray { @apply bg-gray-100 text-gray-800; }
.badge-pink { @apply bg-pink-100 text-pink-800; }
  .badge-yellow { @apply bg-yellow-100 text-yellow-800; }
      .badge-gray { @apply bg-gray-100 text-gray-800; }
    </style>