<template>
  <div class="min-h-screen bg-gray-50">
    
    <div class="max-w-4xl mx-auto px-4 py-8">
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
            <!-- Country Code Dropdown -->
            <select v-model="form.country_code" 
                    class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-pink-500 focus:border-transparent min-w-[140px]">
              <option v-for="country in countryCodes" :key="country.iso" :value="country.code">
                {{ country.flag }} {{ country.code }}
              </option>
            </select>
            
            <input v-model="form.phone" 
                   type="tel" 
                   placeholder="9876543210"
                   class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                   :class="{'border-red-500': errors.phone}">
          </div>
          <p v-if="errors.phone" class="text-red-500 text-sm mt-1">{{ errors.phone }}</p>
          <p v-if="user?.phone_verified_at" class="text-green-600 text-sm mt-1">
            <i class="fas fa-check-circle mr-1"></i>Phone verified
          </p>
        </div>

        <!-- Save Button -->
        <button @click="saveProfile" 
                :disabled="saving"
                class="w-full bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-lg font-semibold transition disabled:opacity-50">
          <i class="fas fa-save mr-2"></i>{{ saving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>

      <!-- Location Information -->
      <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-map-marker-alt mr-2 text-pink-600"></i>Location
        </h2>

        <!-- Google Maps Autocomplete -->
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-2">
            <i class="fas fa-search mr-1"></i>Search Location via Google Maps
          </label>
          <input ref="locationInput"
                 type="text" 
                 placeholder="Search for your location..."
                 class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
          <p class="text-sm text-gray-500 mt-2">
            <i class="fas fa-info-circle mr-1"></i>Search and select from Google Maps to auto-fill fields below, or enter manually
          </p>
        </div>

        <!-- Manual Entry Fields (also auto-filled by Google Maps) -->
        <div class="space-y-4">
          <div>
            <label class="block text-gray-700 font-medium mb-2">
              City<span class="text-red-500">*</span>
            </label>
            <input v-model="form.location.city" 
                   type="text" 
                   placeholder="Enter your city"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                   :class="{'border-red-500': errors.city}">
            <p v-if="errors.city" class="text-red-500 text-sm mt-1">{{ errors.city }}</p>
          </div>

          <div>
            <label class="block text-gray-700 font-medium mb-2">
              Area/Locality<span class="text-red-500">*</span>
            </label>
            <input v-model="form.location.area" 
                   type="text" 
                   placeholder="Enter your area or locality"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                   :class="{'border-red-500': errors.area}">
            <p v-if="errors.area" class="text-red-500 text-sm mt-1">{{ errors.area }}</p>
          </div>

          <div>
            <label class="block text-gray-700 font-medium mb-2">
              Full Address
            </label>
            <textarea v-model="form.location.address" 
                      rows="3"
                      placeholder="Enter your complete address"
                      class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-pink-500 focus:border-transparent"></textarea>
            <p class="text-sm text-gray-500 mt-1">
              <i class="fas fa-info-circle mr-1"></i>Optional: Provide detailed address
            </p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-medium mb-2">
                Latitude
              </label>
              <input v-model="form.location.lat" 
                     type="text" 
                     placeholder="e.g., 19.0760"
                     class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
            </div>
            <div>
              <label class="block text-gray-700 font-medium mb-2">
                Longitude
              </label>
              <input v-model="form.location.lng" 
                     type="text" 
                     placeholder="e.g., 72.8777"
                     class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
            </div>
          </div>
          <p class="text-sm text-gray-500">
            <i class="fas fa-info-circle mr-1"></i>Optional: Add coordinates for precise location
          </p>
        </div>

        <!-- Save Location Button -->
        <button @click="saveLocation" 
                :disabled="savingLocation"
                class="w-full mt-4 bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-lg font-semibold transition disabled:opacity-50">
          <i class="fas fa-save mr-2"></i>{{ savingLocation ? 'Saving...' : 'Save Location' }}
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
import { ref, reactive, computed, onMounted, nextTick } from 'vue';
import { useUserStore } from '../store';
import axios from '../bootstrap';
import HeaderRoot from '../components/header/HeaderRoot.vue';
import { countryCodes } from '../utils/countryCodes';
import { loadGoogleMaps } from '../utils/googleMaps';

export default {
  name: 'ProfileManagement',
  components: {
    HeaderRoot
  },
  setup() {
    const userStore = useUserStore();
    const user = computed(() => userStore.user);

    const form = reactive({
      name: '',
      email: '',
      phone: '',
      country_code: '+91',
      location: {
        city: '',
        area: '',
        address: '',
        lat: null,
        lng: null,
      }
    });

    const errors = reactive({
      name: '',
      email: '',
      phone: '',
      city: '',
      area: '',
    });

    const profilePhotoPreview = ref(null);
    const profilePhotoFile = ref(null);
    const uploadingPhoto = ref(false);
    const saving = ref(false);
    const savingLocation = ref(false);
    const emailVerificationSent = ref(false);
    const successMessage = ref('');
    const locationInput = ref(null);
    let autocomplete = null;

    onMounted(async () => {
      if (user.value) {
        form.name = user.value.name || '';
        form.email = user.value.email || '';
        form.phone = user.value.phone || '';
        form.country_code = user.value.country_code || '+91';
        
        // Load location data from user first, fallback to tutor/student profile
        form.location.city = user.value.city || user.value.tutor?.city || user.value.student?.city || '';
        form.location.area = user.value.area || user.value.tutor?.area || user.value.student?.area || '';
        form.location.address = user.value.address || user.value.tutor?.address || user.value.student?.address || '';
        form.location.lat = user.value.lat || user.value.tutor?.lat || user.value.student?.lat || null;
        form.location.lng = user.value.lng || user.value.tutor?.lng || user.value.student?.lng || null;
      }
      
      try {
        await loadGoogleMaps();
        await nextTick();
        initAutocomplete();
      } catch (error) {
        console.error('Failed to load Google Maps', error);
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

    const initAutocomplete = () => {
      if (!locationInput.value || !window.google?.maps?.places?.Autocomplete) {
        console.warn('Google Maps Places API not available');
        return;
      }

      autocomplete = new window.google.maps.places.Autocomplete(locationInput.value, {
        types: ['geocode'],
        componentRestrictions: { country: 'in' } // Restrict to India, change as needed
      });

      autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        
        if (!place.geometry) {
          return;
        }

        // Extract location details
        let city = '';
        let area = '';
        
        place.address_components.forEach(component => {
          if (component.types.includes('locality')) {
            city = component.long_name;
          }
          if (component.types.includes('sublocality') || component.types.includes('sublocality_level_1')) {
            area = component.long_name;
          }
        });

        form.location.address = place.formatted_address;
        form.location.city = city;
        form.location.area = area;
        form.location.lat = place.geometry.location.lat();
        form.location.lng = place.geometry.location.lng();
      });
    };

    const validateLocation = () => {
      errors.city = '';
      errors.area = '';
      let valid = true;

      if (!form.location.city || form.location.city.trim().length < 2) {
        errors.city = 'City is required';
        valid = false;
      }

      if (!form.location.area || form.location.area.trim().length < 2) {
        errors.area = 'Area/Locality is required';
        valid = false;
      }

      return valid;
    };

    const saveLocation = async () => {
      if (!validateLocation()) return;

      savingLocation.value = true;
      try {
        await axios.put('/api/profile/location', {
          city: form.location.city,
          area: form.location.area,
          address: form.location.address,
          lat: form.location.lat,
          lng: form.location.lng,
        });
        await userStore.fetchUser();
        successMessage.value = 'Location updated successfully!';
        setTimeout(() => successMessage.value = '', 3000);
      } catch (error) {
        if (error.response?.data?.errors) {
          Object.assign(errors, error.response.data.errors);
        } else {
          alert('Error updating location. Please try again.');
        }
      } finally {
        savingLocation.value = false;
      }
    };

    const saveProfile = async () => {
      if (!validateForm()) return;

      saving.value = true;
      try {
        await axios.put('/api/profile', {
          name: form.name,
          email: form.email,
          phone: form.phone,
          country_code: form.country_code,
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
      saving,
      savingLocation,
      emailVerificationSent,
      successMessage,
      locationInput,
      countryCodes,
      getProfilePhoto,
      handlePhotoUpload,
      saveProfilePhoto,
      sendEmailVerification,
      saveProfile,
      saveLocation,
    };
  }
};
</script>
