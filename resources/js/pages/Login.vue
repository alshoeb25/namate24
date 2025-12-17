<template>
  <div class="bg-white flex flex-col items-center justify-start min-h-screen pt-10">

    <!-- LOGO AND NAME SIDE BY SIDE -->
    <div class="flex items-center gap-3">
        <img src="https://image2url.com/images/1765179057005-967d0875-ac5d-4a43-b65f-a58abd9f651d.png"
            class="w-20 h-20 object-contain" />
        <h1 class="text-pink-600 font-bold text-3xl">Namate 24</h1>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="w-full max-w-xs mt-10 px-4">

        <!-- Login Heading -->
        <h2 class="text-2xl font-bold text-gray-900">Login</h2>
        <p class="text-gray-600 mt-1 text-sm">
            Don't have an account?
            <router-link to="/register" class="text-pink-600 font-semibold">sign up</router-link>
        </p>

        <form @submit.prevent="submit">
          <!-- Email verification pending message -->
          <div v-if="emailVerificationPending" class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4">
            <p class="text-orange-700 text-sm">
              <strong>Email not verified.</strong> Please check your email and click the verification link before logging in.
            </p>
            <button type="button" @click="resendVerificationEmail" class="text-orange-600 font-semibold text-sm mt-2">
              Resend verification email
            </button>
          </div>

          <!-- Error message -->
          <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            <p class="text-red-700 text-sm">{{ errorMessage }}</p>
          </div>

          <!-- Phone / Email Input -->
            <div class="mt-6 relative">
              <div class="flex items-center rounded-full border border-gray-300 px-4 py-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-pink-500" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 12a5 5 0 100-10 5 5 0 000 10zm-7 7a7 7 0 1114 0H3z" />
                </svg>
                <input name="identifier" v-model="payload.identifier" type="text" class="ml-3 flex-1 outline-none text-gray-700 placeholder-gray-400 bg-transparent"
                  placeholder="email or phone" aria-label="email or phone" />
              </div>
            </div>

          <!-- Password Input -->
          <div class="mt-4 relative">
              <div class="flex items-center rounded-full border border-gray-300 px-4 py-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-pink-500" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 11c1.657 0 3-1.343 3-3V6a3 3 0 10-6 0v2c0 1.657 1.343 3 3 3z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11h14v10H5z" />
                  </svg>

                  <input v-model="payload.password" type="password"
                      class="ml-3 flex-1 outline-none text-gray-700 placeholder-gray-400 bg-transparent"
                      placeholder="Password" />
              </div>
          </div>

          <!-- Forgot Password Button -->
          <div class="mt-2 text-right">
              <a href="#" class="text-pink-600 text-sm font-semibold">
                  Forgot your password?
              </a>
          </div>

          <!-- Login Button -->
          <button type="submit"
              :disabled="loading"
              class="w-full mt-6 bg-pink-500 hover:bg-pink-600 disabled:opacity-60 text-white py-3 rounded-full font-medium text-lg shadow-md flex items-center justify-center">
              <svg v-if="loading" class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
              </svg>
              <span v-if="!loading">Login</span>
              <span v-else>Logging in...</span>
          </button>
        </form>

        <!-- Divider -->
        <div class="flex items-center gap-4 mt-6">
          <div class="flex-1 h-px bg-gray-300"></div>
          <span class="text-gray-500 text-sm">or</span>
          <div class="flex-1 h-px bg-gray-300"></div>
        </div>

        <!-- Google Login Button -->
        <div class="mt-6">
          <GoogleLoginButton role="student" />
        </div>

    </div>

  </div>
</template>

<script>
import { reactive, ref } from 'vue';
import { useUserStore } from '../store';
import { useRouter } from 'vue-router';
import axios from 'axios';
import GoogleLoginButton from '../components/GoogleLoginButton.vue';

export default {
  components: {
    GoogleLoginButton,
  },
  setup() {
    const payload = reactive({ identifier: '', password: '' });
    const loading = ref(false);
    const emailVerificationPending = ref(false);
    const errorMessage = ref('');
    const userStore = useUserStore();
    const router = useRouter();

    async function submit() {
      errorMessage.value = '';
      emailVerificationPending.value = false;
      loading.value = true;

      try {
        const id = (payload.identifier || '').trim();
        if (!id || !payload.password) {
          errorMessage.value = 'Please enter both email/phone and password';
          return;
        }

        const credentials = { password: payload.password };
        // simple detection: if contains @ treat as email, otherwise phone
        if (id.includes('@')) {
          credentials.email = id;
        } else {
          credentials.phone = id;
        }

        const response = await axios.post('/api/login', credentials);
        
        // Check if email is not verified
        if (response.data.email_verified === false) {
          emailVerificationPending.value = true;
          return;
        }

        // Login successful
        await userStore.setToken(response.data.token);
        await userStore.setUser(response.data.user);

        // If logged in user is a tutor, send them to the tutor dashboard in the SPA
        if (userStore.user && userStore.user.role === 'tutor') {
          router.push('/tutor/profile');
        } else {
          router.push(response.data.redirect_url || '/');
        }
      } catch (e) {
        const err = e.response?.data?.message || 'Login failed';
        if (e.response?.status === 403 && e.response?.data?.email_verified === false) {
          emailVerificationPending.value = true;
          errorMessage.value = err;
        } else {
          errorMessage.value = err;
        }
      } finally {
        loading.value = false;
      }
    }

    async function resendVerificationEmail() {
      loading.value = true;
      try {
        const id = (payload.identifier || '').trim();
        if (!id || !id.includes('@')) {
          errorMessage.value = 'Please enter a valid email address';
          return;
        }

        await axios.post('/api/email/resend-verification', { email: id });
        errorMessage.value = 'Verification email sent! Check your inbox.';
      } catch (e) {
        errorMessage.value = 'Failed to resend email. Try again later.';
      } finally {
        loading.value = false;
      }
    }

    return { payload, submit, loading, emailVerificationPending, errorMessage, resendVerificationEmail };
  }
};
</script>

<style scoped>
.auth-form { max-width:420px; margin:40px auto; padding:20px; background:#fff; border-radius:8px; }
.auth-form input { width:100%; padding:8px; margin-bottom:8px; }
</style>