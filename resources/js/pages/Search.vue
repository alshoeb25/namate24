<template>
  <div class="w-full min-h-screen bg-gray-50">
    <!-- Header -->
    <HeaderRoot />

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
import HeaderRoot from '../components/header/HeaderRoot.vue';

export default {
  name: 'Search',
  components: {
    HeaderRoot
  },
  setup() {
    const route = useRoute();
    const userStore = useUserStore();

    const searchQuery = ref('');
    const tutors = ref([]);
    const loading = ref(false);

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

    onMounted(() => {
      searchQuery.value = route.query.subject || '';
      search();
    });

    return {
      searchQuery,
      tutors,
      loading,
      search,
    };
  }
};
</script>
