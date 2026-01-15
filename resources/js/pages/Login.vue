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
                  placeholder="email" aria-label="email" />
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

                  <input v-model="payload.password" :type="showPassword ? 'text' : 'password'"
                      class="ml-3 flex-1 outline-none text-gray-700 placeholder-gray-400 bg-transparent"
                      placeholder="Password" />

                  <button type="button" @click="showPassword = !showPassword" class="ml-2 text-gray-400 hover:text-gray-600">
                      <svg v-if="showPassword" class="fi-icon-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                        <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"></path>
                        <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd"></path>
                      </svg>
                      <svg v-else class="fi-icon-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                        <path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 0 0-1.06 1.06l14.5 14.5a.75.75 0 1 0 1.06-1.06l-1.745-1.745a10.029 10.029 0 0 0 3.3-4.38 1.651 1.651 0 0 0 0-1.185A10.004 10.004 0 0 0 9.999 3a9.956 9.956 0 0 0-4.744 1.194L3.28 2.22ZM7.752 6.69l1.092 1.092a2.5 2.5 0 0 1 3.374 3.373l1.091 1.092a4 4 0 0 0-5.557-5.557Z" clip-rule="evenodd"></path>
                        <path d="m10.748 13.93 2.523 2.523a9.987 9.987 0 0 1-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 0 1 0-1.186A10.007 10.007 0 0 1 2.839 6.02L6.07 9.252a4 4 0 0 0 4.678 4.678Z"></path>
                      </svg>
                  </button>
              </div>
          </div>

          <!-- Forgot Password Button -->
          <div class="mt-2 text-right">
              <router-link to="/forgot-password" class="text-pink-600 text-sm font-semibold">
                Forgot your password?
              </router-link>
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
import { useRouter, useRoute } from 'vue-router';
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
    const showPassword = ref(false);
    const userStore = useUserStore();
    const router = useRouter();
    const route = useRoute();

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

        // Check for redirect query parameter first
        const redirectTo = route.query.redirect;
        if (redirectTo) {
          router.push(redirectTo);
        } else {
          // Always redirect to home page after login
          router.push('/');
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

    return { payload, submit, loading, emailVerificationPending, errorMessage, resendVerificationEmail, showPassword };
  }
};
</script>

<style scoped>
.auth-form { max-width:420px; margin:40px auto; padding:20px; background:#fff; border-radius:8px; }
.auth-form input { width:100%; padding:8px; margin-bottom:8px; }
</style>