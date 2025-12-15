<template>
  <div class="w-full min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="w-full bg-white shadow-md">
      <div class="max-w-7xl mx-auto flex items-center justify-between px-4 py-3">
        <router-link to="/" class="flex items-center gap-1">
          <img src="https://image2url.com/images/1765179057005-967d0875-ac5d-4a43-b65f-a58abd9f651d.png"
            alt="Namate 24 Logo" class="w-10 h-10 object-contain">
          <span class="text-pink-600 font-bold text-lg md:text-xl">Namate 24</span>
        </router-link>

        <!-- Right: Profile + Hamburger -->
        <div class="flex items-center gap-4">
          <div class="relative">
            <button @click="profileMenuOpen = !profileMenuOpen"
              class="w-10 h-10 rounded-full object-cover bg-gray-200 flex items-center justify-center">
              <svg v-if="!isAuthenticated" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" />
              </svg>
            </button>
            <div v-if="profileMenuOpen"
              class="absolute right-0 mt-2 w-40 bg-white shadow-lg rounded-lg overflow-hidden border z-50">
              <router-link v-if="!isAuthenticated" to="/login"
                class="block text-center bg-gradient-to-r from-pink-500 to-pink-600 text-white
                  font-medium text-sm py-2 hover:opacity-90">
                Sign in / Sign up
              </router-link>
              <div v-else class="py-2">
                <button @click="logout" class="w-full text-left px-4 py-2 text-gray-700 text-sm hover:bg-gray-100">
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Search Results -->
    <main class="max-w-7xl mx-auto px-4 py-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Search Tutors</h2>

      <!-- Filter Box -->
      <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex gap-4 flex-col md:flex-row">
          <input v-model="searchQuery" type="text" placeholder="Search by name or subject..."
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg outline-none focus:border-blue-500">
          <button @click="search" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Search
          </button>
        </div>
      </div>

      <!-- Results Grid -->
      <div v-if="loading" class="text-center py-8">
        <p class="text-gray-500">Loading tutors...</p>
      </div>

      <div v-else-if="tutors.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="tutor in tutors" :key="tutor.id"
          class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition">
          <img v-if="tutor.avatar" :src="tutor.avatar" class="w-full h-48 object-cover rounded-lg mb-4">
          <h3 class="font-bold text-lg text-gray-900">{{ tutor.name }}</h3>
          <p class="text-sm text-gray-600 mb-3">{{ tutor.tutor?.headline || 'Experienced Tutor' }}</p>
          <p v-if="tutor.tutor?.price_per_hour" class="text-blue-600 font-semibold mb-3">
            {{ tutor.tutor.price_per_hour }} / hour
          </p>
          <router-link :to="`/tutor/${tutor.id}`"
            class="block text-center bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            View Profile
          </router-link>
        </div>
      </div>

      <div v-else class="text-center py-12">
        <p class="text-gray-500 text-lg">No tutors found. Try a different search.</p>
      </div>
    </main>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useUserStore } from '../store';
import axios from 'axios';

export default {
  name: 'Search',
  setup() {
    const route = useRoute();
    const userStore = useUserStore();

    const profileMenuOpen = ref(false);
    const searchQuery = ref('');
    const tutors = ref([]);
    const loading = ref(false);

    const isAuthenticated = computed(() => userStore.token !== null);

    const search = async () => {
      loading.value = true;
      try {
        const params = {
          q: searchQuery.value || route.query.subject || '',
          location: route.query.location || '',
        };
        const res = await axios.get('/api/tutors', { params });
        tutors.value = res.data.data || [];
      } catch (error) {
        console.error('Search error:', error);
        tutors.value = [];
      } finally {
        loading.value = false;
      }
    };

    const logout = async () => {
      await userStore.logout();
      profileMenuOpen.value = false;
    };

    onMounted(() => {
      searchQuery.value = route.query.subject || '';
      search();
    });

    return {
      profileMenuOpen,
      searchQuery,
      tutors,
      loading,
      isAuthenticated,
      search,
      logout,
    };
  }
};
</script>
