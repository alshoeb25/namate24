<template>
  <button 
    @click="handleGoogleLogin" 
    :disabled="loading"
    class="w-full flex items-center justify-center gap-3 px-4 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
    <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
      <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
      <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
      <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
    </svg>
    <span class="font-medium text-gray-700">
      {{ loading ? 'Signing in...' : 'Continue with Google' }}
    </span>
  </button>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '../store';
import axios from 'axios';

const props = defineProps({
  role: {
    type: String,
    default: 'student',
    validator: (value) => ['student', 'tutor'].includes(value)
  }
});

const router = useRouter();
const userStore = useUserStore();
const loading = ref(false);

// Load Google Sign-In library
const loadGoogleScript = () => {
  return new Promise((resolve, reject) => {
    if (window.google) {
      resolve();
      return;
    }

    const script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    script.async = true;
    script.defer = true;
    script.onload = resolve;
    script.onerror = reject;
    document.head.appendChild(script);
  });
};

const handleGoogleLogin = async () => {
  if (loading.value) return;
  
  loading.value = true;

  try {
    await loadGoogleScript();

    // Initialize Google Sign-In
    window.google.accounts.id.initialize({
      client_id: import.meta.env.VITE_GOOGLE_CLIENT_ID,
      callback: handleCredentialResponse,
    });

    // Trigger the sign-in prompt
    window.google.accounts.id.prompt((notification) => {
      if (notification.isNotDisplayed() || notification.isSkippedMoment()) {
        // Fallback to popup if prompt is not displayed
        window.google.accounts.id.renderButton(
          document.getElementById('google-signin-button'),
          { theme: 'outline', size: 'large', width: 300 }
        );
      }
    });

  } catch (error) {
    console.error('Error loading Google Sign-In:', error);
    alert('Failed to load Google Sign-In. Please try again.');
    loading.value = false;
  }
};

const handleCredentialResponse = async (response) => {
  try {
    // Decode JWT to get user info
    const userInfo = JSON.parse(atob(response.credential.split('.')[1]));

    // Send to backend
    const res = await axios.post('/api/auth/google/callback', {
      access_token: response.credential,
      role: props.role,
      email: userInfo.email,
      name: userInfo.name,
      picture: userInfo.picture,
    });

    // Store token and user
    localStorage.setItem('token', res.data.token);
    axios.defaults.headers.common['Authorization'] = `Bearer ${res.data.token}`;
    
    userStore.user = res.data.user;

    // Redirect
    if (res.data.redirect_url) {
      router.push(res.data.redirect_url);
    } else {
      router.push('/');
    }

  } catch (error) {
    console.error('Google login error:', error);
    alert(error.response?.data?.message || 'Google login failed. Please try again.');
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
#google-signin-button {
  display: none;
}
</style>
