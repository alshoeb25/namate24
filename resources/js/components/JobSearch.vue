<template>
  <div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
      <!-- Subject Filter - Searchable Dropdown -->
      <div class="relative md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
        <div class="relative">
          <input 
            v-model="subjectSearch" 
            @input="handleSubjectSearch"
            @focus="showSubjectDropdown = true"
            @blur="() => setTimeout(() => showSubjectDropdown = false, 200)"
            type="text" 
            placeholder="Search subjects..." 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
          >
          <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
        </div>
        
        <!-- Subject Dropdown -->
        <div v-if="showSubjectDropdown && filteredSubjects.length > 0" class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50 max-h-60 overflow-y-auto">
          <div 
            @mousedown.prevent="selectSubject(null)"
            class="px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm border-b"
          >
            <div class="font-medium text-gray-500">All Subjects</div>
          </div>
          <div 
            v-for="subject in filteredSubjects" 
            :key="subject.id"
            @mousedown.prevent="selectSubject(subject)"
            :class="[
              'px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm',
              form.subject_id == subject.id ? 'bg-blue-100' : ''
            ]"
          >
            <div class="font-medium text-gray-900">{{ subject.name }}</div>
          </div>
        </div>
      </div>

      <!-- Location Filter with Google Maps Autocomplete -->
      <div class="relative md:col-span-2 location-autocomplete-wrapper">
        <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
        <div class="relative">
          <input 
            ref="locationInput"
            type="text" 
            placeholder="Search city, area, or address..." 
            class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none pac-target-input"
          >
          <i class="fas fa-map-marker-alt absolute right-3 top-3 text-gray-400 pointer-events-none"></i>
          
          <!-- Selected Location Indicator -->
          <div v-if="locationDetails.city" class="absolute -bottom-6 left-0 right-0 text-xs text-green-600 flex items-center gap-1 z-10">
            <i class="fas fa-check-circle"></i>
            <span class="truncate">{{ locationDetails.city }}<span v-if="locationDetails.state">, {{ locationDetails.state }}</span></span>
          </div>
        </div>
      </div>

      <!-- Search Button -->
      <div class="flex items-end md:col-span-1">
        <button 
          @click="apply" 
          class="w-full px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center justify-center gap-2"
        >
          <i class="fas fa-search"></i>
          <span class="hidden lg:inline">Search</span>
        </button>
      </div>
    </div>

    <!-- Advanced Filters Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
      <!-- Mode Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Mode</label>
        <select v-model="form.mode" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
          <option value="">All Modes</option>
          <option value="online">Online</option>
          <option value="offline">Offline</option>
          <option value="both">Both</option>
        </select>
      </div>

      <!-- Sort By -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
        <select v-model="form.sort_by" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
          <option value="recent">Most Recent</option>
          <option value="budget">Budget</option>
          <option value="distance">Distance</option>
        </select>
      </div>

      <!-- Spacer -->
      <div class="hidden md:block"></div>

      <!-- Reset Button -->
      <div v-if="hasActiveFilters" class="flex items-end">
        <button @click="reset" class="w-full px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 border border-gray-300 font-medium flex items-center justify-center gap-2 transition-colors">
          <i class="fas fa-times-circle"></i>
          Clear Filters
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { reactive, computed, ref, onMounted, watch, nextTick } from 'vue';
import { loadGoogleMaps } from '../utils/googleMaps';

export default {
  name: 'JobSearch',
  props: {
    subjects: {
      type: Array,
      default: () => []
    },
    initialFilters: {
      type: Object,
      default: () => ({
        subject_id: '',
        location: '',
        mode: '',
        sort_by: 'recent'
      })
    }
  },
  emits: ['search'],
  setup(props, { emit }) {
    const form = reactive({
      subject_id: props.initialFilters.subject_id || '',
      location: props.initialFilters.location || '',
      mode: props.initialFilters.mode || '',
      sort_by: props.initialFilters.sort_by || 'recent'
    });

    const locationInput = ref(null);
    const locationDetails = reactive({
      address: '',
      city: '',
      state: '',
      area: '',
      lat: null,
      lng: null,
      placeId: ''
    });

    // Subject search functionality
    const subjectSearch = ref('');
    const showSubjectDropdown = ref(false);
    const selectedSubject = ref(null);

    let autocomplete = null;

    const hasActiveFilters = computed(() => {
      return form.subject_id || form.location || form.mode || form.sort_by !== 'recent';
    });

    const filteredSubjects = computed(() => {
      if (!subjectSearch.value.trim()) {
        return props.subjects;
      }
      const search = subjectSearch.value.toLowerCase().trim();
      return props.subjects.filter(subject => 
        subject.name.toLowerCase().includes(search)
      );
    });

    onMounted(async () => {
      // Initialize subject search display if pre-selected and subjects are loaded
      if (form.subject_id && props.subjects.length > 0) {
        const subject = props.subjects.find(s => s.id == form.subject_id);
        if (subject) {
          subjectSearch.value = subject.name;
          selectedSubject.value = subject;
        }
      }

      // Initialize Google Maps Autocomplete
      await nextTick(); // Ensure DOM is ready
      
      try {
        await loadGoogleMaps();
        
        if (!locationInput.value) {
          console.error('Location input ref not found');
          return;
        }
        
        if (!window.google?.maps?.places?.Autocomplete) {
          console.error('Google Maps Autocomplete not available');
          return;
        }

        autocomplete = new window.google.maps.places.Autocomplete(
          locationInput.value,
          { 
            types: ['geocode'],
            componentRestrictions: { country: 'in' }
          }
        );

        autocomplete.setFields(['address_components', 'formatted_address', 'geometry', 'place_id']);

        autocomplete.addListener('place_changed', () => {
          const place = autocomplete.getPlace();
          if (!place || !place.geometry) {
            console.warn('No details available for input:', place?.name);
            return;
          }
          handlePlaceChanged(place);
        });

        // Set initial location value if exists
        if (form.location && locationInput.value) {
          locationInput.value.value = form.location;
        }
        
        console.log('Google Maps Autocomplete initialized successfully');
      } catch (error) {
        console.error('Failed to initialize Google Maps:', error);
      }
    });

    function handlePlaceChanged(place) {
      console.log('Place changed:', place);
      
      const addressComponents = place.address_components || [];
      
      // Extract location details
      locationDetails.address = place.formatted_address || '';
      locationDetails.placeId = place.place_id || '';
      
      // Get coordinates
      if (place.geometry?.location) {
        locationDetails.lat = typeof place.geometry.location.lat === 'function' 
          ? place.geometry.location.lat() 
          : place.geometry.location.lat;
        locationDetails.lng = typeof place.geometry.location.lng === 'function' 
          ? place.geometry.location.lng() 
          : place.geometry.location.lng;
      }
      
      // Extract city, state, area with priority order
      const locality = addressComponents.find(c => c.types.includes('locality'));
      const adminLevel2 = addressComponents.find(c => c.types.includes('administrative_area_level_2'));
      const adminLevel1 = addressComponents.find(c => c.types.includes('administrative_area_level_1'));
      const sublocality = addressComponents.find(c => c.types.includes('sublocality_level_1'));
      const sublocality2 = addressComponents.find(c => c.types.includes('sublocality'));
      
      locationDetails.city = locality?.long_name || adminLevel2?.long_name || '';
      locationDetails.state = adminLevel1?.short_name || adminLevel1?.long_name || '';
      locationDetails.area = sublocality?.long_name || sublocality2?.long_name || '';
      
      // Update form location with city name or formatted address
      form.location = locationDetails.city || locality?.short_name || place.name || place.formatted_address;
      
      console.log('Location details:', locationDetails);
      console.log('Form location set to:', form.location);
    }

    function handleSubjectSearch() {
      showSubjectDropdown.value = true;
    }

    function selectSubject(subject) {
      if (subject) {
        form.subject_id = subject.id;
        subjectSearch.value = subject.name;
        selectedSubject.value = subject;
      } else {
        // Clear selection
        form.subject_id = '';
        subjectSearch.value = '';
        selectedSubject.value = null;
      }
      showSubjectDropdown.value = false;
    }

    function apply() {
      const searchParams = { 
        ...form,
        lat: locationDetails.lat,
        lng: locationDetails.lng
      };
      
      // Only include lat/lng if we have valid coordinates
      if (!searchParams.lat || !searchParams.lng) {
        delete searchParams.lat;
        delete searchParams.lng;
      }
      
      emit('search', searchParams);
    }

    function reset() {
      form.subject_id = '';
      form.location = '';
      form.mode = '';
      form.sort_by = 'recent';
      subjectSearch.value = '';
      selectedSubject.value = null;
      
      // Clear location input and details
      if (locationInput.value) {
        locationInput.value.value = '';
      }
      locationDetails.address = '';
      locationDetails.city = '';
      locationDetails.state = '';
      locationDetails.area = '';
      locationDetails.lat = null;
      locationDetails.lng = null;
      locationDetails.placeId = '';
      
      emit('search', { ...form });
    }

    watch(
      () => props.initialFilters,
      (newFilters) => {
        form.subject_id = newFilters.subject_id || '';
        form.location = newFilters.location || '';
        if (locationInput.value) {
          locationInput.value.value = newFilters.location || '';
        }
        form.mode = newFilters.mode || '';
        form.sort_by = newFilters.sort_by || 'recent';
        
        // Update subject search display when filter changes
        if (newFilters.subject_id) {
          const subject = props.subjects.find(s => s.id == newFilters.subject_id);
          if (subject) {
            subjectSearch.value = subject.name;
            selectedSubject.value = subject;
          }
        } else {
          subjectSearch.value = '';
          selectedSubject.value = null;
        }
      },
      { deep: true }
    );

    // Watch for subjects loading to update display and trigger initial search
    watch(
      () => props.subjects,
      (newSubjects, oldSubjects) => {
        if (newSubjects.length > 0 && form.subject_id && !subjectSearch.value) {
          const subject = newSubjects.find(s => s.id == form.subject_id);
          if (subject) {
            subjectSearch.value = subject.name;
            selectedSubject.value = subject;
            // Auto-search when tutor's subject loads (only on first load)
            if (!oldSubjects || oldSubjects.length === 0) {
              apply();
            }
          }
        }
      }
    );

    return {
      form,
      hasActiveFilters,
      locationInput,
      locationDetails,
      subjectSearch,
      showSubjectDropdown,
      selectedSubject,
      filteredSubjects,
      apply,
      reset,
      handleSubjectSearch,
      selectSubject
    };
  }
};
</script>

<style scoped>
/* Ensure Google Maps autocomplete dropdown appears above everything */
.location-autocomplete-wrapper {
  position: relative;
  z-index: 1;
}

/* Prevent icon from blocking autocomplete clicks */
.pointer-events-none {
  pointer-events: none;
}
</style>

<style>
/* Global styles for Google Maps autocomplete dropdown (cannot be scoped) */
.pac-container {
  z-index: 9999 !important;
  border-radius: 8px;
  margin-top: 4px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border: 1px solid #d1d5db;
}

.pac-item {
  padding: 8px 12px;
  cursor: pointer;
  font-size: 14px;
  border-top: 1px solid #e5e7eb;
}

.pac-item:first-child {
  border-top: none;
}

.pac-item:hover {
  background-color: #eff6ff;
}

.pac-item-query {
  font-size: 14px;
  color: #111827;
  font-weight: 500;
}

.pac-matched {
  font-weight: 600;
  color: #2563eb;
}

.pac-icon {
  margin-top: 2px;
}
</style>
