<template>
  <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-all duration-300">
    <!-- Header -->
    <div class="flex items-start justify-between mb-4">
      <div class="flex-1">
        <h3 class="text-xl font-bold text-gray-900 mb-2">
          {{ getSubjectsDisplay() }}
        </h3>
        <div class="flex flex-col gap-2">
          <!-- Location with Address Details -->
          <div class="flex items-start gap-2">
            <i class="fas fa-map-marker-alt text-red-500 mt-1"></i>
            <div class="flex-1">
              <div class="text-sm text-gray-600 font-medium">
                {{ requirement.city || 'Location not specified' }}
              </div>
              <div v-if="requirement.area" class="text-xs text-gray-500">
                {{ requirement.area }}
              </div>
              <div v-if="requirement.address" class="text-xs text-gray-500 mt-1 line-clamp-2">
                {{ requirement.address }}
              </div>
            </div>
          </div>

          <!-- Other Details -->
          <div class="flex items-center gap-4 text-sm text-gray-600 flex-wrap">
            <span class="flex items-center gap-1">
              <i class="fas fa-clock"></i>
              {{ formatDate(requirement.created_at) }}
            </span>
            <span v-if="requirement.distance !== undefined && requirement.distance !== null && requirement.distance >= 0" class="flex items-center gap-1 text-blue-600">
              <i class="fas fa-map-marker-alt"></i>
              {{ formatDistance(requirement.distance) }}
            </span>
            <span v-if="requirement.mode" :class="`px-3 py-1 rounded-full text-xs font-medium ${getModeColor()}`">
              {{ getModeText() }}
            </span>
          </div>
        </div>
      </div>
      <div class="flex flex-col items-end gap-2">
        <div v-if="requirement.has_unlocked" class="px-3 py-1 bg-green-100 text-green-600 rounded-full text-xs font-medium">
          <i class="fas fa-unlock"></i> Unlocked
        </div>
        <div v-else-if="requirement.unlock_price" class="px-3 py-1 bg-yellow-100 text-yellow-600 rounded-full text-xs font-medium">
          <i class="fas fa-lock"></i> {{ requirement.unlock_price }} coins
        </div>
        <div v-if="requirement.lead_info" class="text-xs text-gray-600">
          {{ requirement.lead_info.spots_available }}/{{ requirement.lead_info.max_leads }} spots
        </div>
      </div>
    </div>

    <!-- Details -->
    <div class="mb-4">
      <p v-if="requirement.details" class="text-gray-700 leading-relaxed line-clamp-3">
        {{ requirement.details }}
      </p>
      <p v-else class="text-gray-500 italic">No additional details provided</p>
    </div>

    <!-- Budget & Info -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 text-sm">
      <!-- Budget -->
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-rupee-sign text-green-600"></i>
        </div>
        <div>
          <div class="text-gray-500">{{ getFieldLabel('budget', 'Budget') }}</div>
          <div class="font-medium">{{ getBudgetDisplay() }}</div>
        </div>
      </div>

      <!-- Class -->
      <div v-if="requirement.class" class="flex items-center gap-2">
        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-book text-indigo-600"></i>
        </div>
        <div>
          <div class="text-gray-500">Class</div>
          <div class="font-medium">{{ requirement.class }}</div>
        </div>
      </div>

      <!-- Level -->
      <div v-if="requirement.level" class="flex items-center gap-2">
        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-graduation-cap text-blue-600"></i>
        </div>
        <div>
          <div class="text-gray-500">{{ getFieldLabel('level', 'Level') }}</div>
          <div class="font-medium">{{ getDisplayLabel('level', requirement.level) }}</div>
        </div>
      </div>

      <!-- Gender Preference -->
      <div v-if="requirement.gender_preference" class="flex items-center gap-2">
        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-user text-purple-600"></i>
        </div>
        <div>
          <div class="text-gray-500">{{ getFieldLabel('gender_preference', 'Gender') }}</div>
          <div class="font-medium">{{ getDisplayLabel('gender_preference', requirement.gender_preference) }}</div>
        </div>
      </div>

      <!-- Start Date -->
      <div v-if="requirement.desired_start" class="flex items-center gap-2">
        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-calendar text-yellow-600"></i>
        </div>
        <div>
          <div class="text-gray-500">{{ getFieldLabel('desired_start', 'Start Date') }}</div>
          <div class="font-medium">{{ formatDate(requirement.desired_start) }}</div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-3 pt-4 border-t border-gray-200">
      <button 
        v-if="!requirement.has_unlocked"
        @click="unlock" 
        :disabled="unlocking || requirement.lead_info?.is_full"
        :class="[
          'flex-1 py-2.5 rounded-lg font-medium transition-colors flex items-center justify-center gap-2',
          requirement.lead_info?.is_full 
            ? 'bg-gray-400 text-white cursor-not-allowed'
            : 'bg-green-600 text-white hover:bg-green-700'
        ]"
      >
        <i :class="unlocking ? 'fas fa-spinner fa-spin' : 'fas fa-unlock'"></i>
        {{ unlocking ? 'Unlocking...' : requirement.lead_info?.is_full ? 'Full' : `Unlock (${requirement.unlock_price || 0} coins)` }}
      </button>
      <button 
        @click="viewDetails" 
        class="flex-1 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center justify-center gap-2"
      >
        <i class="fas fa-eye"></i>
        View Details
      </button>
      <button 
        v-if="requirement.has_unlocked"
        @click="contact" 
        class="flex-1 py-2.5 border border-green-600 text-green-600 rounded-lg font-medium hover:bg-green-50 transition-colors flex items-center justify-center gap-2"
      >
        <i class="fas fa-envelope"></i>
        Contact Student
      </button>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useUserStore } from '../store';
import axios from 'axios';

export default {
  name: 'RequirementCard',
  props: {
    requirement: {
      type: Object,
      required: true
    },
    fieldLabels: {
      type: Object,
      default: () => ({})
    }
  },
  setup(props) {
    const unlocking = ref(false);
    const router = useRouter();
    const route = useRoute();
    const userStore = useUserStore();

    async function unlock() {
      if (unlocking.value) return;
      
      unlocking.value = true;
      try {
        const res = await axios.post(`/api/enquiries/${props.requirement.id}/unlock`);
        
        // Update requirement with unlocked data
        Object.assign(props.requirement, res.data.enquiry);
        
        // Update wallet balance in user store if coins were charged
        if (res.data.charged && userStore.user) {
          userStore.user.coins -= (props.requirement.unlock_price || 0);
        }
        
        alert(`Unlocked successfully! ${res.data.charged ? `${props.requirement.unlock_price || 0} coins deducted.` : 'Already unlocked.'}`);
      } catch (error) {
        console.error('Error unlocking requirement:', error);
        if (error.response?.status === 401 || error.response?.status === 403) {
          // Not authenticated - redirect to login
          router.push({ name: 'login', query: { redirect: route.fullPath } });
        } else {
          const message = error.response?.data?.message || 'Failed to unlock requirement';
          alert(message);
        }
      } finally {
        unlocking.value = false;
      }
    }

    return {
      unlocking,
      unlock,
      router,
      route
    };
  },
  methods: {
    getSubjectsDisplay() {
      // Try subject_names first (array of subject names)
      if (this.requirement.subject_names && Array.isArray(this.requirement.subject_names) && this.requirement.subject_names.length > 0) {
        return this.requirement.subject_names.join(', ');
      }
      
      // Try subjects array (array of subject objects)
      if (this.requirement.subjects && Array.isArray(this.requirement.subjects) && this.requirement.subjects.length > 0) {
        return this.requirement.subjects.map(s => s.name).join(', ');
      }
      
      // Try single subject object
      if (this.requirement.subject && this.requirement.subject.name) {
        return this.requirement.subject.name;
      }
      
      // Try subject_name string
      if (this.requirement.subject_name) {
        return this.requirement.subject_name;
      }
      
      // Fallback
      return 'Subjects required';
    },

    getFieldLabel(fieldName, fallback) {
      // Get label for field from props or use fallback
      if (this.fieldLabels && this.fieldLabels[fieldName]) {
        return this.fieldLabels[fieldName];
      }
      return fallback;
    },

    getDisplayLabel(fieldName, value) {
      // Get display label for a specific field value from database
      if (!this.fieldLabels) return value;
      
      // If we have a mapping object for this field
      const fieldMap = this.fieldLabels[`${fieldName}_map`];
      if (fieldMap && fieldMap[value]) {
        return fieldMap[value];
      }
      
      return value;
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      const now = new Date();
      const diffInSeconds = Math.floor((now - date) / 1000);
      
      if (diffInSeconds < 60) return 'Just now';
      if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} mins ago`;
      if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
      if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`;
      
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    },
    
    formatDistance(distance) {
      // Distance comes from Elasticsearch in km
      const km = parseFloat(distance);
      if (isNaN(km)) return '';
      
      if (km < 1) {
        return `${Math.round(km * 1000)}m away`;
      } else if (km < 10) {
        return `${km.toFixed(1)}km away`;
      } else {
        return `${Math.round(km)}km away`;
      }
    },
    
    getBudgetDisplay() {
      if (this.requirement.budget_min && this.requirement.budget_max) {
        return `₹${this.requirement.budget_min} - ₹${this.requirement.budget_max}`;
      }
      if (this.requirement.budget_min) {
        return `₹${this.requirement.budget_min}+`;
      }
      if (this.requirement.budget_max) {
        return `Up to ₹${this.requirement.budget_max}`;
      }
      return 'Negotiable';
    },
    
    getModeText() {
      const modes = {
        'online': 'Online',
        'offline': 'Offline',
        'both': 'Both'
      };
      return modes[this.requirement.mode] || this.requirement.mode;
    },
    
    getModeColor() {
      const colors = {
        'online': 'bg-blue-100 text-blue-600',
        'offline': 'bg-green-100 text-green-600',
        'both': 'bg-purple-100 text-purple-600'
      };
      return colors[this.requirement.mode] || 'bg-gray-100 text-gray-600';
    },
    
    getStatusColor() {
      const colors = {
        'open': 'bg-green-100 text-green-600',
        'closed': 'bg-red-100 text-red-600',
        'pending': 'bg-yellow-100 text-yellow-600'
      };
      return colors[this.requirement.status] || 'bg-gray-100 text-gray-600';
    },
    
    viewDetails() {
      this.$router.push({ name: 'requirement.show', params: { id: this.requirement.id } });
    },
    
    contact() {
      if (this.requirement.phone) {
        alert(`Student Contact: ${this.requirement.phone}${this.requirement.alternate_phone ? '\nAlternate: ' + this.requirement.alternate_phone : ''}`);
      } else {
        alert('Contact information not available');
      }
    }
  }
};
</script>
