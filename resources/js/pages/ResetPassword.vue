<template>
  <div class="bg-white flex flex-col items-center justify-start min-h-screen pt-10">
    <div class="flex items-center gap-3">
      <img src="https://image2url.com/images/1765179057005-967d0875-ac5d-4a43-b65f-a58abd9f651d.png" class="w-16 h-16 object-contain" />
      <h1 class="text-pink-600 font-bold text-2xl">Namate 24</h1>
    </div>

    <div class="w-full max-w-sm mt-10 px-4">
      <h2 class="text-2xl font-bold text-gray-900">Reset password</h2>
      <p class="text-gray-600 mt-1 text-sm">Choose a new password for your account.</p>

      <form @submit.prevent="submit" class="mt-6 space-y-4">
        <div v-if="message" class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700 text-sm">{{ message }}</div>
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700 text-sm">{{ error }}</div>

        <div class="relative">
          <div class="flex items-center rounded-full border border-gray-300 px-4 py-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-pink-500" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10 12a5 5 0 100-10 5 5 0 000 10zm-7 7a7 7 0 1114 0H3z" />
            </svg>
            <input v-model="email" type="email" class="ml-3 flex-1 outline-none text-gray-700 placeholder-gray-400 bg-transparent" placeholder="Email address" />
          </div>
        </div>

        <div class="relative">
          <div class="flex items-center rounded-full border border-gray-300 px-4 py-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3V6a3 3 0 10-6 0v2c0 1.657 1.343 3 3 3z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11h14v10H5z" />
            </svg>
            <input v-model="password" type="password" class="ml-3 flex-1 outline-none text-gray-700 placeholder-gray-400 bg-transparent" placeholder="New password" />
          </div>
        </div>

        <div class="relative">
          <div class="flex items-center rounded-full border border-gray-300 px-4 py-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3V6a3 3 0 10-6 0v2c0 1.657 1.343 3 3 3z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11h14v10H5z" />
            </svg>
            <input v-model="confirmPassword" type="password" class="ml-3 flex-1 outline-none text-gray-700 placeholder-gray-400 bg-transparent" placeholder="Confirm new password" />
          </div>
        </div>

        <button type="submit" :disabled="loading" class="w-full bg-pink-500 hover:bg-pink-600 disabled:opacity-60 text-white py-3 rounded-full font-medium text-lg shadow-md flex items-center justify-center">
          <svg v-if="loading" class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
          </svg>
          <span v-if="!loading">Update password</span>
          <span v-else>Updating...</span>
        </button>
      </form>

      <div class="mt-6 text-center text-sm">
        <router-link to="/login" class="text-pink-600 font-semibold">Back to login</router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

export default {
  setup() {
    const route = useRoute();
    const router = useRouter();
    const email = ref('');
    const password = ref('');
    const confirmPassword = ref('');
    const loading = ref(false);
    const message = ref('');
    const error = ref('');
    const token = ref('');

    onMounted(() => {
      token.value = route.query.token || '';
      email.value = route.query.email || '';
    });

    async function submit() {
      message.value = '';
      error.value = '';

      if (!email.value || !token.value) {
        error.value = 'Reset link is missing email or token.';
        return;
      }

      if (!password.value || password.value.length < 6) {
        error.value = 'Password must be at least 6 characters.';
        return;
      }

      if (password.value !== confirmPassword.value) {
        error.value = 'Passwords do not match.';
        return;
      }

      loading.value = true;
      try {
        const response = await axios.post('/api/password/reset', {
          email: email.value,
          token: token.value,
          password: password.value,
          password_confirmation: confirmPassword.value,
        });

        message.value = response.data?.message || 'Password updated. You can now log in.';

        setTimeout(() => router.push('/login'), 1200);
      } catch (e) {
        const status = e.response?.status;
        if (status === 404) {
          error.value = 'We could not find an account with that email.';
        } else {
          error.value = e.response?.data?.message || 'Could not reset password. Please try again.';
        }
      } finally {
        loading.value = false;
      }
    }

    return { email, password, confirmPassword, loading, message, error, submit };
  }
};
</script>
