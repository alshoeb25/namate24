<template>
  <header class="w-full bg-white shadow-md relative z-50">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-4 py-3">
      <!-- Logo -->
      <router-link to="/" class="flex items-center gap-1">
        <img src="https://image2url.com/images/1765179057005-967d0875-ac5d-4a43-b65f-a58abd9f651d.png" 
             alt="Namate 24 Logo" 
             class="w-10 h-10 object-contain">
        <span class="text-pink-600 font-bold text-lg md:text-xl">Namate 24</span>
      </router-link>

      <!-- Desktop: Menu Items + Login -->
      <div class="hidden md:flex items-center gap-6">
        <router-link to="/" class="text-gray-700 hover:text-pink-600 font-medium">Home</router-link>
        <router-link to="/tutors" class="text-gray-700 hover:text-pink-600 font-medium">Find Tutors</router-link>
        <router-link to="/login" class="bg-pink-600 text-white px-6 py-2 rounded-md font-medium hover:bg-pink-700 transition">
          Login
        </router-link>
      </div>

      <!-- Mobile: Hamburger + Login -->
      <div class="flex md:hidden items-center gap-4">
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-md focus:outline-none">
          <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <router-link to="/login" class="bg-pink-600 text-white px-4 py-2 rounded-md text-sm font-medium">
          Login
        </router-link>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div v-if="mobileMenuOpen" class="md:hidden bg-white shadow-md border-t">
      <ul class="flex flex-col p-4 space-y-3">
        <li>
          <router-link to="/" @click="mobileMenuOpen = false" 
                       class="block px-4 py-2 text-gray-700 font-medium hover:bg-gray-50 rounded-md">
            Home
          </router-link>
        </li>
        <li>
          <router-link to="/tutors" @click="mobileMenuOpen = false" 
                       class="block px-4 py-2 text-gray-700 font-medium hover:bg-gray-50 rounded-md">
            Find Tutors
          </router-link>
        </li>
        <li>
          <router-link to="/register" @click="mobileMenuOpen = false" 
                       class="block px-4 py-2 text-gray-700 font-medium hover:bg-gray-50 rounded-md">
            Register
          </router-link>
        </li>
      </ul>
    </div>
  </header>
</template>

<script>
import { ref } from 'vue';

export default {
  name: 'GuestHeader',
  setup() {
    const mobileMenuOpen = ref(false);
    return { mobileMenuOpen };
  }
};
</script>
