<template>
  <!-- Hero Section -->
  <section class="w-full">
    <div class="text-black rounded-b-3xl p-6 shadow-lg w-full bg-cover bg-center 
               bg-[url('https://image2url.com/images/1765221100057-fff8f4b5-27df-48e0-8d70-7393bebec0ff.png')] 
               md:bg-[url('https://image2url.com/images/1765432244131-32eb4e62-559a-40c6-87c3-d046b0b27ae1.png')]">
      <div class="desktop-bg max-w-3xl mx-auto">
        <h2 class="text-2xl font-bold text-white">
          Empowering Teachers & Students Worldwide
        </h2>
        <p class="text-sm mt-1 opacity-90 text-white">
          Expert guidance for every subject, anywhere.
        </p>

        <div class="mt-4 bg-white p-4 rounded-xl flex flex-col gap-3">
          <!-- Subject Input -->
          <div class="flex row gap-2">
            <div class="flex items-center bg-gray-100 rounded-lg px-3 py-2 flex-1 relative">
              <img src="https://img.icons8.com/?size=100&id=McUzNetNtaJK&format=png" 
                   alt="search icon"
                   class="h-5 w-5 object-contain opacity-70" />
              <input v-model="searchSubject" 
                     type="text" 
                     id="subjectAutocomplete"
                     placeholder="Subject / Skill"
                     @input="handleSubjectInput"
                     @keyup.enter="performSearch"
                     @focus="showDropdown = true"
                     @blur="hideDropdown"
                     class="ml-2 bg-transparent outline-none w-full text-gray-700">
              
              <!-- Autocomplete Dropdown -->
              <div v-if="showDropdown && subjectSuggestions.length > 0"
                   class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg z-50 max-h-60 overflow-y-auto">
                <button v-for="subject in subjectSuggestions" 
                        :key="subject.id"
                        @mousedown.prevent="selectSubject(subject)"
                        class="w-full text-left px-4 py-2 hover:bg-blue-50 transition-colors border-b last:border-b-0">
                  <div class="font-medium text-gray-900">{{ subject.name }}</div>
                  <div v-if="subject.category" class="text-xs text-gray-500">{{ subject.category }}</div>
                </button>
              </div>
              
              <!-- Loading indicator -->
              <div v-if="loadingSubjects" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                <svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
              </div>
            </div>
            
            <!-- Hidden inputs -->
            <input type="hidden" id="subjectId" v-model="subjectId">
            <input type="hidden" id="subjectUrl" v-model="subjectUrl">
            <input type="hidden" id="subjectSearchId" v-model="subjectSearchId">
            <input type="hidden" id="subjectSearchName" v-model="subjectSearchName">
            <input type="hidden" id="searchType" v-model="searchType">
          </div>

          <!-- Location + Arrow -->
          <div class="flex row gap-2">
            <div class="flex items-center bg-gray-100 rounded-lg px-3 py-2 flex-1">
              <img src="https://img.icons8.com/?size=100&id=p5n5ZAUprZsA&format=png" 
                   alt="location icon"
                   class="h-5 w-5 opacity-70" />
              <input v-model="searchLocation" 
                     type="text" 
                     placeholder="Zip Code or City"
                     @keyup.enter="performSearch"
                     class="ml-2 bg-transparent outline-none w-full text-gray-700">
            </div>
            <button @click="performSearch"
                    class="bg-blue-600 hover:bg-blue-700 transition text-white px-2 rounded-lg 
                           flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                   stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M14 5l7 7-7 7M21 12H3" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { encryptQueryParams, decryptQueryParams } from '../utils/encryption';

const router = useRouter();
const route = useRoute();
const searchSubject = ref('');
const searchLocation = ref('');
const subjectSuggestions = ref([]);
const showDropdown = ref(false);
const loadingSubjects = ref(false);

// Hidden input values
const subjectId = ref('');
const subjectUrl = ref('');
const subjectSearchId = ref('');
const subjectSearchName = ref('');
const searchType = ref('tutors'); // 'tutors' or 'jobs'

let searchTimeout = null;

async function handleSubjectInput(event) {
  const value = event.target.value.trim();
  
  // Reset hidden values when typing
  subjectId.value = '';
  subjectUrl.value = '';
  subjectSearchId.value = '';
  subjectSearchName.value = '';
  
  // Only search if at least 2 characters
  if (value.length < 2) {
    subjectSuggestions.value = [];
    showDropdown.value = false;
    return;
  }
  
  // Debounce API call
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(async () => {
    await fetchSubjects(value);
  }, 300);
}

async function fetchSubjects(query) {
  loadingSubjects.value = true;
  try {
    const response = await axios.get('/api/search-subjects', {
      params: { search: query, limit: 10 }
    });
    subjectSuggestions.value = response.data.data || response.data;
    showDropdown.value = true;
  } catch (error) {
    console.error('Error fetching subjects:', error);
    subjectSuggestions.value = [];
  } finally {
    loadingSubjects.value = false;
  }
}

function selectSubject(subject) {
  searchSubject.value = subject.name;
  subjectId.value = subject.id;
  subjectUrl.value = subject.slug || subject.name.toLowerCase().replace(/\s+/g, '-');
  
  // Clear custom search values since a subject was selected
  subjectSearchId.value = '';
  subjectSearchName.value = '';
  
  // Hide dropdown
  subjectSuggestions.value = [];
  showDropdown.value = false;
}

function hideDropdown() {
  setTimeout(() => {
    showDropdown.value = false;
  }, 200);
}

function makeSearchUrl(subject, mode, city) {
  let url = 'tutors/';

  if (subject) {
    url += subject.toLowerCase().replace(/\s+/g, '-');
  }

  if (mode) {
    url += '-' + mode.toLowerCase();
  }

  url += '-tutors';

  if (city) {
    url += '-in-' + city.toLowerCase().replace(/\s+/g, '-');
  }

  return '/' + url;
}

function performSearch() {
  const subject = searchSubject.value.trim();
  const location = searchLocation.value.trim();
  
  // If both are empty, redirect to /tutors
  if (!subject && !location) {
    router.push('/tutors');
    return;
  }
  
  // If no subject was selected from dropdown, store as custom search
  if (subject && !subjectId.value) {
    subjectSearchName.value = subject;
    subjectSearchId.value = 'custom_' + Date.now();
  }
  
  // Build dynamic path based on searchType
  let searchPath = '/tutors';
  
  if (searchType.value === 'jobs') {
    searchPath = '/tutor-jobs';
  } else if (searchType.value === 'tutors') {
    // Build SEO-friendly URL: /{subject}-tutors-in-{city}
    if (subject || location) {
      const subjectSlug = subjectUrl.value || subject.toLowerCase().replace(/\s+/g, '-');
      const citySlug = location.toLowerCase().replace(/\s+/g, '-');
      
      if (subject && location) {
        searchPath = `/${subjectSlug}-tutors-in-${citySlug}`;
      } else if (subject) {
        searchPath = `/${subjectSlug}-tutors`;
      } else if (location) {
        searchPath = `/tutors-in-${citySlug}`;
      }
    }
  }
  
  // Build query parameters with hidden input values
  const queryData = {};
  
  if (subjectId.value) {
    queryData.subject_id = subjectId.value;
  }
  
  if (subjectUrl.value) {
    queryData.subject_url = subjectUrl.value;
  }
  
  if (subjectSearchId.value) {
    queryData.subject_search_id = subjectSearchId.value;
  }
  
  if (subjectSearchName.value) {
    queryData.subject_search_name = subjectSearchName.value;
  }
  
  if (subject) {
    queryData.subject = subject;
  }
  
  if (location) {
    queryData.location = location;
  }
  
  queryData.search_type = searchType.value;
  
  // Encrypt query parameters
  const encrypted = encryptQueryParams(queryData);
  
  // Navigate to search results
  router.push({
    path: searchPath,
    query: { q: encrypted }
  });
}

function populateFromRoute() {
  // Decrypt query if encrypted
  let queryData = {};
  
  if (route.query.q) {
    const decrypted = decryptQueryParams(route.query.q);
    queryData = decrypted || {};
  } else {
    // Fallback to plain query params
    queryData = route.query;
  }
  
  // Populate fields from decrypted data
  if (queryData.subject) {
    searchSubject.value = queryData.subject;
  }
  if (queryData.location) {
    searchLocation.value = queryData.location;
  }
  if (queryData.subject_id) {
    subjectId.value = queryData.subject_id;
  }
  if (queryData.subject_url) {
    subjectUrl.value = queryData.subject_url;
  }
  if (queryData.search_type) {
    searchType.value = queryData.search_type;
  }
}

// Populate fields on mount
onMounted(() => {
  populateFromRoute();
});

// Watch for route changes to update fields
watch(() => route.query, () => {
  populateFromRoute();
}, { deep: true });
</script>
