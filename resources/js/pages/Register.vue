<template>
  <div class="bg-white flex flex-col items-center justify-start min-h-screen pt-10">

    <!-- LOGO AND NAME -->
    <div class="flex items-center gap-3">
      <img src="https://image2url.com/images/1765179057005-967d0875-ac5d-4a43-b65f-a58abd9f651d.png" class="w-20 h-20 object-contain" />
      <h1 class="text-pink-600 font-bold text-3xl">Namate 24</h1>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="w-full max-w-xs mt-10 px-4">

        <!-- Show email verification message if registered with email -->
        <div v-if="verificationSent" class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
          <div class="text-green-600 text-4xl mb-4">✓</div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Check Your Email</h2>
          <p class="text-gray-600 mb-4">
            We've sent a verification link to <strong>{{ registeredEmail }}</strong>
          </p>
          <p class="text-gray-600 mb-6">
            Click the link in the email to verify your account. The link expires in 24 hours.
          </p>
          <button @click="goToLogin" class="w-full bg-pink-500 hover:bg-pink-600 text-white py-3 rounded-full font-medium">
            Go to Login
          </button>
          <p class="text-gray-500 text-sm mt-4">
            Didn't receive the email? Check your spam folder or 
            <button @click="resendEmail" class="text-pink-600 font-semibold">resend it</button>
          </p>
        </div>

        <!-- Sign up form (shown if not yet registered) -->
        <div v-else>
          <!-- Signup Heading -->
          <h2 class="text-2xl font-bold text-gray-900">Sign Up</h2>
          <p class="text-gray-600 mt-1 text-sm">
              Already have an account?
              <router-link to="/login" class="text-pink-600 font-semibold">login</router-link>
          </p>

          <form @submit.prevent="submit">
            <!-- Full Name -->
            <div class="mt-6 relative">
                <input v-model="payload.name" type="text" placeholder="Full Name"
                  class="w-full rounded-full border border-gray-300 px-4 py-3 outline-none text-gray-700 placeholder-gray-400" />
            </div>

            <!-- Email or Phone -->
            <div class="mt-4 relative">
                <div class="flex items-center rounded-full border border-gray-300 px-4 py-3">
                    <input name="identifier" v-model="payload.identifier" type="text" placeholder="Email or Phone"
                      class="ml-3 flex-1 outline-none text-gray-700 placeholder-gray-400 bg-transparent" />
                </div>
            </div>

            <!-- Password -->
            <div class="mt-4 relative">
                <input v-model="payload.password" type="password" placeholder="Password"
                  class="w-full rounded-full border border-gray-300 px-4 py-3 outline-none text-gray-700 placeholder-gray-400" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4 relative">
                <input v-model="payload.confirmPassword" type="password" placeholder="Confirm Password"
                  class="w-full rounded-full border border-gray-300 px-4 py-3 outline-none text-gray-700 placeholder-gray-400" />
            </div>

            <!-- Role -->
            <div class="mt-4">
              <select v-model="payload.role" class="w-full rounded-full border border-gray-300 px-4 py-3">
                <option value="student">Student</option>
                <option value="tutor">Tutor</option>
              </select>
            </div>

            <!-- Error message -->
            <div v-if="errorMessage" class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
              {{ errorMessage }}
            </div>

            <!-- Signup Button -->
            <button type="submit" :disabled="loading" class="w-full mt-6 bg-pink-500 hover:bg-pink-600 disabled:opacity-60 text-white py-3 rounded-full font-medium text-lg shadow-md flex items-center justify-center">
              <svg v-if="loading" class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
              </svg>
              <span v-if="!loading">Sign Up</span>
              <span v-else>Signing up...</span>
            </button>
          </form>
        </div>

    </div>
  </div>
</template>

<script>
import { reactive, ref } from 'vue';
import { useUserStore } from '../store';
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
  setup() {
    const payload = reactive({ name: '', identifier: '', password: '', confirmPassword: '', role: 'student' });
    const userStore = useUserStore();
    const router = useRouter();

    const loading = ref(false);
    const verificationSent = ref(false);
    const registeredEmail = ref('');
    const errorMessage = ref('');

    async function submit() {
      errorMessage.value = '';
      loading.value = true;
      try {
        // validate
        if (!payload.name || !payload.identifier || !payload.password) {
          errorMessage.value = 'Please fill all required fields';
          return;
        }
        if (payload.password !== payload.confirmPassword) {
          errorMessage.value = 'Passwords do not match';
          return;
        }

        const data = { name: payload.name, password: payload.password, role: payload.role };
        // detect email or phone
        const id = (payload.identifier || '').trim();
        if (id.includes('@')) {
          data.email = id;
        } else {
          data.phone = id;
        }

        const response = await axios.post('/api/register', data);
        
        if (response.data.email_sent) {
          registeredEmail.value = data.email || data.phone;
          verificationSent.value = true;
        } else {
          // If no email, auto-login
          await userStore.login({ 
            [data.email ? 'email' : 'phone']: id, 
            password: payload.password 
          });
          router.push('/');
        }
      } catch (e) {
        errorMessage.value = e.response?.data?.message || 'Registration failed';
      } finally {
        loading.value = false;
      }
    }

    function goToLogin() {
      router.push('/login');
    }

    async function resendEmail() {
      loading.value = true;
      errorMessage.value = '';
      try {
        await axios.post('/api/email/resend-verification', {
          email: registeredEmail.value
        });
        errorMessage.value = 'Verification email resent! Check your inbox.';
      } catch (e) {
        errorMessage.value = 'Failed to resend email. Try again later.';
      } finally {
        loading.value = false;
      }
    }

    return { payload, submit, loading, verificationSent, registeredEmail, goToLogin, resendEmail, errorMessage };
  }
};
</script>

<style scoped>
</style>