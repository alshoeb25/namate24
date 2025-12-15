<template>
  <div class="bg-white flex flex-col items-center justify-center min-h-screen">

    <!-- LOGO AND NAME -->
    <div class="flex items-center gap-3 mb-10">
      <img src="https://image2url.com/images/1765179057005-967d0875-ac5d-4a43-b65f-a58abd9f651d.png" class="w-20 h-20 object-contain" />
      <h1 class="text-pink-600 font-bold text-3xl">Namate 24</h1>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="w-full max-w-md px-4">

      <!-- Verifying state -->
      <div v-if="verifying" class="text-center">
        <div class="flex justify-center mb-6">
          <svg class="animate-spin h-12 w-12 text-pink-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Verifying Email</h2>
        <p class="text-gray-600 mt-2">Please wait while we verify your email address...</p>
      </div>

      <!-- Success state -->
      <div v-else-if="verified" class="bg-green-50 border border-green-200 rounded-lg p-8 text-center">
        <div class="text-green-600 text-5xl mb-4">✓</div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Email Verified!</h2>
        <p class="text-gray-600 mb-6">
          Your email has been successfully verified. You can now login to your account.
        </p>
        <button @click="goToLogin" class="w-full bg-pink-500 hover:bg-pink-600 text-white py-3 rounded-full font-medium text-lg">
          Go to Login
        </button>
      </div>

      <!-- Error state -->
      <div v-else class="bg-red-50 border border-red-200 rounded-lg p-8 text-center">
        <div class="text-red-600 text-5xl mb-4">✗</div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Verification Failed</h2>
        <p class="text-gray-600 mb-6">
          {{ errorMessage }}
        </p>
        <div class="flex gap-4">
          <button @click="goToLogin" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-3 rounded-full font-medium">
            Go to Login
          </button>
          <button @click="goToRegister" class="flex-1 bg-pink-500 hover:bg-pink-600 text-white py-3 rounded-full font-medium">
            Back to Register
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';

export default {
  setup() {
    const router = useRouter();
    const route = useRoute();
    
    const verifying = ref(true);
    const verified = ref(false);
    const errorMessage = ref('');

    onMounted(async () => {
      const token = route.query.token;

      if (!token) {
        verifying.value = false;
        errorMessage.value = 'No verification token provided. Please use the link from your email.';
        return;
      }

      try {
        const response = await axios.post('/api/email/verify', { token });
        
        verified.value = true;
        verifying.value = false;
      } catch (e) {
        verifying.value = false;
        errorMessage.value = e.response?.data?.message || 'Failed to verify email. The link may have expired.';
      }
    });

    function goToLogin() {
      router.push('/login');
    }

    function goToRegister() {
      router.push('/register');
    }

    return { verifying, verified, errorMessage, goToLogin, goToRegister };
  }
};
</script>

<style scoped>
</style>
