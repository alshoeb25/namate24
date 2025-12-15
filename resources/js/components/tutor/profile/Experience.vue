<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Teaching and Professional Experience</h2>

    <!-- Experience List -->
    <div v-if="experiences.length > 0" class="mb-6 space-y-4">
      <h3 class="text-lg font-semibold">Your Experience</h3>
      <div
        v-for="(exp, index) in experiences"
        :key="index"
        class="border border-gray-200 rounded-lg p-4 flex justify-between items-start"
      >
        <div class="flex-1 space-y-1">
          <h4 class="font-semibold text-gray-900">{{ exp.designation }}</h4>
          <p class="text-gray-700">{{ exp.organization }}<span v-if="exp.city">, {{ exp.city }}</span></p>
          <p class="text-sm text-gray-600">Employment: {{ exp.association }}</p>
          <p class="text-sm text-gray-600">
            {{ monthName(exp.start_month) }} {{ exp.start_year }} -
            <span v-if="exp.is_current">Present</span>
            <span v-else>{{ monthName(exp.end_month) }} {{ exp.end_year }}</span>
          </p>
          <p v-if="exp.roles" class="text-sm text-gray-600">Roles: {{ exp.roles }}</p>
        </div>
        <div class="flex gap-2">
          <button
            @click="editExperience(index)"
            class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm"
          >
            Edit
          </button>
          <button
            @click="deleteExperience(index)"
            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Form -->
    <form @submit.prevent="saveExperience" class="border-t pt-6">
      <h3 class="text-lg font-semibold mb-4">{{ editingIndex !== null ? 'Edit' : 'Add' }} Experience</h3>

      <div class="space-y-4">
        <!-- Organization and Designation -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Organization Name *</label>
            <input
              v-model="form.organization"
              type="text"
              class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"
              placeholder="e.g., Bright Minds Academy"
              required
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Designation *</label>
            <input
              v-model="form.designation"
              type="text"
              class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"
              placeholder="e.g., Senior Math Tutor"
              required
            />
          </div>
        </div>

        <!-- City and Employment Type -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">City</label>
            <input
              v-model="form.city"
              type="text"
              class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"
              placeholder="e.g., Mumbai"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Association *</label>
            <select
              v-model="form.association"
              class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"
              required
            >
              <option value="">Please select</option>
              <option value="Full Time">Full Time</option>
              <option value="Part Time">Part Time</option>
            </select>
          </div>
        </div>

        <!-- Dates -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
            <div class="flex flex-col sm:flex-row gap-3">
              <select v-model.number="form.start_month" class="border border-gray-300 rounded px-3 py-2 w-full" required>
                <option value="">Month</option>
                <option v-for="(m, idx) in months" :key="idx" :value="idx + 1">{{ m }}</option>
              </select>
              <select v-model.number="form.start_year" class="border border-gray-300 rounded px-3 py-2 w-full" required>
                <option value="">Year</option>
                <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
              </select>
            </div>
          </div>
          <div>
            <div class="flex items-center mb-2">
              <label class="block text-sm font-medium text-gray-700">End Date</label>
              <label class="ml-3 inline-flex items-center text-sm text-gray-600">
                <input type="checkbox" v-model="form.is_current" @change="onCurrentToggle" class="rounded border-gray-300" />
                <span class="ml-2">Currently working</span>
              </label>
            </div>
            <div class="flex flex-col sm:flex-row gap-3" v-if="!form.is_current">
              <select v-model.number="form.end_month" class="border border-gray-300 rounded px-3 py-2 w-full">
                <option value="">Month</option>
                <option v-for="(m, idx) in months" :key="idx" :value="idx + 1">{{ m }}</option>
              </select>
              <select v-model.number="form.end_year" class="border border-gray-300 rounded px-3 py-2 w-full">
                <option value="">Year</option>
                <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
              </select>
            </div>
            <p v-else class="text-sm text-gray-500">End date left blank (still working)</p>
          </div>
        </div>

        <!-- Roles -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Your roles and responsibilities</label>
          <textarea
            v-model="form.roles"
            rows="4"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
            placeholder="Describe your key responsibilities"
          ></textarea>
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
  name: 'Experience',
  data() {
    return {
      experiences: [],
      months: [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
      ],
      form: {
        organization: '',
        designation: '',
        association: '',
        city: '',
        roles: '',
        start_month: '',
        start_year: '',
        end_month: '',
        end_year: '',
        is_current: false,
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
      const arr = [];
      for (let y = currentYear; y >= currentYear - 50; y--) {
        arr.push(y);
      }
      return arr;
    }
  },
  mounted() {
    this.fetchExperience();
  },
  methods: {
    monthName(num) {
      if (!num) return '';
      return this.months[num - 1];
    },
    onCurrentToggle() {
      if (this.form.is_current) {
        this.form.end_month = null;
        this.form.end_year = null;
      }
    },
    async fetchExperience() {
      try {
        const response = await axios.get('/api/tutor/profile/experience');
        this.experiences = response.data.experiences || [];
      } catch (err) {
        this.error = 'Failed to load experience';
      }
    },
    async saveExperience() {
      try {
        this.loading = true;
        this.error = '';

        if (!this.form.organization || !this.form.designation || !this.form.association) {
          this.error = 'Please fill organization, designation, and association';
          return;
        }
        if (!this.form.start_month || !this.form.start_year) {
          this.error = 'Please select start month and year';
          return;
        }

        // clear end date if current
        if (this.form.is_current) {
          this.form.end_month = null;
          this.form.end_year = null;
        }

        let response;
        if (this.editingIndex !== null) {
          response = await axios.post(`/api/tutor/profile/experience/${this.editingIndex}`, this.form);
        } else {
          response = await axios.post('/api/tutor/profile/experience', this.form);
        }

        this.message = response.data.message;
        this.experiences = response.data.experiences;
        this.resetForm();
        setTimeout(() => (this.message = ''), 3000);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to save experience';
      } finally {
        this.loading = false;
      }
    },
    editExperience(index) {
      const exp = this.experiences[index];
      this.form = { ...exp };
      this.editingIndex = index;
      window.scrollTo(0, document.body.scrollHeight);
    },
    async deleteExperience(index) {
      if (!confirm('Are you sure you want to delete this experience?')) return;
      try {
        this.loading = true;
        this.error = '';
        const response = await axios.delete(`/api/tutor/profile/experience/${index}`);
        this.message = response.data.message;
        this.experiences = response.data.experiences;
        setTimeout(() => (this.message = ''), 3000);
      } catch (err) {
        this.error = 'Failed to delete experience';
      } finally {
        this.loading = false;
      }
    },
    resetForm() {
      this.form = {
        organization: '',
        designation: '',
        association: '',
        city: '',
        roles: '',
        start_month: '',
        start_year: '',
        end_month: '',
        end_year: '',
        is_current: false,
      };
      this.editingIndex = null;
      this.message = '';
      this.error = '';
    },
  },
};
</script>
