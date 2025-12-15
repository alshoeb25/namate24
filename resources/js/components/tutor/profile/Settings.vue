<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Settings</h2>
    
    <form @submit.prevent="updateSettings">
      <div class="space-y-4">
        <div class="border-b pb-4">
          <h3 class="text-lg font-semibold mb-3">Privacy</h3>
          <label class="flex items-center gap-3">
            <input 
              v-model="form.do_not_share_contact" 
              type="checkbox" 
              class="rounded w-4 h-4"
            />
            <span class="text-sm font-medium text-gray-700">Do not share contact information</span>
          </label>
          <p class="text-xs text-gray-500 mt-2">
            When checked, only your name will be visible on your public profile. Contact details will remain hidden.
          </p>
        </div>

        <div class="border-b pb-4">
          <h3 class="text-lg font-semibold mb-3">Notification Preferences</h3>
          <div class="space-y-2">
            <label class="flex items-center gap-3">
              <input 
                type="checkbox" 
                v-model="notifications.booking_requests"
                class="rounded w-4 h-4"
              />
              <span class="text-sm font-medium text-gray-700">Booking Requests</span>
            </label>
            <label class="flex items-center gap-3">
              <input 
                type="checkbox" 
                v-model="notifications.messages"
                class="rounded w-4 h-4"
              />
              <span class="text-sm font-medium text-gray-700">Messages</span>
            </label>
            <label class="flex items-center gap-3">
              <input 
                type="checkbox" 
                v-model="notifications.reviews"
                class="rounded w-4 h-4"
              />
              <span class="text-sm font-medium text-gray-700">Reviews & Ratings</span>
            </label>
            <label class="flex items-center gap-3">
              <input 
                type="checkbox" 
                v-model="notifications.promotions"
                class="rounded w-4 h-4"
              />
              <span class="text-sm font-medium text-gray-700">Promotional Emails</span>
            </label>
          </div>
        </div>

        <div class="flex gap-4 pt-4">
          <button 
            type="submit" 
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            :disabled="loading"
          >
            {{ loading ? 'Saving...' : 'Save Settings' }}
          </button>
          <button 
            type="button" 
            @click="resetForm" 
            class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition"
          >
            Cancel
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
  name: 'Settings',
  data() {
    return {
      form: {
        do_not_share_contact: false,
        notification_preferences: {},
      },
      notifications: {
        booking_requests: false,
        messages: false,
        reviews: false,
        promotions: false,
      },
      loading: false,
      message: '',
      error: '',
    };
  },
  mounted() {
    this.fetchSettings();
  },
  methods: {
    async fetchSettings() {
      try {
        this.loading = true;
        const response = await axios.get('/api/tutor/profile/settings');
        this.form.do_not_share_contact = response.data.do_not_share_contact || false;
        const prefs = response.data.notification_preferences || {};
        this.notifications = {
          booking_requests: prefs.booking_requests || false,
          messages: prefs.messages || false,
          reviews: prefs.reviews || false,
          promotions: prefs.promotions || false,
        };
      } catch (err) {
        this.error = 'Failed to load settings';
      } finally {
        this.loading = false;
      }
    },
    async updateSettings() {
      try {
        this.loading = true;
        this.error = '';
        const payload = {
          do_not_share_contact: this.form.do_not_share_contact,
          notification_preferences: this.notifications,
        };
        const response = await axios.post('/api/tutor/profile/settings', payload);
        this.message = response.data.message;
        setTimeout(() => this.message = '', 3000);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to update settings';
      } finally {
        this.loading = false;
      }
    },
    resetForm() {
      this.fetchSettings();
      this.message = '';
      this.error = '';
    },
  },
};
</script>
