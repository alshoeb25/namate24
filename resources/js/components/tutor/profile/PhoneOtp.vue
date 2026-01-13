<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Phone Number</h2>
    
    <div v-if="phoneVerified && !isEditing" class="p-4 bg-green-100 text-green-700 rounded-lg mb-6 flex items-center justify-between">
      <span> Phone Number: {{ userPhone }}</span>
      <button 
        @click="isEditing = true"
        class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition"
      >
        Edit
      </button>
    </div>

    <div v-if="!phoneVerified || isEditing">
      <!-- Enter Phone -->
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
          <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
            <select 
              v-model="form.country_code" 
              class="w-full sm:w-auto min-w-[120px] px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
            >
              <option v-for="country in countryCodes" :key="country.iso" :value="country.code">
                {{ country.flag }} {{ country.code }}
              </option>
            </select>
            <input 
              v-model="form.phone" 
              type="tel" 
              class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
              placeholder="9876543210"
            />
          </div>
        </div>
        <div class="flex gap-2">
          <button 
            @click="savePhone" 
            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm sm:text-base"
            :disabled="loading"
          >
            {{ loading ? 'Saving...' : 'Save Phone Number' }}
          </button>
          <button 
            v-if="isEditing"
            @click="cancelEdit" 
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition text-sm"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Messages -->
    <div v-if="message" class="mt-4 p-4 bg-green-100 text-green-700 rounded-lg">
      {{ message }}
    </div>
    <div v-if="error" class="mt-4 p-4 bg-red-100 text-red-700 rounded-lg">
      {{ error }}
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { countryCodes } from '../../../utils/countryCodes';

export default {
  name: 'PhoneOtp',
  data() {
    return {
      countryCodes,
      form: {
        phone: '',
        country_code: '+91',
      },
      phoneVerified: false,
      userPhone: '',
      isEditing: false,
      loading: false,
      message: '',
      error: '',
    };
  },
  mounted() {
    this.checkVerificationStatus();
  },
  methods: {
    async checkVerificationStatus() {
      try {
        const response = await axios.get('/api/tutor/profile/personal-details');
        this.phoneVerified = response.data.user.phone_verified;
        this.userPhone = response.data.user.phone;
        if (response.data.user.phone) {
          this.form.phone = response.data.user.phone;
        }
        if (response.data.user.country_code) {
          this.form.country_code = response.data.user.country_code;
        }
      } catch (err) {
        console.error('Failed to check verification status');
      }
    },
    async savePhone() {
      try {
        this.loading = true;
        this.error = '';
        await axios.post('/api/tutor/profile/phone/save', { 
          phone: this.form.phone,
          country_code: this.form.country_code
        });
        this.message = 'Phone number saved successfully';
        this.phoneVerified = true;
        this.userPhone = this.form.country_code + ' ' + this.form.phone;
        this.isEditing = false;
        setTimeout(() => this.message = '', 3000);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to save phone number';
      } finally {
        this.loading = false;
      }
    },
    cancelEdit() {
      this.isEditing = false;
      this.error = '';
      // Reload the previous values
      this.checkVerificationStatus();
    },
  },
};
</script>
