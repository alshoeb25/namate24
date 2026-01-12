<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Search Component -->
    <HeroSearch />

    <!-- Search Filters -->
    <div class="bg-white border-b shadow-sm sticky top-0 z-40">
      <div class="container mx-auto px-4 py-3">
        <div class="flex flex-wrap gap-3 items-center">
          <button @click="toggleFilter('online')" 
                  :class="['px-4 py-2 rounded-full text-sm font-medium transition-colors',
                          filters.online ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">
            <i class="fas fa-wifi mr-1"></i> Online
          </button>
          <button @click="toggleFilter('home')" 
                  :class="['px-4 py-2 rounded-full text-sm font-medium transition-colors',
                          filters.home ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">
            <i class="fas fa-home mr-1"></i> At Home
          </button>
          <button @click="toggleFilter('verified')" 
                  :class="['px-4 py-2 rounded-full text-sm font-medium transition-colors',
                          filters.verified ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">
            <i class="fas fa-check-circle mr-1"></i> Verified
          </button>
            <button @click="toggleFilter('featured')" 
              :class="['px-4 py-2 rounded-full text-sm font-medium transition-colors',
                filters.featured ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">
              <i class="fas fa-star mr-1"></i> Featured
            </button>
          
          <select v-model="filters.experience" 
                  class="px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700 border-none outline-none">
            <option value="">Experience</option>
            <option value="0-2">0-2 years</option>
            <option value="3-5">3-5 years</option>
            <option value="5+">5+ years</option>
          </select>

          <select v-model="filters.priceRange" 
                  class="px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700 border-none outline-none">
            <option value="">Price Range</option>
            <option value="0-500">₹0 - ₹500</option>
            <option value="500-1000">₹500 - ₹1000</option>
            <option value="1000+">₹1000+</option>
          </select>

          <button v-if="hasActiveFilters" 
                  @click="resetFilters"
                  class="px-4 py-2 rounded-full text-sm font-medium bg-red-50 text-red-600 hover:bg-red-100">
            <i class="fas fa-times mr-1"></i> Clear All
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Main Content - Tutors List -->
        <div class="lg:w-3/4">
          <!-- Results Count -->
          <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">
              {{ filteredTutors.length }}
              {{ isJobsSearch ? (filteredTutors.length === 1 ? 'Enquiry' : 'Enquiries') : (filteredTutors.length === 1 ? 'Tutor' : 'Tutors') }} Found
            </h2>
            <p v-if="searchQuery.subject || searchQuery.location" class="text-gray-600 mt-1">
              <span v-if="searchQuery.subject">Subject: <strong>{{ searchQuery.subject }}</strong></span>
              <span v-if="searchQuery.subject && searchQuery.location"> • </span>
              <span v-if="searchQuery.location">Location: <strong>{{ searchQuery.location }}</strong></span>
            </p>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="space-y-6">
            <div v-for="n in 3" :key="n" class="bg-white rounded-xl shadow-sm p-6 animate-pulse">
              <div class="flex gap-4">
                <div class="w-20 h-20 bg-gray-200 rounded-full"></div>
                <div class="flex-1">
                  <div class="h-4 bg-gray-200 rounded w-1/3 mb-2"></div>
                  <div class="h-3 bg-gray-200 rounded w-1/2 mb-2"></div>
                  <div class="h-3 bg-gray-200 rounded w-2/3"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tutors List -->
          <div v-else-if="filteredTutors.length > 0" class="space-y-6">
            <template v-if="isJobsSearch">
              <JobCard v-for="enquiry in filteredTutors" 
                       :key="enquiry.id" 
                       :enquiry="enquiry" />
            </template>
            <template v-else>
              <div v-for="tutor in filteredTutors" :key="tutor.id" class="relative">
                <span v-if="isFeatured(tutor)"
                      class="absolute right-3 top-3 inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold text-amber-800 bg-amber-100 rounded-full border border-amber-200">
                  ★ Featured
                </span>
                <TutorCard :tutor="tutor" />
              </div>
            </template>
          </div>

          <!-- No Results State -->
          <div v-else class="bg-white rounded-xl shadow-sm p-12 text-center">
            <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
              <i class="fas fa-search text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">No results found</h3>
            <p class="text-gray-600 mb-4">Try adjusting your search filters or location</p>
            <button @click="resetFilters"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
              <i class="fas fa-redo mr-2"></i>Reset All Filters
            </button>
          </div>
        </div>

        <!-- Sidebar -->
        <aside class="lg:w-1/4">
          <!-- Locations & stats shown only for tutor search -->
          <div v-if="!isJobsSearch" class="bg-white rounded-xl shadow-sm p-6 mb-6 sticky top-24">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
              <i class="fas fa-map-marker-alt text-blue-600"></i>
              Teaching Locations
            </h3>
            <div class="space-y-3">
              <router-link v-for="location in popularLocations" 
                           :key="location.city"
                           :to="{ path: '/search', query: { location: location.city } }"
                           class="block text-gray-700 hover:text-blue-600 transition-colors text-sm">
                <i class="fas fa-map-pin text-gray-400 mr-2"></i>
                {{ location.city }}
                <span class="text-gray-500 text-xs ml-1">({{ location.count }})</span>
              </router-link>
            </div>

            <!-- Stats Card -->
            <div class="mt-8 pt-6 border-t border-gray-200">
              <h4 class="text-sm font-semibold text-gray-900 mb-3">Quick Stats</h4>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-gray-600 text-sm">Total Tutors</span>
                  <span class="font-semibold text-blue-600">{{ tutors.length }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-600 text-sm">Average Experience</span>
                  <span class="font-semibold text-blue-600">{{ averageExperience }} yrs</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-600 text-sm">Response Rate</span>
                  <span class="font-semibold text-blue-600">98%</span>
                </div>
              </div>
            </div>

            
          </div>

          
        </aside>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from '../bootstrap';
import HeroSearch from '../components/HeroSearch.vue';
import TutorCard from '../components/TutorCard.vue';
import JobCard from '../components/JobCard.vue';
import { decryptQueryParams } from '../utils/encryption';

const route = useRoute();
const router = useRouter();
const tutors = ref([]);
const loading = ref(false);
const newsletterEmail = ref('');

const searchQuery = computed(() => {
  if (route.query.q) {
    // Decrypt encrypted query
    const decrypted = decryptQueryParams(route.query.q);
    return decrypted || { subject: '', location: '' };
  }
  // Fallback to regular query params for backward compatibility
  const query = {
    subject: route.query.subject || '',
    location: route.query.location || '',
    subject_id: route.query.subject_id || '',
    subject_url: route.query.subject_url || '',
    subject_search_id: route.query.subject_search_id || '',
    subject_search_name: route.query.subject_search_name || '',
    search_type: route.query.search_type || 'tutors'
  };

  // If navigating via /tutor-jobs path, force jobs search mode
  if (route.name === 'tutor-jobs') {
    query.search_type = 'jobs';
  }

  return query;
});

const filters = ref({
  online: false,
  home: false,
  verified: false,
  featured: false,
  experience: '',
  priceRange: ''
});

const hasActiveFilters = computed(() => {
  return filters.value.online || 
         filters.value.home || 
         filters.value.verified || 
         filters.value.featured || 
         filters.value.experience || 
         filters.value.priceRange;
});

const isJobsSearch = computed(() => searchQuery.value.search_type === 'jobs');

const filteredTutors = computed(() => {
  if (isJobsSearch.value) {
    return tutors.value;
  }

  let result = [...tutors.value];

  // Apply filters
  if (filters.value.online) {
    result = result.filter(t => t.online_available);
  }
  
  if (filters.value.home) {
    result = result.filter(t => t.teaching_mode?.includes('home'));
  }
  
  if (filters.value.verified) {
    result = result.filter(t => t.verified);
  }

  if (filters.value.featured) {
    result = result.filter(isFeatured);
  }

  if (filters.value.experience) {
    const [min, max] = filters.value.experience.split('-').map(v => v.replace('+', ''));
    result = result.filter(t => {
      const exp = t.experience_total_years || 0;
      if (max) {
        return exp >= parseInt(min) && exp <= parseInt(max);
      } else {
        return exp >= parseInt(min);
      }
    });
  }

  if (filters.value.priceRange) {
    const [min, max] = filters.value.priceRange.split('-').map(v => v.replace('+', ''));
    result = result.filter(t => {
      const price = t.price_per_hour || 0;
      if (max) {
        return price >= parseInt(min) && price <= parseInt(max);
      } else {
        return price >= parseInt(min);
      }
    });
  }

  return result;
});

const popularLocations = computed(() => {
  const locationMap = {};
  tutors.value.forEach(tutor => {
    const city = tutor.city || 'Unknown';
    locationMap[city] = (locationMap[city] || 0) + 1;
  });
  
  return Object.entries(locationMap)
    .map(([city, count]) => ({ city, count }))
    .sort((a, b) => b.count - a.count)
    .slice(0, 8);
});

const averageExperience = computed(() => {
  if (tutors.value.length === 0) return 0;
  const total = tutors.value.reduce((sum, t) => sum + (t.experience_total_years || 0), 0);
  return (total / tutors.value.length).toFixed(1);
});

const getRating = (tutor) => {
  return tutor?.rating_avg ?? tutor?.rating ?? tutor?.tutor?.rating_avg ?? 0;
};

const isFeatured = (tutor) => {
  const rating = getRating(tutor);
  const verified = tutor?.verified ?? tutor?.tutor?.verified ?? false;
  return verified && rating >= 4.5;
};

function toggleFilter(filterName) {
  filters.value[filterName] = !filters.value[filterName];
  // loadTutors() will be called by filters watch
}

function resetFilters() {
  filters.value = {
    online: false,
    home: false,
    verified: false,
    featured: false,
    experience: '',
    priceRange: ''
  };
  // loadTutors() will be called by filters watch

  // Also reset search fields via route flag so HeroSearch can clear inputs
  try {
    router.replace({ path: '/tutors', query: { reset: '1' } });
  } catch (e) {
    // no-op: route may not change context
  }
}

async function loadTutors() {
  loading.value = true;
  try {
    const params = {};
    
    // Add all search query parameters including hidden values
    if (searchQuery.value.subject) params.subject = searchQuery.value.subject;
    if (searchQuery.value.location) params.location = searchQuery.value.location;
    if (searchQuery.value.subject_id) params.subject_id = searchQuery.value.subject_id;
    if (searchQuery.value.subject_url) params.subject_url = searchQuery.value.subject_url;
    if (searchQuery.value.subject_search_id) params.subject_search_id = searchQuery.value.subject_search_id;
    if (searchQuery.value.subject_search_name) params.subject_search_name = searchQuery.value.subject_search_name;
    if (searchQuery.value.search_type) params.search_type = searchQuery.value.search_type;
    
    // Add filter parameters
    if (filters.value.online) params.online = 'true';
    if (filters.value.home) params.home = 'true';
    if (filters.value.verified) params.verified = 'true';
    if (filters.value.featured) params.featured = 'true';
    if (filters.value.experience) params.experience = filters.value.experience;
    if (filters.value.priceRange) params.price_range = filters.value.priceRange;

    // Determine API endpoint based on search_type
    const endpoint = isJobsSearch.value ? '/api/tutor-jobs' : '/api/tutors';
    
    // Send encrypted query to API
    const response = await axios.get(endpoint, { 
      params,
      headers: {
        'X-Search-Query': route.query.q || '' // Send encrypted query in header
      }
    });
    tutors.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error loading tutors:', error);
    tutors.value = [];
  } finally {
    loading.value = false;
  }
}

function subscribeNewsletter() {
  if (!newsletterEmail.value) return;
  
  // TODO: Implement newsletter subscription
  alert('Newsletter subscription coming soon!');
  newsletterEmail.value = '';
}

// Watch for route changes (search query changes)
watch(() => route.query.q || route.query, (newVal, oldVal) => {
  // Reload when encrypted query or any query param changes
  loadTutors();
}, { deep: true });

// Watch for filter changes (debounced to avoid multiple calls)
let filterTimeout = null;
watch(filters, () => {
  clearTimeout(filterTimeout);
  filterTimeout = setTimeout(() => {
    loadTutors();
  }, 150);
}, { deep: true });

onMounted(() => {
  loadTutors();
});
</script>
