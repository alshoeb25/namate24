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
                    <input name="identifier" v-model="payload.identifier" type="text" placeholder="Email"
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
              <label class="block text-gray-700 font-medium mb-2">Register as</label>
              <select v-model="payload.role" class="w-full rounded-full border border-gray-300 px-4 py-3">
                <option value="student">Student / Parent</option>
                <option value="tutor">Teacher / Tutor</option>
              </select>
              <p class="text-sm text-gray-500 mt-2">You can add the other role later from your profile</p>
            </div>

            <!-- Referral Code -->
            <div class="mt-4">
              <div class="flex items-center justify-between mb-2">
                <label class="block text-gray-700 font-medium">Referral Code (Optional)</label>
                <button 
                  v-if="payload.referralCode && !referralValidated" 
                  @click.prevent="validateReferral" 
                  type="button"
                  class="text-xs text-pink-600 font-semibold hover:underline"
                  :disabled="validatingReferral"
                >
                  {{ validatingReferral ? 'Checking...' : 'Validate' }}
                </button>
              </div>
              <div class="relative">
                <input 
                  v-model="payload.referralCode" 
                  type="text" 
                  placeholder="Enter referral code"
                  :class="[
                    'w-full rounded-full border px-4 py-3 outline-none text-gray-700 placeholder-gray-400 uppercase',
                    referralValidated ? 'border-green-500 bg-green-50' : 
                    referralError ? 'border-red-500 bg-red-50' : 'border-gray-300'
                  ]"
                  maxlength="20"
                />
                <div v-if="referralValidated" class="absolute right-4 top-3.5 text-green-600">
                  <i class="fas fa-check-circle"></i>
                </div>
                <div v-if="referralError" class="absolute right-4 top-3.5 text-red-600">
                  <i class="fas fa-times-circle"></i>
                </div>
              </div>
              
              <!-- Referral Success Message -->
              <div v-if="referralValidated && referrerInfo" class="mt-2 bg-green-50 border border-green-200 rounded-lg p-3">
                <div class="flex items-start gap-2">
                  <i class="fas fa-gift text-green-600 mt-0.5"></i>
                  <div class="text-sm">
                    <p class="text-green-800 font-semibold">
                      <template v-if="referrerInfo.type === 'user'">
                        Valid referral code from {{ referrerInfo.name }}!
                      </template>
                      <template v-else>
                        Valid admin referral code!
                      </template>
                    </p>
                    <p class="text-green-700 mt-1">
                      🎁 You'll earn <strong>{{ referrerInfo.reward?.coins || referrerInfo.reward?.referred_coins || 0 }} coins</strong> when you sign up!
                    </p>
                    <p v-if="referrerInfo.type === 'user' && referrerInfo.reward?.referrer_coins" class="text-green-700 mt-1 text-xs">
                      💝 Your referrer will also receive {{ referrerInfo.reward.referrer_coins }} bonus coins
                    </p>
                  </div>
                </div>
              </div>

              <!-- Referral Error Message -->
              <div v-if="referralError" class="mt-2 text-sm text-red-600">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ referralError }}
              </div>

              <!-- Referral Info -->
              <div v-if="!payload.referralCode" class="mt-2 text-sm text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>Have a friend's referral code? Enter it to earn bonus coins!
              </div>
            </div>

            <!-- Error message -->
            <div v-if="errorMessage" class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
              {{ errorMessage }}
            </div>

            <!-- Success message for referral -->
            <div v-if="successMessage" class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
              {{ successMessage }}
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

          <!-- Divider -->
          <div class="flex items-center gap-4 mt-6">
            <div class="flex-1 h-px bg-gray-300"></div>
            <span class="text-gray-500 text-sm">or</span>
            <div class="flex-1 h-px bg-gray-300"></div>
          </div>

          <!-- Google Signup Button -->
          <div class="mt-6">
            <GoogleLoginButton :role="payload.role" />
          </div>
        </div>

    </div>
  </div>
</template>

<script>
import { reactive, ref, onMounted, watch } from 'vue';
import { useUserStore } from '../store';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import GoogleLoginButton from '../components/GoogleLoginButton.vue';

export default {
  components: {
    GoogleLoginButton,
  },
  setup() {
    const payload = reactive({ 
      name: '', 
      identifier: '', 
      password: '', 
      confirmPassword: '', 
      role: 'student',
      referralCode: ''
    });
    const userStore = useUserStore();
    const router = useRouter();
    const route = useRoute();

    const loading = ref(false);
    const verificationSent = ref(false);
    const registeredEmail = ref('');
    const errorMessage = ref('');
    const successMessage = ref('');
    
    // Referral validation states
    const validatingReferral = ref(false);
    const referralValidated = ref(false);
    const referralError = ref('');
    const referrerInfo = ref(null);

    // Set role and referral code from URL parameters if present
    onMounted(() => {
      const type = route.query.type;
      if (type === 'tutor' || type === 'teacher') {
        payload.role = 'tutor';
      } else if (type === 'student' || type === 'parent') {
        payload.role = 'student';
      }

      // Pre-fill referral code from URL
      const ref = route.query.ref;
      if (ref) {
        payload.referralCode = ref.toUpperCase();
        // Auto-validate if provided via URL
        validateReferral();
      }
    });

    // Watch for referral code changes and reset validation
    watch(() => payload.referralCode, (newCode, oldCode) => {
      if (newCode !== oldCode) {
        referralValidated.value = false;
        referralError.value = '';
        referrerInfo.value = null;
      }
    });

    async function validateReferral() {
      if (!payload.referralCode) return;
      
      validatingReferral.value = true;
      referralError.value = '';
      referralValidated.value = false;
      referrerInfo.value = null;

      try {
        const response = await axios.post('/api/validate-referral-code', {
          referral_code: payload.referralCode.toUpperCase()
        });
        
        if (response.data.valid) {
          referralValidated.value = true;
          const data = response.data;
          
          // Handle both user and admin referral types
          if (data.type === 'user') {
            // User referral code
            referrerInfo.value = {
              name: data.referrer?.name || 'Your Friend',
              referral_code: data.referral_code,
              type: 'user',
              reward: data.reward
            };
          } else if (data.type === 'admin') {
            // Admin referral code
            referrerInfo.value = {
              name: 'NaMate24',
              referral_code: data.referral_code,
              type: 'admin',
              reward: {
                coins: data.reward?.coins || 0,
                type: data.reward?.type || 'admin'
              }
            };
          }
        }
      } catch (e) {
        referralError.value = e.response?.data?.message || 'Invalid referral code';
        referralValidated.value = false;
      } finally {
        validatingReferral.value = false;
      }
    }

    async function submit() {
      errorMessage.value = '';
      successMessage.value = '';
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
        
        // Add referral code if provided
        if (payload.referralCode) {
          data.referral_code = payload.referralCode.toUpperCase();
        }
        
        // detect email or phone
        const id = (payload.identifier || '').trim();
        if (id.includes('@')) {
          data.email = id;
        } else {
          data.phone = id;
        }

        const response = await axios.post('/api/register', data);
        
        // Show success message if referral was applied
        if (response.data.referral_applied && response.data.referral_reward) {
          successMessage.value = `🎉 You earned ${response.data.referral_reward.coins} coins from ${response.data.referral_reward.referrer_name}'s referral!`;
        }
        
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

    return { 
      payload, 
      submit, 
      loading, 
      verificationSent, 
      registeredEmail, 
      goToLogin, 
      resendEmail, 
      errorMessage,
      successMessage,
      validateReferral,
      validatingReferral,
      referralValidated,
      referralError,
      referrerInfo
    };
  }
};
</script>

<style scoped>
</style>