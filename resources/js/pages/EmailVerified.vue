<template>
  <div class="bg-white flex flex-col items-center justify-center min-h-screen px-4">
    <!-- Success State -->
    <div v-if="status === 'success'" class="w-full max-w-md">
      <!-- Success Icon -->
      <div class="flex justify-center mb-6">
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center">
          <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
      </div>

      <!-- Success Message -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-3">Email Verified!</h1>
        <p class="text-gray-600 text-lg mb-2">
          Your email has been successfully verified.
        </p>
        <p class="text-gray-500 text-sm">
          You can now log in and start using NaMate24.
        </p>
      </div>

      <!-- Welcome Benefits -->
      <div class="bg-gradient-to-r from-pink-50 to-purple-50 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
          <span>🎉</span>
          <span>Welcome to NaMate24!</span>
        </h3>
        <ul class="space-y-2 text-sm text-gray-700">
          <li class="flex items-start gap-2">
            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span>Access thousands of verified tutors</span>
          </li>
          <li class="flex items-start gap-2">
            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span>Post learning requirements</span>
          </li>
          <li class="flex items-start gap-2">
            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span>Connect with tutors directly</span>
          </li>
        </ul>
      </div>

      <!-- Login Button -->
      <button
        @click="goToLogin"
        class="w-full bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 text-white py-4 rounded-full font-semibold text-lg shadow-lg transition-all duration-200 transform hover:scale-105"
      >
        Login to Your Account
      </button>

      <!-- Additional Info -->
      <div class="text-center mt-6">
        <p class="text-sm text-gray-500">
          Need help? <a href="mailto:support@namate24.com" class="text-pink-600 hover:underline font-medium">Contact Support</a>
        </p>
      </div>
    </div>

    <!-- Failed State -->
    <div v-else-if="status === 'failed'" class="w-full max-w-md">
      <!-- Error Icon -->
      <div class="flex justify-center mb-6">
        <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center">
          <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
      </div>

      <!-- Error Message -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-3">Verification Failed</h1>
        <p class="text-gray-600 text-lg mb-2">
          We couldn't verify your email address.
        </p>
        <p class="text-gray-500 text-sm">
          The verification link may have expired or is invalid.
        </p>
      </div>

      <!-- Error Info -->
      <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-red-900 mb-3">What you can do:</h3>
        <ul class="space-y-2 text-sm text-red-800">
          <li class="flex items-start gap-2">
            <span>•</span>
            <span>Try logging in - you may have already verified</span>
          </li>
          <li class="flex items-start gap-2">
            <span>•</span>
            <span>Request a new verification email</span>
          </li>
          <li class="flex items-start gap-2">
            <span>•</span>
            <span>Contact support if the problem persists</span>
          </li>
        </ul>
      </div>

      <!-- Action Buttons -->
      <div class="space-y-3">
        <button
          @click="goToLogin"
          class="w-full bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 text-white py-4 rounded-full font-semibold text-lg shadow-lg transition-all duration-200"
        >
          Try Login
        </button>
        <button
          @click="goToRegister"
          class="w-full bg-white border-2 border-gray-300 hover:border-pink-500 text-gray-700 py-4 rounded-full font-semibold text-lg transition-all duration-200"
        >
          Request New Verification
        </button>
      </div>

      <!-- Support Link -->
      <div class="text-center mt-6">
        <p class="text-sm text-gray-500">
          Still having issues? <a href="mailto:support@namate24.com" class="text-pink-600 hover:underline font-medium">Contact Support</a>
        </p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-else class="w-full max-w-md text-center">
      <div class="flex justify-center mb-6">
        <div class="w-16 h-16 border-4 border-pink-500 border-t-transparent rounded-full animate-spin"></div>
      </div>
      <p class="text-gray-600 text-lg">Verifying your email...</p>
    </div>

    <!-- Logo Footer -->
    <div class="mt-12 text-center">
      <div class="flex items-center justify-center gap-3 mb-3">
        <img 
          src="https://image2url.com/images/1765179057005-967d0875-ac5d-4a43-b65f-a58abd9f651d.png" 
          class="w-12 h-12 object-contain" 
          alt="NaMate24 Logo"
        />
        <h2 class="text-pink-600 font-bold text-2xl">NaMate24</h2>
      </div>
      <p class="text-gray-500 text-sm">Connecting Students with Expert Tutors</p>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';

export default {
  name: 'EmailVerified',
  setup() {
    const router = useRouter();
    const route = useRoute();
    const status = ref(null);

    onMounted(() => {
      // Get status from query parameter
      status.value = route.query.status || 'loading';
    });

    const goToLogin = () => {
      router.push('/login');
    };

    const goToRegister = () => {
      router.push('/register');
    };

    return {
      status,
      goToLogin,
      goToRegister,
    };
  },
};
</script>

<style scoped>
/* Add any additional styles here if needed */
</style>
