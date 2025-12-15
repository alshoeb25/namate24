<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Education</h2>
    
    <!-- Education List -->
    <div v-if="educations.length > 0" class="mb-6 space-y-4">
      <h3 class="text-lg font-semibold">Your Education</h3>
      <div 
        v-for="(edu, index) in educations" 
        :key="index"
        class="border border-gray-200 rounded-lg p-4 flex justify-between items-start"
      >
        <div class="flex-1">
          <h4 class="font-semibold">
            {{ edu.degree_type }}
            <span v-if="edu.degree_name"> ({{ edu.degree_name }})</span>
            <span v-if="edu.speciality"> in {{ edu.speciality }}</span>
          </h4>
          <p class="text-gray-600">{{ edu.institution }}<span v-if="edu.city">, {{ edu.city }}</span></p>
          <p class="text-sm text-gray-500">
            {{ getMonthName(edu.start_month) }} {{ edu.start_year }} - 
            <span v-if="edu.is_ongoing">Present</span>
            <span v-else>{{ getMonthName(edu.end_month) }} {{ edu.end_year }}</span>
          </p>
          <p v-if="edu.study_mode" class="text-sm text-gray-500">{{ edu.study_mode }}</p>
          <p v-if="edu.score" class="text-sm text-gray-500">Score: {{ edu.score }}</p>
        </div>
        <div class="flex gap-2">
          <button 
            @click="editEducation(index)"
            class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm"
          >
            Edit
          </button>
          <button 
            @click="deleteEducation(index)"
            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Form -->
    <form @submit.prevent="saveEducation" class="border-t pt-6">
      <h3 class="text-lg font-semibold mb-4">{{ editingIndex !== null ? 'Edit' : 'Add' }} Education</h3>
      
      <div class="space-y-4">
        <!-- Degree Type and Degree Name -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Degree Type *</label>
            <select 
              v-model.number="form.degree_type_id" 
              @change="onDegreeTypeChange"
              class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"
              required
            >
              <option value="">Please select</option>
              <option 
                v-for="type in degreeTypes" 
                :key="type.id" 
                :value="type.id"
              >
                {{ type.name }}
              </option>
            </select>
          </div>
          <div v-if="form.degree_type_id !== 0">
            <label class="block text-sm font-medium text-gray-700">Degree Name</label>
            <input 
              v-model="form.degree_name" 
              type="text" 
              class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"
              placeholder="e.g., B.Tech, M.Sc"
            />
          </div>
        </div>

        <!-- Institute with Autocomplete -->
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700">Institute Name with City *</label>
          <input 
            v-model="instituteSearch" 
            @input="searchInstitutes"
            @focus="showInstituteDropdown = true"
            type="text" 
            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"
            placeholder="Type to search institute..."
            required
            autocomplete="off"
          />
          
          <!-- Autocomplete Dropdown -->
          <div 
            v-if="showInstituteDropdown && filteredInstitutes.length > 0"
            class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
          >
            <div
              v-for="institute in filteredInstitutes"
              :key="institute.id"
              @click="selectInstitute(institute)"
              class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100"
            >
              <div class="font-medium">{{ institute.name }}</div>
              <div v-if="institute.city" class="text-sm text-gray-500">{{ institute.city }}</div>
            </div>
          </div>

          <!-- "Others" option -->
          <div class="mt-2">
            <label class="inline-flex items-center">
              <input 
                type="checkbox" 
                v-model="isCustomInstitute"
                @change="onCustomInstituteToggle"
                class="rounded border-gray-300"
              />
              <span class="ml-2 text-sm">Others (Enter manually)</span>
            </label>
          </div>

          <!-- Manual Institute Entry -->
          <div v-if="isCustomInstitute" class="mt-2 grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Institute Name *</label>
              <input 
                v-model="form.institution" 
                type="text" 
                class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"
                placeholder="Institute name"
                required
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">City</label>
              <input 
                v-model="form.city" 
                type="text" 
                class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"
                placeholder="City"
              />
            </div>
          </div>
        </div>

        <!-- Speciality and Score -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Speciality (Optional)</label>
            <input 
              v-model="form.speciality" 
              type="text" 
              class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"
              placeholder="e.g., Computer Science"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Score (Optional)</label>
            <input 
              v-model="form.score" 
              type="text" 
              class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"
              placeholder="e.g., 3.8 GPA or 85%"
            />
          </div>
        </div>

        <!-- Study Mode -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Study Mode</label>
          <select v-model="form.study_mode" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">Select mode</option>
            <option value="Full Time">Full Time</option>
            <option value="Part Time">Part Time</option>
            <option value="Distance/Correspondence">Distance/Correspondence</option>
          </select>
        </div>

        <!-- Start Date -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <select 
                v-model.number="form.start_month" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                required
              >
                <option value="">Month</option>
                <option v-for="(month, idx) in months" :key="idx" :value="idx + 1">
                  {{ month }}
                </option>
              </select>
            </div>
            <div>
              <select 
                v-model.number="form.start_year" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                required
              >
                <option value="">Year</option>
                <option v-for="year in years" :key="year" :value="year">
                  {{ year }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- End Date or Ongoing -->
        <div>
          <div class="flex items-center mb-2">
            <label class="block text-sm font-medium text-gray-700">End Date</label>
            <label class="ml-4 inline-flex items-center">
              <input 
                type="checkbox" 
                v-model="form.is_ongoing"
                @change="onOngoingChange"
                class="rounded border-gray-300"
              />
              <span class="ml-2 text-sm text-gray-600">Currently studying</span>
            </label>
          </div>
          <div v-if="!form.is_ongoing" class="grid grid-cols-2 gap-4">
            <div>
              <select 
                v-model.number="form.end_month" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg"
              >
                <option value="">Month</option>
                <option v-for="(month, idx) in months" :key="idx" :value="idx + 1">
                  {{ month }}
                </option>
              </select>
            </div>
            <div>
              <select 
                v-model.number="form.end_year" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg"
              >
                <option value="">Year</option>
                <option v-for="year in years" :key="year" :value="year">
                  {{ year }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <div class="flex gap-4 pt-4">
          <button 
            type="submit" 
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            :disabled="loading"
          >
            {{ loading ? 'Saving...' : (editingIndex !== null ? 'Update' : 'Add') }}
          </button>
          <button 
            type="button" 
            @click="resetForm" 
            class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
          >
            {{ editingIndex !== null ? 'Cancel Edit' : 'Clear' }}
          </button>
        </div>

        <!-- Messages -->
        <div v-if="message" class="p-4 bg-green-100 text-green-700 rounded-lg">
          {{ message }}
        </div>
        <div v-if="error" class="p-4 bg-red-100 text-red-700 rounded-lg">
          {{ error }}
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Education',
  data() {
    return {
      educations: [],
      degreeTypes: [],
      filteredInstitutes: [],
      instituteSearch: '',
      showInstituteDropdown: false,
      isCustomInstitute: false,
      searchTimeout: null,
      months: [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
      ],
      form: {
        degree_type_id: '',
        degree_type: '',
        degree_name: '',
        institute_id: '',
        institution: '',
        city: '',
        study_mode: '',
        speciality: '',
        score: '',
        start_month: '',
        start_year: '',
        end_month: '',
        end_year: '',
        is_ongoing: false,
      },
      editingIndex: null,
      loading: false,
      message: '',
      error: '',
    };
  },
  computed: {
    years() {
      const currentYear = new Date().getFullYear();
      const years = [];
      for (let i = currentYear; i >= currentYear - 50; i--) {
        years.push(i);
      }
      return years;
    }
  },
  mounted() {
    this.fetchEducation();
    this.fetchDegreeTypes();
    
    // Close dropdown when clicking outside
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },
  methods: {
    async fetchEducation() {
      try {
        const response = await axios.get('/api/tutor/profile/education');
        this.educations = response.data.educations || [];
      } catch (err) {
        this.error = 'Failed to load education';
      }
    },
    async fetchDegreeTypes() {
      try {
        const response = await axios.get('/api/tutor/profile/degree-types');
        this.degreeTypes = response.data.degree_types || [];
      } catch (err) {
        console.error('Failed to load degree types');
      }
    },
    async searchInstitutes() {
      clearTimeout(this.searchTimeout);
      
      if (this.instituteSearch.length < 2) {
        this.filteredInstitutes = [];
        return;
      }
      
      this.searchTimeout = setTimeout(async () => {
        try {
          const response = await axios.get('/api/tutor/profile/institutes/search', {
            params: { q: this.instituteSearch }
          });
          this.filteredInstitutes = response.data.institutes || [];
          this.showInstituteDropdown = true;
        } catch (err) {
          console.error('Failed to search institutes');
        }
      }, 300);
    },
    selectInstitute(institute) {
      this.form.institute_id = institute.id;
      this.form.institution = institute.name;
      this.form.city = institute.city || '';
      this.instituteSearch = `${institute.name}${institute.city ? ', ' + institute.city : ''}`;
      this.showInstituteDropdown = false;
      this.isCustomInstitute = false;
    },
    onCustomInstituteToggle() {
      if (this.isCustomInstitute) {
        this.form.institute_id = null;
        this.form.institution = '';
        this.form.city = '';
        this.instituteSearch = '';
        this.showInstituteDropdown = false;
      }
    },
    onDegreeTypeChange() {
      const selectedType = this.degreeTypes.find(t => t.id === this.form.degree_type_id);
      if (selectedType) {
        this.form.degree_type = selectedType.name;
      }
    },
    onOngoingChange() {
      if (this.form.is_ongoing) {
        this.form.end_month = null;
        this.form.end_year = null;
      }
    },
    handleClickOutside(event) {
      if (!event.target.closest('.relative')) {
        this.showInstituteDropdown = false;
      }
    },
    getMonthName(monthNum) {
      if (!monthNum) return '';
      return this.months[monthNum - 1];
    },
    async saveEducation() {
      try {
        this.loading = true;
        this.error = '';

        // Validate required fields
        if (!this.form.degree_type_id) {
          this.error = 'Please select a degree type';
          return;
        }
        if (!this.form.institution) {
          this.error = 'Please enter or select an institution';
          return;
        }
        if (!this.form.start_month || !this.form.start_year) {
          this.error = 'Please select start date';
          return;
        }
        
        let response;
        
        if (this.editingIndex !== null) {
          response = await axios.post(`/api/tutor/profile/education/${this.editingIndex}`, this.form);
        } else {
          response = await axios.post('/api/tutor/profile/education', this.form);
        }
        
        this.message = response.data.message;
        this.educations = response.data.educations;
        this.resetForm();
        setTimeout(() => this.message = '', 3000);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to save education';
      } finally {
        this.loading = false;
      }
    },
    editEducation(index) {
      const edu = this.educations[index];
      this.form = { ...edu };
      this.editingIndex = index;
      
      // Set institute search text if institute exists
      if (edu.institution) {
        this.instituteSearch = `${edu.institution}${edu.city ? ', ' + edu.city : ''}`;
      }
      
      // Set custom institute flag if no institute_id
      this.isCustomInstitute = !edu.institute_id;
      
      window.scrollTo(0, document.body.scrollHeight);
    },
    async deleteEducation(index) {
      if (!confirm('Are you sure you want to delete this education record?')) return;
      
      try {
        this.loading = true;
        this.error = '';
        const response = await axios.delete(`/api/tutor/profile/education/${index}`);
        this.message = response.data.message;
        this.educations = response.data.educations;
        setTimeout(() => this.message = '', 3000);
      } catch (err) {
        this.error = 'Failed to delete education';
      } finally {
        this.loading = false;
      }
    },
    resetForm() {
      this.form = {
        degree_type_id: '',
        degree_type: '',
        degree_name: '',
        institute_id: '',
        institution: '',
        city: '',
        study_mode: '',
        speciality: '',
        score: '',
        start_month: '',
        start_year: '',
        end_month: '',
        end_year: '',
        is_ongoing: false,
      };
      this.instituteSearch = '';
      this.isCustomInstitute = false;
      this.editingIndex = null;
      this.message = '';
      this.error = '';
    },
  },
};
</script>
