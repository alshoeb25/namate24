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
          <router-link to="/student/dashboard" 
                       class="text-gray-700 hover:text-pink-600 font-medium transition"
                       active-class="text-pink-600 font-semibold">
            Dashboard
          </router-link>
          
          <router-link to="/tutors" 
                       class="text-gray-700 hover:text-pink-600 font-medium transition"
                       active-class="text-pink-600 font-semibold">
            Find Tutors
          </router-link>

          <router-link to="/student/bookings" 
                       class="text-gray-700 hover:text-pink-600 font-medium transition"
                       active-class="text-pink-600 font-semibold">
            My Bookings
          </router-link>

          <router-link to="/student/requirements" 
                       class="text-gray-700 hover:text-pink-600 font-medium transition"
                       active-class="text-pink-600 font-semibold">
            My Requirements
          </router-link>
        </nav>
      </div>

      <!-- Mobile Menu (shown when hamburger clicked) -->
      <div v-if="mobileMenuOpen" class="md:hidden bg-white border-t">
        <ul class="flex flex-col p-4 space-y-3">
          <li>
            <router-link to="/student/dashboard" @click="closeMobileMenu" 
                         class="block px-4 py-2 text-gray-700 font-medium hover:bg-gray-50 rounded-md">
              Dashboard
            </router-link>
          </li>
          
          <li>
            <router-link to="/tutors" @click="closeMobileMenu" 
                         class="block px-4 py-2 text-gray-700 font-medium hover:bg-gray-50 rounded-md">
              Find Tutors
            </router-link>
          </li>

          <li>
            <router-link to="/student/bookings" @click="closeMobileMenu" 
                         class="block px-4 py-2 text-gray-700 font-medium hover:bg-gray-50 rounded-md">
              My Bookings
            </router-link>
          </li>

          <li>
            <router-link to="/student/requirements" @click="closeMobileMenu" 
                         class="block px-4 py-2 text-gray-700 font-medium hover:bg-gray-50 rounded-md">
              My Requirements
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
  name: 'StudentSecondaryMenu',
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
