import { ref, onMounted, watch } from 'vue';

const mapsLoaded = ref(false);
const googleAPI = window.google;

export function useGoogleMapsAutocomplete(inputRef, options = {}) {
  const place = ref(null);
  const isLoading = ref(false);
  const error = ref(null);

  const {
    onPlaceSelect = () => {},
    componentRestrictions = {},
    types = ['geocode']
  } = options;

  const initializeAutocomplete = () => {
    if (!inputRef.value || !window.google?.maps?.places) {
      console.warn('Google Maps API not loaded or input ref not ready');
      return;
    }

    const autocomplete = new google.maps.places.Autocomplete(inputRef.value, {
      types: types,
      componentRestrictions: componentRestrictions,
    });

    autocomplete.addListener('place_changed', () => {
      isLoading.value = true;
      try {
        const selectedPlace = autocomplete.getPlace();

        if (!selectedPlace.geometry) {
          error.value = 'Please select a valid location from the dropdown';
          isLoading.value = false;
          return;
        }

        // Parse address components
        const addressComponents = selectedPlace.address_components || [];
        const locationData = {
          address: selectedPlace.formatted_address,
          lat: selectedPlace.geometry.location.lat(),
          lng: selectedPlace.geometry.location.lng(),
          placeId: selectedPlace.place_id,
          city: '',
          state: '',
          country: '',
          postalCode: '',
          area: ''
        };

        // Extract address components
        addressComponents.forEach(component => {
          const types = component.types;
          if (types.includes('locality')) {
            locationData.city = component.long_name;
          }
          if (types.includes('administrative_area_level_1')) {
            locationData.state = component.long_name;
          }
          if (types.includes('country')) {
            locationData.country = component.long_name;
          }
          if (types.includes('postal_code')) {
            locationData.postalCode = component.long_name;
          }
          // For area/neighborhood
          if (types.includes('neighborhood') || types.includes('sublocality')) {
            locationData.area = component.long_name;
          }
        });

        place.value = locationData;
        error.value = null;
        onPlaceSelect(locationData);
      } catch (err) {
        error.value = 'Error processing location: ' + err.message;
        console.error('Google Maps error:', err);
      } finally {
        isLoading.value = false;
      }
    });

    return autocomplete;
  };

  onMounted(() => {
    // Initialize after Vue component mounts
    if (window.google?.maps?.places) {
      setTimeout(() => {
        initializeAutocomplete();
      }, 100);
    } else {
      console.warn('Google Maps API not available');
    }
  });

  return {
    place,
    isLoading,
    error,
    initializeAutocomplete
  };
}

export function loadGoogleMapsAPI() {
  return new Promise((resolve) => {
    if (window.google?.maps) {
      resolve(true);
      return;
    }

    const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;
    if (!apiKey) {
      console.error('Google Maps API key not configured in VITE_GOOGLE_MAPS_API_KEY');
      resolve(false);
      return;
    }

    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&language=en`;
    script.async = true;
    script.defer = true;
    script.onload = () => {
      mapsLoaded.value = true;
      resolve(true);
    };
    script.onerror = () => {
      console.error('Failed to load Google Maps API');
      resolve(false);
    };
    document.head.appendChild(script);
  });
}
