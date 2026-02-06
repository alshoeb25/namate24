<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Address</h2>
    
    <form @submit.prevent="updateAddress">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Search Address</label>
          <input
            ref="autocompleteInput"
            type="text"
            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="Start typing your address"
          />
          <p class="text-sm text-gray-500 mt-1">Powered by Google Places</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Street Address</label>
          <input 
            v-model="form.address" 
            type="text" 
            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="123 Main Street"
          />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">City</label>
            <input 
              v-model="form.city" 
              type="text" 
              class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              placeholder="New York"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">State</label>
            <input 
              v-model="form.state" 
              type="text" 
              class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              placeholder="NY"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Zip Code</label>
            <input 
              v-model="form.zip_code" 
              type="text" 
              class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              placeholder="10001"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Country</label>
            <input 
              v-model="form.country" 
              type="text" 
              class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              placeholder="United States"
            />
          </div>
        </div>

        <div class="flex gap-4 pt-4">
          <button 
            type="submit" 
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            :disabled="loading"
          >
            {{ loading ? 'Saving...' : 'Save Address' }}
          </button>
          <button 
            type="button" 
            @click="resetForm" 
            class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition"
          >
            Cancel
          </button>
        </div>

        <!-- Messages -->
        <div v-if="message" class="p-4 bg-green-100 text-green-700 rounded-lg">
          {{ message }}
        </div>
        <div v-if="error" class="p-4 bg-red-100 text-red-700 rounded-lg">
          {{ error }}
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios';
import { loadGoogleMaps } from '../../../utils/googleMaps';

export default {
  name: 'Address',
  data() {
    return {
      form: {
        address: '',
        city: '',
        state: '',
        zip_code: '',
        country: '',
      },
      loading: false,
      message: '',
      error: '',
      autocomplete: null,
    };
  },
  mounted() {
    this.initGoogleAutocomplete();
    this.fetchAddress();
  },
  methods: {
    async initGoogleAutocomplete() {
      try {
        await loadGoogleMaps();

        if (!this.$refs.autocompleteInput || !window.google?.maps?.places?.Autocomplete) {
          console.warn('Google Maps Places API not available');
          return;
        }

        this.autocomplete = new window.google.maps.places.Autocomplete(
          this.$refs.autocompleteInput,
          { types: ['geocode'] }
        );

        this.autocomplete.addListener('place_changed', () => {
          const place = this.autocomplete.getPlace();
          if (!place || !place.address_components) return;
          this.applyPlaceResult(place);
        });

        this.setAutocompleteValue();
      } catch (err) {
        console.error('Failed to load Google Maps', err);
      }
    },
    setAutocompleteValue() {
      if (this.$refs.autocompleteInput && this.form.address) {
        this.$refs.autocompleteInput.value = this.form.address;
      }
    },
    applyPlaceResult(place) {
      const updated = {
        address: place.formatted_address || this.form.address,
        city: this.form.city,
        state: this.form.state,
        zip_code: this.form.zip_code,
        country: this.form.country,
      };

      place.address_components.forEach((component) => {
        const types = component.types || [];
        if (types.includes('locality')) updated.city = component.long_name;
        if (types.includes('administrative_area_level_1')) updated.state = component.long_name;
        if (types.includes('postal_code')) updated.zip_code = component.long_name;
        if (types.includes('country')) updated.country = component.long_name;
      });

      this.form = { ...this.form, ...updated };
    },
    async fetchAddress() {
      try {
        this.loading = true;
        const response = await axios.get('/api/tutor/profile/address');
        this.form = {
          address: response.data.address || '',
          city: response.data.city || '',
          state: response.data.state || '',
          zip_code: response.data.zip_code || '',
          country: response.data.country || '',
        };
        this.setAutocompleteValue();
      } catch (err) {
        this.error = 'Failed to load address';
      } finally {
        this.loading = false;
      }
    },
    async updateAddress() {
      try {
        this.loading = true;
        this.error = '';
        const response = await axios.post('/api/tutor/profile/address', this.form);
        this.message = response.data?.message || 'Data saved successfully.';
        setTimeout(() => this.message = '', 3000);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to update address';
      } finally {
        this.loading = false;
      }
    },
    resetForm() {
      this.fetchAddress();
      this.message = '';
      this.error = '';
    },
  },
};
</script>
