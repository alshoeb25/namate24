<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Phone Verification</h2>
    
    <div v-if="phoneVerified" class="p-4 bg-green-100 text-green-700 rounded-lg mb-6">
      ✓ Phone verified: {{ userPhone }}
    </div>

    <div v-else>
      <!-- Step 1: Enter Phone -->
      <div v-if="step === 'phone'" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Phone Number</label>
          <input 
            v-model="form.phone" 
            type="tel" 
            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="+1234567890"
          />
        </div>
        <button 
          @click="sendOtp" 
          class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          :disabled="loading"
        >
          {{ loading ? 'Sending...' : 'Send OTP' }}
        </button>
      </div>

      <!-- Step 2: Verify OTP -->
      <div v-if="step === 'otp'" class="space-y-4">
        <p class="text-gray-700">OTP sent to {{ form.phone }}</p>
        <div>
          <label class="block text-sm font-medium text-gray-700">Enter OTP</label>
          <input 
            v-model="form.otp" 
            type="text" 
            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="000000"
            maxlength="6"
          />
        </div>
        <button 
          @click="verifyOtp" 
          class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
          :disabled="loading"
        >
          {{ loading ? 'Verifying...' : 'Verify OTP' }}
        </button>
        <button 
          @click="step = 'phone'" 
          class="w-full px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition"
        >
          Change Phone
        </button>
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

export default {
  name: 'PhoneOtp',
  data() {
    return {
      form: {
        phone: '',
        otp: '',
      },
      step: 'phone',
      phoneVerified: false,
      userPhone: '',
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
      } catch (err) {
        console.error('Failed to check verification status');
      }
    },
    async sendOtp() {
      try {
        this.loading = true;
        this.error = '';
        await axios.post('/api/tutor/profile/phone/send-otp', { phone: this.form.phone });
        this.message = 'OTP sent successfully';
        this.step = 'otp';
        setTimeout(() => this.message = '', 3000);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to send OTP';
      } finally {
        this.loading = false;
      }
    },
    async verifyOtp() {
      try {
        this.loading = true;
        this.error = '';
        const response = await axios.post('/api/tutor/profile/phone/verify-otp', { otp: this.form.otp });
        this.message = 'Phone verified successfully';
        this.phoneVerified = true;
        this.userPhone = this.form.phone;
        this.step = 'phone';
        this.form = { phone: '', otp: '' };
        setTimeout(() => this.message = '', 3000);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to verify OTP';
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
