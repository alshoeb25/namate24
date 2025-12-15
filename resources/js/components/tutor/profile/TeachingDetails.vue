<template>
  <main class="max-w-4xl mx-auto mt-16 px-4 mb-20">
    <h2 class="text-3xl font-semibold text-gray-800 mb-8">Teaching Details</h2>

    <form class="space-y-10" @submit.prevent="save">
      <!-- Fee Details -->
      <div class="bg-white w-full p-6 sm:p-8 rounded-lg shadow-md">
        <h3 class="text-xl font-medium text-gray-700 mb-4">Fee Details</h3>
        <div class="grid md:grid-cols-3 gap-4">
          <div>
            <label class="block text-gray-700 mb-2">I charge</label>
            <select v-model="form.charge_type" class="w-full border px-3 py-2 rounded-sm" required>
              <option value="Hourly">Hourly</option>
              <option value="Monthly">Monthly</option>
            </select>
          </div>
          <div>
            <label class="block text-gray-700 mb-2">Minimum Fee (INR)</label>
            <input v-model.number="form.min_fee" type="number" min="0" class="w-full border px-3 py-2 rounded-sm" required />
          </div>
          <div>
            <label class="block text-gray-700 mb-2">Maximum Fee (INR)</label>
            <input v-model.number="form.max_fee" type="number" min="0" class="w-full border px-3 py-2 rounded-sm" required />
          </div>
        </div>
        <div class="mt-4">
          <label class="block text-gray-700 mb-2">Fee Details (How fee can vary)</label>
          <textarea v-model="form.fee_notes" rows="4" class="w-full border px-3 py-2 rounded-sm focus:ring-1 focus:ring-blue-500"></textarea>
        </div>
      </div>

      <!-- Experience -->
      <div class="bg-white w-full p-6 sm:p-8 rounded-lg shadow-md">
        <h3 class="text-xl font-medium text-gray-700 mb-4">Experience</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-gray-700 mb-2">Years of total experience (Teaching & other)</label>
            <input v-model.number="form.experience_total_years" type="number" min="0" class="w-full border px-3 py-2 rounded-sm bg-green-50" required />
          </div>
          <div>
            <label class="block text-gray-700 mb-2">Years of total teaching experience (Offline + Online)</label>
            <input v-model.number="form.experience_teaching_years" type="number" min="0" class="w-full border px-3 py-2 rounded-sm" required />
          </div>
          <div>
            <label class="block text-gray-700 mb-2">Years of online teaching experience</label>
            <input v-model.number="form.experience_online_years" type="number" min="0" class="w-full border px-3 py-2 rounded-sm" required />
          </div>
        </div>
      </div>

      <!-- Preferences -->
      <div class="bg-white w-full p-6 sm:p-8 rounded-lg shadow-md">
        <h3 class="text-xl font-medium text-gray-700 mb-4">Teaching Preferences</h3>
        <div class="space-y-5">
          <div>
            <label class="block text-gray-700 mb-2">Willing to travel to student?</label>
            <div class="flex gap-6">
              <label class="flex items-center gap-2">
                <input type="radio" value="true" v-model="travelWillingRadio" /> Yes
              </label>
              <label class="flex items-center gap-2">
                <input type="radio" value="false" v-model="travelWillingRadio" /> No
              </label>
            </div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2">How far can you travel? (kms)</label>
            <input v-model.number="form.travel_distance_km" type="number" min="0" class="w-full border px-3 py-2 rounded-sm" :disabled="!form.travel_willing" />
          </div>

          <div>
            <label class="block text-gray-700 mb-2">Available for online teaching?</label>
            <div class="flex gap-6">
              <label class="flex items-center gap-2"><input type="radio" value="true" v-model="onlineAvailableRadio" /> Yes</label>
              <label class="flex items-center gap-2"><input type="radio" value="false" v-model="onlineAvailableRadio" /> No</label>
            </div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2">Do you have a digital pen?</label>
            <div class="flex gap-6">
              <label class="flex items-center gap-2"><input type="radio" value="true" v-model="digitalPenRadio" /> Yes</label>
              <label class="flex items-center gap-2"><input type="radio" value="false" v-model="digitalPenRadio" /> No</label>
            </div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2">Do you help with homework and assignments?</label>
            <div class="flex gap-6">
              <label class="flex items-center gap-2"><input type="radio" value="true" v-model="homeworkRadio" /> Yes</label>
              <label class="flex items-center gap-2"><input type="radio" value="false" v-model="homeworkRadio" /> No</label>
            </div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2">Full time teacher employed by School/College?</label>
            <div class="flex gap-6">
              <label class="flex items-center gap-2"><input type="radio" value="true" v-model="employedRadio" /> Yes</label>
              <label class="flex items-center gap-2"><input type="radio" value="false" v-model="employedRadio" /> No</label>
            </div>
          </div>

          <div>
            <label class="block text-gray-700 mb-2">Opportunities you are interested in</label>
            <select v-model="selectedOpportunity" class="w-full border px-3 py-2 rounded-sm">
              <option value="">Please select</option>
              <option value="Part Time">Part Time</option>
              <option value="Full Time">Full Time</option>
              <option value="Both (Part Time & Full Time)">Both (Part Time & Full Time)</option>
            </select>
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
                <div v-if="filteredLanguages.length === 0" class="px-3 py-2 text-sm text-gray-500">No results</div>
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
        </div>
      </div>

      <!-- Save -->
      <div class="pt-2 flex items-center gap-4">
        <button type="submit" class="px-10 py-3 bg-blue-600 text-white rounded-sm hover:bg-blue-700" :disabled="loading">
          {{ loading ? 'Saving...' : 'Save' }}
        </button>
        <span v-if="message" class="text-green-700">{{ message }}</span>
        <span v-if="error" class="text-red-700">{{ error }}</span>
      </div>
    </form>
  </main>
</template>

<script>
import axios from 'axios';

export default {
  name: 'TeachingDetails',
  data() {
    return {
      form: {
        charge_type: 'Hourly',
        min_fee: '',
        max_fee: '',
        fee_notes: '',
        experience_total_years: '',
        experience_teaching_years: '',
        experience_online_years: '',
        travel_willing: true,
        travel_distance_km: '',
        online_available: true,
        has_digital_pen: false,
        helps_homework: false,
        employed_full_time: false,
        opportunities: [],
        languages: [],
      },
      selectedOpportunity: '',
      languageSearch: '',
      languageDropdown: false,
      languagesOptions: ['English','Hindi','Urdu','Marathi','Tamil','Telugu','Bengali','Gujarati','Kannada','Malayalam','Punjabi'],
      loading: false,
      message: '',
      error: '',
    };
  },
  computed: {
    travelWillingRadio: {
      get() {
        return String(this.form.travel_willing);
      },
      set(val) {
        this.form.travel_willing = val === 'true';
        if (!this.form.travel_willing) {
          this.form.travel_distance_km = null;
        }
      },
    },
    onlineAvailableRadio: {
      get() {
        return String(this.form.online_available);
      },
      set(val) {
        this.form.online_available = val === 'true';
      },
    },
    digitalPenRadio: {
      get() {
        return String(this.form.has_digital_pen);
      },
      set(val) {
        this.form.has_digital_pen = val === 'true';
      },
    },
    homeworkRadio: {
      get() {
        return String(this.form.helps_homework);
      },
      set(val) {
        this.form.helps_homework = val === 'true';
      },
    },
    employedRadio: {
      get() {
        return String(this.form.employed_full_time);
      },
      set(val) {
        this.form.employed_full_time = val === 'true';
      },
    },
    filteredLanguages() {
      if (!this.languageSearch) return this.languagesOptions;
      return this.languagesOptions.filter((l) => l.toLowerCase().includes(this.languageSearch.toLowerCase()));
    },
  },
  mounted() {
    this.fetchTeachingDetails();
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },
  methods: {
    handleClickOutside(e) {
      if (!e.target.closest('.relative')) {
        this.languageDropdown = false;
      }
    },
    toggleLanguage(lang) {
      const exists = this.form.languages.includes(lang);
      this.form.languages = exists
        ? this.form.languages.filter((l) => l !== lang)
        : [...this.form.languages, lang];
    },
    hydrateFromResponse(data) {
      this.form.charge_type = data.charge_type || 'Hourly';
      this.form.min_fee = data.min_fee ?? '';
      this.form.max_fee = data.max_fee ?? '';
      this.form.fee_notes = data.fee_notes || '';
      this.form.experience_total_years = data.experience_total_years ?? '';
      this.form.experience_teaching_years = data.experience_teaching_years ?? '';
      this.form.experience_online_years = data.experience_online_years ?? '';
      this.form.travel_willing = Boolean(data.travel_willing);
      this.form.travel_distance_km = data.travel_distance_km ?? '';
      this.form.online_available = Boolean(data.online_available);
      this.form.has_digital_pen = Boolean(data.has_digital_pen);
      this.form.helps_homework = Boolean(data.helps_homework);
      this.form.employed_full_time = Boolean(data.employed_full_time);
      this.form.opportunities = data.opportunities || [];
      this.form.languages = data.languages || [];
      this.selectedOpportunity = this.form.opportunities[0] || '';
    },
    async fetchTeachingDetails() {
      try {
        this.loading = true;
        const response = await axios.get('/api/tutor/profile/teaching-details');
        this.hydrateFromResponse(response.data || {});
      } catch (err) {
        this.error = 'Failed to load teaching details';
      } finally {
        this.loading = false;
      }
    },
    async save() {
      try {
        this.loading = true;
        this.error = '';

        if (!this.form.charge_type || this.form.min_fee === '' || this.form.max_fee === '') {
          this.error = 'Please complete the fee section';
          return;
        }
        if (Number(this.form.max_fee) < Number(this.form.min_fee)) {
          this.error = 'Maximum fee must be greater than or equal to minimum fee';
          return;
        }
        if (this.form.experience_total_years === '' || this.form.experience_teaching_years === '' || this.form.experience_online_years === '') {
          this.error = 'Please fill all experience fields';
          return;
        }

        const payload = {
          ...this.form,
          opportunities: this.selectedOpportunity ? [this.selectedOpportunity] : [],
        };

        const response = await axios.post('/api/tutor/profile/teaching-details', payload);
        this.message = response.data.message;
        setTimeout(() => (this.message = ''), 3000);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to save';
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
