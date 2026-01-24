<template>
  <div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <!-- Subject Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
        <select v-model="form.subject_id" @change="apply" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
          <option value="">All Subjects</option>
          <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
            {{ subject.name }}
          </option>
        </select>
      </div>

      <!-- Location Filter with Autocomplete -->
      <div class="relative">
        <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
        <input 
          ref="locationInput"
          v-model="form.location" 
          @input="handleLocationInput"
          @focus="showLocationSuggestions = true"
          @blur="() => setTimeout(() => showLocationSuggestions = false, 200)"
          type="text" 
          placeholder="City or area" 
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
        >
        
        <!-- Location Suggestions Dropdown -->
        <div v-if="showLocationSuggestions && locationSuggestions.length > 0" class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50">
          <div 
            v-for="(suggestion, index) in locationSuggestions" 
            :key="index"
            @click="selectLocation(suggestion)"
            class="px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm"
          >
            <div class="font-medium text-gray-900">{{ suggestion.main_text }}</div>
            <div v-if="suggestion.secondary_text" class="text-xs text-gray-600">{{ suggestion.secondary_text }}</div>
          </div>
        </div>

        <!-- Location Details Display -->
        <div v-if="locationDetails.address && form.location" class="absolute left-0 right-0 top-full mt-2 bg-green-50 border border-green-300 rounded-lg p-3 shadow-lg z-40 text-sm">
          <div class="text-green-700 font-medium">
            <i class="fas fa-map-pin mr-1"></i>{{ locationDetails.address }}
          </div>
          <p v-if="locationDetails.city" class="text-green-600 text-xs mt-1">
            {{ locationDetails.city }}<span v-if="locationDetails.area">, {{ locationDetails.area }}</span>
          </p>
        </div>
      </div>

      <!-- Mode Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Mode</label>
        <select v-model="form.mode" @change="apply" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
          <option value="">All Modes</option>
          <option value="online">Online</option>
          <option value="offline">Offline</option>
          <option value="both">Both</option>
        </select>
      </div>

      <!-- Sort By -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
        <select v-model="form.sort_by" @change="apply" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
          <option value="recent">Most Recent</option>
          <option value="budget">Budget</option>
          <option value="distance">Distance</option>
        </select>
      </div>
    </div>

    <!-- Reset Button -->
    <div v-if="hasActiveFilters" class="mt-4">
      <button @click="reset" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 text-sm font-medium">
        <i class="fas fa-times mr-2"></i>Clear Filters
      </button>
    </div>
  </div>
</template>

<script>
import { reactive, computed, ref, onMounted, watch } from 'vue';
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
    const showLocationSuggestions = ref(false);
    const locationSuggestions = ref([]);
    const locationDetails = reactive({
      address: '',
      city: '',
      area: '',
      lat: null,
      lng: null,
      placeId: ''
    });

    let autocompleteService = null;
    let placesService = null;
    let autocompleteSessionToken = null;

    const hasActiveFilters = computed(() => {
      return form.subject_id || form.location || form.mode || form.sort_by !== 'recent';
    });

    onMounted(async () => {
      try {
        await loadGoogleMaps();
        // Initialize Google Places Autocomplete Service
        if (window.google?.maps?.places) {
          autocompleteService = new window.google.maps.places.AutocompleteService();
          placesService = new window.google.maps.places.PlacesService(document.createElement('div'));
          autocompleteSessionToken = new window.google.maps.places.AutocompleteSessionToken();
        }
      } catch (error) {
        console.error('Failed to load Google Maps:', error);
      }
    });

    async function handleLocationInput(event) {
      const value = event.target.value.trim();
      form.location = value;
      
      // Clear stored place details when the user edits manually
      locationDetails.address = '';
      locationDetails.city = '';
      locationDetails.area = '';
      locationDetails.lat = null;
      locationDetails.lng = null;
      locationDetails.placeId = '';

      if (value.length < 2 || !autocompleteService) {
        locationSuggestions.value = [];
        showLocationSuggestions.value = false;
        // Trigger search when location is cleared
        if (value.length === 0) {
          apply();
        }
        return;
      }

      try {
        const response = await autocompleteService.getPlacePredictions({
          input: value,
          sessionToken: autocompleteSessionToken,
          types: ['(cities)'],
          componentRestrictions: { country: 'in' } // Focus on India
        });

        if (response.predictions) {
          locationSuggestions.value = response.predictions.map(prediction => ({
            main_text: prediction.main_text,
            secondary_text: prediction.secondary_text,
            place_id: prediction.place_id,
            description: prediction.description
          }));
          showLocationSuggestions.value = locationSuggestions.value.length > 0;
        }
      } catch (error) {
        console.error('Error fetching location suggestions:', error);
        locationSuggestions.value = [];
      }
    }

    function selectLocation(suggestion) {
      form.location = suggestion.main_text;
      locationDetails.address = suggestion.description;
      locationDetails.city = suggestion.main_text;
      locationSuggestions.value = [];
      showLocationSuggestions.value = false;

      // Fetch detailed place info to get lat/lng
      if (placesService && suggestion.place_id && window.google?.maps?.places) {
        const request = {
          placeId: suggestion.place_id,
          sessionToken: autocompleteSessionToken,
          fields: ['geometry', 'formatted_address', 'address_components']
        };

        placesService.getDetails(request, (place, status) => {
          if (status === window.google.maps.places.PlacesServiceStatus.OK) {
            locationDetails.place_id = suggestion.place_id;
            locationDetails.lat = place.geometry?.location?.lat();
            locationDetails.lng = place.geometry?.location?.lng();
            locationDetails.address = place.formatted_address || suggestion.description;
            
            // Extract city and area from address components
            const addressComponents = place.address_components || [];
            const city = addressComponents.find(c => c.types.includes('administrative_area_level_2'));
            const area = addressComponents.find(c => c.types.includes('locality'));
            
            if (city) locationDetails.city = city.long_name;
            if (area) locationDetails.area = area.long_name;
          }
        });
      }

      apply();
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
      locationDetails.address = '';
      locationDetails.city = '';
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
        form.mode = newFilters.mode || '';
        form.sort_by = newFilters.sort_by || 'recent';
      },
      { deep: true }
    );

    return {
      form,
      hasActiveFilters,
      locationInput,
      showLocationSuggestions,
      locationSuggestions,
      locationDetails,
      apply,
      reset,
      handleLocationInput,
      selectLocation
    };
  }
};
</script>
