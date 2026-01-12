<template>
  <header class="w-full bg-white shadow-md relative z-60">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-4 py-3">
      <router-link to="/" class="flex items-center gap-1">
        <img :src="'/storage/logo.png'" alt="Namate 24 Logo" class="h-10 sm:h-11 md:h-12 lg:h-14 w-auto object-contain">
      </router-link>

      <div class="hidden md:flex items-center gap-4">
        <button class="relative">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14V11a6 6 0 10-12 0v3c0 .386-.149.735-.395 1.0L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <span class="absolute top-0 right-0 block w-2 h-2 rounded-full bg-red-500"></span>
        </button>

        <div class="relative">
          <button @click="profileMenuOpen = !profileMenuOpen" class="flex items-center gap-2 px-2 py-1 rounded-full hover:bg-gray-100 transition">
            <img v-if="isAuthenticated && user?.avatar" :src="`/storage/${user.avatar}`" alt="Profile" class="w-10 h-10 rounded-full object-cover">
            <img v-else class="w-10 h-10 rounded-full object-cover bg-gray-200" src="https://via.placeholder.com/40" alt="Profile">
            <span v-if="isAuthenticated" class="hidden md:inline text-sm font-medium text-gray-700">{{ user?.name }}</span>
          </button>

          <div v-if="profileMenuOpen" class="absolute right-0 mt-2 w-44 bg-white shadow-lg rounded-lg overflow-hidden border z-[999]">
            <div v-if="!isAuthenticated" class="py-2">
              <router-link to="/login" @click="profileMenuOpen = false" class="block px-4 py-2 text-gray-700 text-sm hover:bg-gray-100 font-medium">Login</router-link>
              <router-link to="/register" @click="profileMenuOpen = false" class="block px-4 py-2 text-gray-700 text-sm hover:bg-gray-100 font-medium">Register</router-link>
            </div>
            <div v-else class="py-2">
              <router-link to="/profile" @click="profileMenuOpen = false" class="block px-4 py-2 text-gray-700 text-sm hover:bg-gray-100">My Account</router-link>
              <button @click="logout" class="w-full text-left px-4 py-2 text-gray-700 text-sm hover:bg-gray-100">Logout</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile top bar: hamburger left, action right -->
      <div class="flex md:hidden items-center justify-between w-full">
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 ml-2 rounded-md focus:outline-none">
          <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <div class="mr-2">
          <router-link v-if="!isAuthenticated" to="/login" class="bg-pink-500 text-white px-3 py-1 rounded-md text-sm font-medium">Login</router-link>

          <div v-else class="relative inline-block">
            <button @click="profileMenuOpen = !profileMenuOpen" class="flex items-center gap-2 px-2 py-1 rounded-full hover:bg-gray-100 transition">
              <img v-if="user?.avatar" :src="`/storage/${user.avatar}`" alt="Profile" class="w-8 h-8 rounded-full object-cover">
              <img v-else class="w-8 h-8 rounded-full object-cover bg-gray-200" src="https://via.placeholder.com/40" alt="Profile">
              <span class="text-sm font-medium text-gray-700">{{ user?.name }}</span>
            </button>

            <div v-if="profileMenuOpen" class="absolute right-0 mt-2 w-44 bg-white shadow-lg rounded-lg overflow-hidden border z-[999]">
              <router-link to="/profile" @click="profileMenuOpen = false" class="block px-4 py-2 text-gray-700 text-sm hover:bg-gray-100">My Account</router-link>
              <button @click="logout" class="w-full text-left px-4 py-2 text-gray-700 text-sm hover:bg-gray-100">Logout</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="mobileMenuOpen" class="md:hidden bg-white shadow-md border-t">
      <ul class="flex flex-col p-4 space-y-3 text-gray-700 font-medium">
        <template v-if="!isAuthenticated">
          <li>
            <router-link to="/login" @click="mobileMenuOpen = false" class="block px-4 py-2 rounded-md hover:bg-gray-50">Login</router-link>
          </li>
          <li>
            <router-link to="/register" @click="mobileMenuOpen = false" class="block px-4 py-2 rounded-md hover:bg-gray-50">Register</router-link>
          </li>
        </template>
        <template v-else-if="user && user.role === 'tutor'">
          <li>
            <router-link to="/tutor/profile" @click="mobileMenuOpen = false" class="block px-4 py-2 rounded-md hover:bg-gray-50">Dashboard</router-link>
          </li>
          <li>
            <details class="pl-2">
              <summary class="font-medium">Edit Profile</summary>
              <ul class="mt-2 pl-4 space-y-2">
                <li><router-link to="/tutor/profile/personal-details" @click="mobileMenuOpen = false" class="block">Personal Details</router-link></li>
                <li><router-link to="/tutor/profile/photo" @click="mobileMenuOpen = false" class="block">Photo</router-link></li>
                <li><router-link to="/tutor/profile/video" @click="mobileMenuOpen = false" class="block">Introduction Video</router-link></li>
                <li><router-link to="/tutor/profile/subjects" @click="mobileMenuOpen = false" class="block">Subjects</router-link></li>
                <router-link to="/tutor/profile/address" @click="mobileMenuOpen = false" class="block">Address</router-link>
              <router-link to="/tutor/profile/education" @click="mobileMenuOpen = false" class="block">Education</router-link>
              <router-link to="/tutor/profile/experience" @click="mobileMenuOpen = false" class="block">Experience</router-link>
              <router-link to="/tutor/profile/teaching-details" @click="mobileMenuOpen = false" class="block">Teaching Details</router-link>
              <router-link to="/tutor/profile/description" @click="mobileMenuOpen = false" class="block">Profile Description</router-link>
              <router-link to="/tutor/profile/phone" @click="mobileMenuOpen = false" class="block">Phone</router-link>
              <router-link to="/tutor/profile/courses" @click="mobileMenuOpen = false" class="block">Courses</router-link>
              <router-link to="/tutor/profile/settings" @click="mobileMenuOpen = false" class="block">Settings</router-link>
              </ul>
            </details>
          </li>
          <li>
            <router-link to="/tutor/profile/view" @click="mobileMenuOpen = false" class="block px-4 py-2 rounded-md hover:bg-gray-50">View Profile</router-link>
          </li>
        </template>
        <template v-else>
          <li class="hover:text-pink-600 cursor-pointer">Home</li>
        </template>
      </ul>
    </div>
  </header>
</template>

<script>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '../store';

export default {
  name: 'CommonHeader',
  setup() {
    const router = useRouter();
    const userStore = useUserStore();

    const profileMenuOpen = ref(false);
    const mobileMenuOpen = ref(false);

    const isAuthenticated = computed(() => userStore.token !== null);
    const user = computed(() => userStore.user);
    const logout = async () => {
      // Call store logout (clears server session if available)
      try { await userStore.logout(); } catch (e) {}
      // Ensure client-side token and user cleared immediately
      if (userStore.setToken) userStore.setToken(null);
      if (userStore.setUser) userStore.setUser(null);
      profileMenuOpen.value = false;
      router.push('/login');
    };

    return { profileMenuOpen, mobileMenuOpen, isAuthenticated, user, logout };
  }
};

</script>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-hidden::-webkit-scrollbar {
  display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-hidden {
  -ms-overflow-style: none; /* IE and Edge */
  scrollbar-width: none; /* Firefox */
}
</style>
