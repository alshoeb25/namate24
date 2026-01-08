<template>
  <div class="w-full min-h-screen bg-gray-50">
    <!-- Header -->
    <HeaderRoot />

    <!-- Search Results -->
    <main class="max-w-7xl mx-auto px-4 py-8">
      <div class="flex items-center justify-between gap-4 mb-6 flex-wrap">
        <h2 class="text-2xl font-bold text-gray-900">
          {{ featuredOnly ? 'Featured Tutors' : 'Search Tutors' }}
        </h2>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
          <input type="checkbox" v-model="featuredOnly" @change="search" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
          <span>Show only featured</span>
        </label>
      </div>

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
          class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition relative">
          <span v-if="isFeatured(tutor)"
            class="absolute right-3 top-3 inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold text-amber-800 bg-amber-100 rounded-full border border-amber-200">
            ★ Featured
          </span>
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
import { useRoute, useRouter } from 'vue-router';
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
    const router = useRouter();
    const userStore = useUserStore();

    const searchQuery = ref('');
    const tutors = ref([]);
    const loading = ref(false);
    const featuredOnly = ref(route.query.featured === 'true');

    const search = async () => {
      loading.value = true;
      try {
        const params = {
          q: searchQuery.value || route.query.subject || '',
          location: route.query.location || '',
          featured: featuredOnly.value ? 'true' : '',
        };

        // Keep URL in sync with current filters
        router.replace({
          name: 'search',
          query: {
            subject: params.q || undefined,
            location: params.location || undefined,
            featured: featuredOnly.value ? 'true' : undefined,
          }
        });

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

    const getRating = (t) => t?.rating_avg ?? t?.tutor?.rating_avg ?? t?.rating ?? 0;
    const isFeatured = (t) => {
      const rating = getRating(t);
      const verified = t?.verified ?? t?.tutor?.verified ?? false;
      return featuredOnly.value || (verified && rating >= 4.5);
    };

    return {
      searchQuery,
      tutors,
      loading,
      featuredOnly,
      search,
      isFeatured,
    };
  }
};
</script>
