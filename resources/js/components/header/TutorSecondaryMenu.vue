<template>
  <div class="w-full bg-gray-50 border-b">
    <div class="max-w-7xl mx-auto">
      <!-- Desktop Menu -->
      <div class="hidden md:flex items-center justify-between px-4 py-3">
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-1">
          <img src="https://image2url.com/images/1765179057005-967d0875-ac5d-4a43-b65f-a58abd9f651d.png" 
               alt="Namate 24 Logo" 
               class="w-10 h-10 object-contain">
          <span class="text-pink-600 font-bold text-xl">Namate 24</span>
        </router-link>

        <!-- Menu Items -->
        <nav class="flex items-center gap-6">
          <router-link to="/tutor/profile/" 
                       class="text-gray-700 hover:text-pink-600 font-medium transition"
                       active-class="text-pink-600 font-semibold">
            Dashboard
          </router-link>
          
          <div class="relative group">
            <button class="text-gray-700 hover:text-pink-600 font-medium transition flex items-center gap-1">
              Edit Profile
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            
            <!-- Dropdown -->
            <div class="absolute left-0 mt-2 w-56 bg-white shadow-lg rounded-lg overflow-hidden border opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
              <router-link to="/tutor/profile/personal-details" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Personal Details</router-link>
              <router-link to="/tutor/profile/photo" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Photo</router-link>
              <router-link to="/tutor/profile/video" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Introduction Video</router-link>
              <router-link to="/tutor/profile/subjects" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Subjects</router-link>
              <router-link to="/tutor/profile/address" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Address</router-link>
              <router-link to="/tutor/profile/education" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Education</router-link>
              <router-link to="/tutor/profile/experience" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Experience</router-link>
              <router-link to="/tutor/profile/teaching-details" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Teaching Details</router-link>
              <router-link to="/tutor/profile/description" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile Description</router-link>
              <router-link to="/tutor/profile/phone" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Phone</router-link>
              <router-link to="/tutor/profile/courses" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Courses</router-link>
              <router-link to="/tutor/profile/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</router-link>
            </div>
          </div>

          <router-link to="/tutor/profile/view" 
                       class="text-gray-700 hover:text-pink-600 font-medium transition"
                       active-class="text-pink-600 font-semibold">
            View Profile
          </router-link>
        </nav>
      </div>

      <!-- Mobile Menu (shown when hamburger clicked) -->
      <div v-if="mobileMenuOpen" class="md:hidden bg-white border-t">
        <ul class="flex flex-col p-4 space-y-3">
          <li>
            <router-link to="/tutor/profile/" @click="closeMobileMenu" 
                         class="block px-4 py-2 text-gray-700 font-medium hover:bg-gray-50 rounded-md">
              Dashboard
            </router-link>
          </li>
          
          <li>
            <details class="pl-2">
              <summary class="font-medium text-gray-700 cursor-pointer">Edit Profile</summary>
              <ul class="mt-2 pl-4 space-y-2">
                <li><router-link to="/tutor/profile/personal-details" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600">Personal Details</router-link></li>
                <li><router-link to="/tutor/profile/photo" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600">Photo</router-link></li>
                <li><router-link to="/tutor/profile/video" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600">Introduction Video</router-link></li>
                <li><router-link to="/tutor/profile/subjects" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600">Subjects</router-link></li>
                <li><router-link to="/tutor/profile/address" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600">Address</router-link></li>
                <li><router-link to="/tutor/profile/education" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600">Education</router-link></li>
                <li><router-link to="/tutor/profile/experience" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600">Experience</router-link></li>
                <li><router-link to="/tutor/profile/teaching-details" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600">Teaching Details</router-link></li>
                <li><router-link to="/tutor/profile/description" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600">Profile Description</router-link></li>
                <li><router-link to="/tutor/profile/phone" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600">Phone</router-link></li>
                <li><router-link to="/tutor/profile/courses" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600">Courses</router-link></li>
                <li><router-link to="/tutor/profile/settings" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600">Settings</router-link></li>
              </ul>
            </details>
          </li>

          <li>
            <router-link to="/tutor/profile/view" @click="closeMobileMenu" 
                         class="block px-4 py-2 text-gray-700 font-medium hover:bg-gray-50 rounded-md">
              View Profile
            </router-link>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';

export default {
  name: 'TutorSecondaryMenu',
  setup() {
    const mobileMenuOpen = ref(false);

    const handleToggleMenu = () => {
      mobileMenuOpen.value = !mobileMenuOpen.value;
    };

    const closeMobileMenu = () => {
      mobileMenuOpen.value = false;
    };

    // Listen for toggle event from AuthTopBar
    onMounted(() => {
      window.addEventListener('toggle-mobile-menu', handleToggleMenu);
    });

    onUnmounted(() => {
      window.removeEventListener('toggle-mobile-menu', handleToggleMenu);
    });

    return { mobileMenuOpen, closeMobileMenu };
  }
};
</script>
