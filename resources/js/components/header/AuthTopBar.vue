<template>
  <div class="w-full bg-white border-b shadow-sm relative z-50">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-4 py-2">
      <!-- Mobile: Hamburger + Logo -->
      <div class="flex md:hidden items-center gap-2">
        <button @click="toggleMobileMenu" class="p-2 rounded-md focus:outline-none">
          <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <router-link to="/" class="flex items-center gap-1">
          <img src="https://image2url.com/images/1765179057005-967d0875-ac5d-4a43-b65f-a58abd9f651d.png" 
               alt="Logo" 
               class="w-8 h-8 object-contain">
          <span class="text-pink-600 font-bold text-sm">Namate 24</span>
        </router-link>
      </div>

      <!-- Desktop: Empty spacer -->
      <div class="hidden md:block"></div>

      <!-- Right: Notification + Avatar (Desktop & Mobile) -->
      <div class="flex items-center gap-4">
        <!-- Notification Bell -->
        <button class="relative p-2 hover:bg-gray-100 rounded-full transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14V11a6 6 0 10-12 0v3c0 .386-.149.735-.395 1.0L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <span class="absolute top-1 right-1 block w-2 h-2 rounded-full bg-red-500"></span>
        </button>

        <!-- Avatar Dropdown -->
        <div class="relative">
          <button @click="profileMenuOpen = !profileMenuOpen" 
                  class="flex items-center gap-2 px-2 py-1 rounded-full hover:bg-gray-100 transition">
            <img v-if="user?.avatar" 
                 :src="`/storage/${user.avatar}`" 
                 alt="Profile" 
                 class="w-8 h-8 md:w-10 md:h-10 rounded-full object-cover">
            <img v-else 
                 src="https://via.placeholder.com/40" 
                 alt="Profile" 
                 class="w-8 h-8 md:w-10 md:h-10 rounded-full object-cover bg-gray-200">
            <span class="hidden md:inline text-sm font-medium text-gray-700">{{ user?.name }}</span>
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Dropdown Menu -->
          <div v-if="profileMenuOpen" 
               class="absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-lg overflow-hidden border z-[999]">
            <router-link to="/profile" @click="profileMenuOpen = false" 
                         class="block px-4 py-2 text-gray-700 text-sm hover:bg-gray-100">
              My Account
            </router-link>
            <button @click="logout" 
                    class="w-full text-left px-4 py-2 text-gray-700 text-sm hover:bg-gray-100">
              Logout
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '../../store';

export default {
  name: 'AuthTopBar',
  emits: ['toggle-mobile-menu'],
  setup(props, { emit }) {
    const router = useRouter();
    const userStore = useUserStore();
    
    const profileMenuOpen = ref(false);
    const user = computed(() => userStore.user);

    const toggleMobileMenu = () => {
      window.dispatchEvent(new CustomEvent('toggle-mobile-menu'));
    };

    const logout = async () => {
      try { 
        await userStore.logout(); 
      } catch (e) {}
      
      if (userStore.setToken) userStore.setToken(null);
      if (userStore.setUser) userStore.setUser(null);
      profileMenuOpen.value = false;
      router.push('/login');
    };

    return { 
      profileMenuOpen, 
      user, 
      toggleMobileMenu,
      logout 
    };
  }
};
</script>
