<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
      <!-- Header -->
      <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
          <i class="fas fa-user-circle mr-2 text-pink-600"></i>My Profile
        </h1>
        <p class="text-gray-600">Manage your account settings and profile information</p>
      </div>

      <!-- Profile Photo Section -->
      <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-camera mr-2 text-pink-600"></i>Profile Picture
        </h2>
        <div class="flex items-center gap-6">
          <div class="relative">
            <img :src="profilePhotoPreview || getProfilePhoto()" 
                 alt="Profile" 
                 class="w-32 h-32 rounded-full object-cover border-4 border-pink-100">
            <label for="photo-upload" class="absolute bottom-0 right-0 bg-pink-600 hover:bg-pink-700 text-white p-2 rounded-full cursor-pointer shadow-lg transition" :class="{'opacity-50 cursor-not-allowed': uploadingPhoto}">
              <i class="fas fa-camera text-sm"></i>
            </label>
            <input type="file" 
                   id="photo-upload" 
                   @change="handlePhotoUpload" 
                   accept="image/*" 
                   :disabled="uploadingPhoto"
                   class="hidden">
          </div>
          <div>
            <p class="text-gray-600 mb-2">Upload a professional profile picture</p>
            <p class="text-sm text-gray-500">JPG, PNG or GIF. Max size 2MB</p>
            <button v-if="profilePhotoPreview" 
                    @click="saveProfilePhoto" 
                    :disabled="uploadingPhoto"
                    class="mt-3 bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
              <i class="fas mr-2" :class="uploadingPhoto ? 'fa-spinner fa-spin' : 'fa-save'"></i>{{ uploadingPhoto ? 'Uploading...' : 'Save Photo' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Personal Information -->
      <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-id-card mr-2 text-pink-600"></i>Personal Information
        </h2>
        
        <!-- Name -->
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-2">
            Full Name<span class="text-red-500">*</span>
          </label>
          <input v-model="form.name" 
                 type="text" 
                 class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                 :class="{'border-red-500': errors.name}">
          <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
        </div>

        <!-- Email -->
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-2">
            Email Address<span class="text-red-500">*</span>
          </label>
          <div class="flex gap-2">
            <input v-model="form.email" 
                   type="email" 
                   class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                   :class="{'border-red-500': errors.email}">
            <button v-if="form.email !== user?.email && !emailVerificationSent" 
                    @click="sendEmailVerification" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition whitespace-nowrap">
              <i class="fas fa-envelope mr-2"></i>Verify
            </button>
          </div>
          <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
          <p v-if="emailVerificationSent" class="text-green-600 text-sm mt-1">
            <i class="fas fa-check-circle mr-1"></i>Verification email sent! Please check your inbox.
          </p>
          <p v-if="user?.email_verified_at" class="text-green-600 text-sm mt-1">
            <i class="fas fa-check-circle mr-1"></i>Email verified
          </p>
        </div>

        <!-- Phone -->
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-2">
            Phone Number<span class="text-red-500">*</span>
          </label>
          <div class="flex gap-2">
            <input v-model="form.phone" 
                   type="tel" 
                   placeholder="+91 9876543210"
                   class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                   :class="{'border-red-500': errors.phone}">
            <button v-if="form.phone !== user?.phone && !showOtpInput" 
                    @click="sendOtp" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition whitespace-nowrap">
              <i class="fas fa-sms mr-2"></i>Send OTP
            </button>
          </div>
          <p v-if="errors.phone" class="text-red-500 text-sm mt-1">{{ errors.phone }}</p>
          <p v-if="user?.phone_verified_at" class="text-green-600 text-sm mt-1">
            <i class="fas fa-check-circle mr-1"></i>Phone verified
          </p>
        </div>

        <!-- OTP Verification -->
        <div v-if="showOtpInput" class="mb-4 p-4 bg-blue-50 rounded-lg">
          <label class="block text-gray-700 font-medium mb-2">
            Enter OTP<span class="text-red-500">*</span>
          </label>
          <div class="flex gap-2">
            <input v-model="otpCode" 
                   type="text" 
                   placeholder="Enter 6-digit OTP"
                   maxlength="6"
                   class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <button @click="verifyOtp" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition whitespace-nowrap">
              <i class="fas fa-check mr-2"></i>Verify
            </button>
          </div>
          <p class="text-sm text-gray-600 mt-2">
            OTP sent to {{ form.phone }}. 
            <button @click="sendOtp" class="text-blue-600 hover:underline">Resend OTP</button>
          </p>
        </div>

        <!-- Save Button -->
        <button @click="saveProfile" 
                :disabled="saving"
                class="w-full bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-lg font-semibold transition disabled:opacity-50">
          <i class="fas fa-save mr-2"></i>{{ saving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>

      <!-- Success Message -->
      <div v-if="successMessage" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle mr-2"></i>{{ successMessage }}
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useUserStore } from '../store';
import axios from '../bootstrap';

export default {
  name: 'ProfileManagement',
  setup() {
    const userStore = useUserStore();
    const user = computed(() => userStore.user);

    const form = reactive({
      name: '',
      email: '',
      phone: '',
    });

    const errors = reactive({
      name: '',
      email: '',
      phone: '',
    });

    const profilePhotoPreview = ref(null);
    const profilePhotoFile = ref(null);
    const uploadingPhoto = ref(false);
    const showOtpInput = ref(false);
    const otpCode = ref('');
    const saving = ref(false);
    const emailVerificationSent = ref(false);
    const successMessage = ref('');

    onMounted(() => {
      if (user.value) {
        form.name = user.value.name || '';
        form.email = user.value.email || '';
        form.phone = user.value.phone || '';
      }
    });

    const getProfilePhoto = () => {
    return (
      user.value?.avatar_url ||
      user.value?.tutor?.photo_url ||
      'https://via.placeholder.com/40'
    );
  };

    const handlePhotoUpload = (event) => {
      const file = event.target.files[0];
      if (file) {
        if (file.size > 2 * 1024 * 1024) {
          alert('File size must be less than 2MB');
          return;
        }
        profilePhotoFile.value = file;
        const reader = new FileReader();
        reader.onload = (e) => {
          profilePhotoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    };

    const saveProfilePhoto = async () => {
      if (!profilePhotoFile.value) return;

      uploadingPhoto.value = true;
      const formData = new FormData();
      formData.append('photo', profilePhotoFile.value);

      try {
        const res = await axios.post('/api/profile/photo', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        await userStore.fetchUser();
        profilePhotoPreview.value = null;
        profilePhotoFile.value = null;
        successMessage.value = 'Profile photo updated successfully!';
        setTimeout(() => successMessage.value = '', 3000);
      } catch (error) {
        alert('Error uploading photo. Please try again.');
      } finally {
        uploadingPhoto.value = false;
      }
    };

    const sendEmailVerification = async () => {
      try {
        await axios.post('/api/profile/email/verification', { email: form.email });
        emailVerificationSent.value = true;
        setTimeout(() => emailVerificationSent.value = false, 5000);
      } catch (error) {
        errors.email = error.response?.data?.message || 'Error sending verification email';
      }
    };

    const sendOtp = async () => {
      if (!form.phone) {
        errors.phone = 'Phone number is required';
        return;
      }
      try {
        await axios.post('/api/profile/phone/otp', { phone: form.phone });
        showOtpInput.value = true;
        errors.phone = '';
      } catch (error) {
        errors.phone = error.response?.data?.message || 'Error sending OTP';
      }
    };

    const verifyOtp = async () => {
      if (!otpCode.value || otpCode.value.length !== 6) {
        alert('Please enter a valid 6-digit OTP');
        return;
      }
      try {
        await axios.post('/api/profile/phone/verify', { 
          phone: form.phone, 
          otp: otpCode.value 
        });
        await userStore.fetchUser();
        showOtpInput.value = false;
        otpCode.value = '';
        successMessage.value = 'Phone number verified successfully!';
        setTimeout(() => successMessage.value = '', 3000);
      } catch (error) {
        alert(error.response?.data?.message || 'Invalid OTP. Please try again.');
      }
    };

    const validateForm = () => {
      let valid = true;
      errors.name = '';
      errors.email = '';
      errors.phone = '';

      if (!form.name || form.name.trim().length < 2) {
        errors.name = 'Name must be at least 2 characters';
        valid = false;
      }

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!form.email || !emailRegex.test(form.email)) {
        errors.email = 'Please enter a valid email address';
        valid = false;
      }

      const phoneRegex = /^[+]?[\d\s-]{10,15}$/;
      if (!form.phone || !phoneRegex.test(form.phone)) {
        errors.phone = 'Please enter a valid phone number';
        valid = false;
      }

      return valid;
    };

    const saveProfile = async () => {
      if (!validateForm()) return;

      saving.value = true;
      try {
        await axios.put('/api/profile', {
          name: form.name,
          email: form.email,
          phone: form.phone,
        });
        await userStore.fetchUser();
        successMessage.value = 'Profile updated successfully!';
        setTimeout(() => successMessage.value = '', 3000);
      } catch (error) {
        if (error.response?.data?.errors) {
          Object.assign(errors, error.response.data.errors);
        } else {
          alert('Error updating profile. Please try again.');
        }
      } finally {
        saving.value = false;
      }
    };

    return {
      user,
      form,
      errors,
      profilePhotoPreview,
      uploadingPhoto,
      showOtpInput,
      otpCode,
      saving,
      emailVerificationSent,
      successMessage,
      getProfilePhoto,
      handlePhotoUpload,
      saveProfilePhoto,
      sendEmailVerification,
      sendOtp,
      verifyOtp,
      saveProfile,
    };
  }
};
</script>
