<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b shadow-sm py-8">
      <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Tutor Jobs</h1>
        <p class="text-gray-600">Find student requirements matching your expertise</p>
      </div>
    </div>

    <!-- Search Component -->
    <div class="bg-white border-b shadow-sm sticky top-0 z-40">
      <div class="container mx-auto px-4 py-6">
        <JobSearch 
          :subjects="subjects"
          :initial-filters="filters"
          @search="handleSearch"
        />
      </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="space-y-6">
        <div v-for="n in 3" :key="n" class="bg-white rounded-lg shadow p-6 animate-pulse">
          <div class="flex gap-4">
            <div class="w-20 h-20 bg-gray-200 rounded"></div>
            <div class="flex-1">
              <div class="h-4 bg-gray-200 rounded w-1/3 mb-2"></div>
              <div class="h-3 bg-gray-200 rounded w-1/2 mb-2"></div>
              <div class="h-3 bg-gray-200 rounded w-2/3"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Results -->
      <div v-else>
        <!-- Results Count -->
        <div class="mb-6">
          <h2 class="text-2xl font-bold text-gray-900">
            {{ total }} Job{{ total !== 1 ? 's' : '' }}
          </h2>
          <p class="text-gray-600 mt-1">
            <span v-if="filters.subject_id">Subject: <strong>{{ selectedSubjectName }}</strong></span>
            <span v-if="filters.subject_id && filters.location"> • </span>
            <span v-if="filters.location">
              Location: <strong>{{ filters.location }}</strong>
              <span v-if="filters.lat && filters.lng" class="ml-1 text-blue-600">
                <i class="fas fa-location-arrow"></i> within 50km
              </span>
            </span>
          </p>
        </div>

        <!-- Requirements List -->
        <div v-if="requirements.length > 0" class="space-y-6">
          <RequirementCard 
            v-for="requirement in requirements" 
            :key="requirement.id" 
            :requirement="requirement"
            :field-labels="fieldLabels"
          />

          <!-- Pagination -->
          <div v-if="lastPage > 1" class="mt-8 flex justify-center gap-2">
            <button 
              @click="goToPage(currentPage - 1)" 
              :disabled="currentPage === 1"
              class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i class="fas fa-chevron-left"></i> Previous
            </button>
            
            <button 
              v-for="page in visiblePages" 
              :key="page"
              @click="goToPage(page)"
              :class="[
                'px-4 py-2 border rounded-lg',
                page === currentPage 
                  ? 'bg-blue-600 text-white border-blue-600' 
                  : 'border-gray-300 hover:bg-gray-50'
              ]"
            >
              {{ page }}
            </button>
            
            <button 
              @click="goToPage(currentPage + 1)" 
              :disabled="currentPage === lastPage"
              class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>

        <!-- No Results -->
        <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center">
          <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
            <i class="fas fa-search text-gray-400 text-3xl"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">No jobs found</h3>
          <p class="text-gray-600 mb-4">Try adjusting your search filters</p>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import RequirementCard from '../components/RequirementCard.vue';
import JobSearch from '../components/JobSearch.vue';

export default {
  name: 'TutorJobs',
  components: {
    RequirementCard,
    JobSearch
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const loading = ref(false);
    const requirements = ref([]);
    const subjects = ref([]);
    const tutorSubjects = ref([]);
    const fieldLabels = ref({});
    const total = ref(0);
    const currentPage = ref(1);
    const lastPage = ref(1);
    const perPage = ref(20);

    const filters = ref({
      subject_id: route.query.subject_id || '',
      location: route.query.location || '',
      lat: route.query.lat ? parseFloat(route.query.lat) : null,
      lng: route.query.lng ? parseFloat(route.query.lng) : null,
      mode: route.query.mode || '',
      sort_by: route.query.sort_by || 'recent'
    });

    const visiblePages = computed(() => {
      const pages = [];
      const maxVisible = 5;
      let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
      let end = Math.min(lastPage.value, start + maxVisible - 1);
      
      if (end - start + 1 < maxVisible) {
        start = Math.max(1, end - maxVisible + 1);
      }
      
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      return pages;
    });

    const selectedSubjectName = computed(() => {
      if (!filters.value.subject_id) return '';
      return getSubjectName(filters.value.subject_id);
    });

    function isProfileNotApproved(error) {
      const status = error?.response?.status;
      const message = String(error?.response?.data?.message || '').toLowerCase();
      return status === 403 && (message.includes('not verified') || message.includes('not approved'));
    }

    async function loadSubjects() {
      try {
        const res = await axios.get('/api/subjects');
        subjects.value = res.data;
      } catch (error) {
        console.error('Error loading subjects:', error);
      }
    }

    async function loadTutorSubjects() {
      try {
        const res = await axios.get('/api/tutor/profile/subjects');
        tutorSubjects.value = res.data?.subjects || [];

        // Pre-select first subject if no filter selected
        if (!filters.value.subject_id && tutorSubjects.value.length > 0) {
          filters.value.subject_id = String(tutorSubjects.value[0].id);
        }
      } catch (error) {
        if (isProfileNotApproved(error)) {
          router.push('/tutor/profile/not-approved');
          return;
        }
        if (![401, 403].includes(error.response?.status)) {
          console.error('Error loading tutor subjects:', error);
        }
      }
    }

    async function loadFieldLabels() {
      try {
        const res = await axios.get('/api/field-labels');
        // Transform API response to have both field names and value mappings
        const labels = {};
        
        // Store field names
        labels['budget'] = 'Budget';
        labels['level'] = 'Level';
        labels['gender_preference'] = 'Gender Preference';
        labels['desired_start'] = 'Desired Start Date';
        
        // Store value mappings for dropdown fields
        if (res.data.level) {
          labels['level_map'] = res.data.level;
        }
        if (res.data.gender_preference) {
          labels['gender_preference_map'] = res.data.gender_preference;
        }
        
        fieldLabels.value = labels;
      } catch (error) {
        console.error('Error loading field labels:', error);
      }
    }

    function getSubjectName(id) {
      const subject = subjects.value.find(s => s.id == id);
      return subject ? subject.name : '';
    }

    async function searchJobs() {
      loading.value = true;
      try {
        const params = {
          page: currentPage.value,
          per_page: perPage.value,
          ...filters.value
        };

        // Enable nearby search if we have coordinates
        if (params.lat && params.lng) {
          params.nearby = true;
          params.radius = 50; // Search within 50km radius
        }

        // Remove empty filters
        Object.keys(params).forEach(key => {
          if (params[key] === '' || params[key] === null) {
            delete params[key];
          }
        });

        // Try authenticated endpoint first, fallback to public if not authenticated
        try {
          const res = await axios.get('/api/tutor-jobs', { params });
          requirements.value = res.data.data;
          total.value = res.data.total;
          currentPage.value = res.data.current_page;
          lastPage.value = res.data.last_page;
        } catch (authError) {
          if (isProfileNotApproved(authError)) {
            router.push('/tutor/profile/not-approved');
            return;
          }
          if (authError.response?.status === 403 || authError.response?.status === 401) {
            // Not authenticated - use public requirements endpoint
            const res = await axios.get('/api/requirements', { params });
            requirements.value = res.data.data;
            total.value = res.data.total;
            currentPage.value = res.data.current_page;
            lastPage.value = res.data.last_page;
          } else {
            throw authError;
          }
        }
      } catch (error) {
        console.error('Error loading jobs:', error);
      } finally {
        loading.value = false;
      }
    }

    function handleSearch(filterData) {
      filters.value = { ...filterData };
      currentPage.value = 1;
      updateQueryParams();
      searchJobs();
    }

    function goToPage(page) {
      if (page >= 1 && page <= lastPage.value) {
        currentPage.value = page;
        updateQueryParams();
        searchJobs();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    }

    function updateQueryParams() {
      const query = {
        ...filters.value,
        page: currentPage.value > 1 ? currentPage.value : undefined
      };
      
      // Format lat/lng to avoid precision issues
      if (query.lat) query.lat = parseFloat(query.lat).toFixed(6);
      if (query.lng) query.lng = parseFloat(query.lng).toFixed(6);
      
      // Remove empty values
      Object.keys(query).forEach(key => {
        if (query[key] === '' || query[key] === null || query[key] === undefined) {
          delete query[key];
        }
      });

      router.replace({ query });
    }

    watch(() => route.query, (newQuery) => {
      if (newQuery.page) {
        currentPage.value = parseInt(newQuery.page);
      }
      
      filters.value.subject_id = newQuery.subject_id || '';
      filters.value.location = newQuery.location || '';
      filters.value.lat = newQuery.lat ? parseFloat(newQuery.lat) : null;
      filters.value.lng = newQuery.lng ? parseFloat(newQuery.lng) : null;
      filters.value.mode = newQuery.mode || '';
      filters.value.sort_by = newQuery.sort_by || 'recent';
    });

    onMounted(async () => {
      await loadSubjects();
      await loadFieldLabels();
      await loadTutorSubjects();
      await searchJobs();
    });

    return {
      loading,
      requirements,
      subjects,
      fieldLabels,
      filters,
      total,
      currentPage,
      lastPage,
      visiblePages,
      selectedSubjectName,
      handleSearch,
      goToPage,
      getSubjectName
    };
  }
};
</script>
