<template>
  <div v-if="loading" class="flex items-center justify-center min-h-screen">
    <div class="text-xl text-gray-600">Loading...</div>
  </div>

  <div v-else class="max-w-4xl mx-auto px-4 py-8">
    <!-- Back Button -->
    <div class="mb-6">
      <button @click="$router.back()" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i> Back
      </button>
    </div>

    <!-- Requirement Card -->
    <div class="bg-white rounded-xl shadow-lg p-8">
      <!-- Header -->
      <div class="mb-6 pb-6 border-b border-gray-200">
        <div class="flex items-start justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
              {{ requirement.subject?.name || 'Subject Required' }}
            </h1>
            <div class="flex items-center gap-4 text-sm text-gray-600">
              <span class="flex items-center gap-1">
                <i class="fas fa-map-marker-alt"></i>
                {{ requirement.city || 'Location not specified' }}
              </span>
              <span class="flex items-center gap-1">
                <i class="fas fa-clock"></i>
                Posted {{ formatDate(requirement.created_at) }}
              </span>
            </div>
          </div>
          <div class="flex flex-col items-end gap-2">
            <div v-if="requirement.has_unlocked" class="px-4 py-2 bg-green-100 text-green-600 rounded-full text-sm font-medium">
              <i class="fas fa-unlock"></i> Unlocked
            </div>
            <div v-else-if="requirement.unlock_price" class="px-4 py-2 bg-yellow-100 text-yellow-600 rounded-full text-sm font-medium">
              <i class="fas fa-lock"></i> {{ requirement.unlock_price }} coins to unlock
            </div>
            <div v-if="requirement.lead_info" class="text-sm text-gray-600">
              {{ requirement.lead_info.spots_available }}/{{ requirement.lead_info.max_leads }} spots available
            </div>
          </div>
        </div>
      </div>

      <!-- Details Section -->
      <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-3">Requirement Details</h2>
        <p v-if="requirement.details" class="text-gray-700 leading-relaxed whitespace-pre-line">
          {{ requirement.details }}
        </p>
        <p v-else class="text-gray-500 italic">No additional details provided</p>
      </div>

      <!-- Info Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Budget -->
        <div class="bg-green-50 rounded-lg p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <i class="fas fa-rupee-sign text-green-600 text-xl"></i>
            </div>
            <div>
              <div class="text-sm text-gray-600">Budget</div>
              <div class="text-lg font-semibold text-gray-900">{{ getBudgetDisplay() }}</div>
            </div>
          </div>
        </div>

        <!-- Mode -->
        <div class="bg-blue-50 rounded-lg p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <i class="fas fa-laptop text-blue-600 text-xl"></i>
            </div>
            <div>
              <div class="text-sm text-gray-600">Teaching Mode</div>
              <div class="text-lg font-semibold text-gray-900">{{ getModeText() }}</div>
            </div>
          </div>
        </div>

        <!-- Level -->
        <div v-if="requirement.level" class="bg-purple-50 rounded-lg p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <i class="fas fa-graduation-cap text-purple-600 text-xl"></i>
            </div>
            <div>
              <div class="text-sm text-gray-600">Level</div>
              <div class="text-lg font-semibold text-gray-900">{{ requirement.level }}</div>
            </div>
          </div>
        </div>

        <!-- Gender Preference -->
        <div v-if="requirement.gender_preference" class="bg-pink-50 rounded-lg p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
              <i class="fas fa-user text-pink-600 text-xl"></i>
            </div>
            <div>
              <div class="text-sm text-gray-600">Gender Preference</div>
              <div class="text-lg font-semibold text-gray-900">{{ requirement.gender_preference }}</div>
            </div>
          </div>
        </div>

        <!-- Start Date -->
        <div v-if="requirement.desired_start" class="bg-yellow-50 rounded-lg p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
              <i class="fas fa-calendar text-yellow-600 text-xl"></i>
            </div>
            <div>
              <div class="text-sm text-gray-600">Desired Start Date</div>
              <div class="text-lg font-semibold text-gray-900">{{ formatDate(requirement.desired_start) }}</div>
            </div>
          </div>
        </div>

        <!-- Languages -->
        <div v-if="requirement.languages && requirement.languages.length > 0" class="bg-indigo-50 rounded-lg p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
              <i class="fas fa-language text-indigo-600 text-xl"></i>
            </div>
            <div>
              <div class="text-sm text-gray-600">Languages</div>
              <div class="text-lg font-semibold text-gray-900">{{ requirement.languages.join(', ') }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-3 pt-6 border-t border-gray-200">
        <button 
          v-if="!requirement.has_unlocked"
          @click="openUnlockModal" 
          :disabled="unlocking || requirement.lead_info?.is_full"
          :class="[
            'flex-1 py-3 rounded-lg font-medium transition-colors flex items-center justify-center gap-2',
            requirement.lead_info?.is_full 
              ? 'bg-gray-400 text-white cursor-not-allowed'
              : 'bg-green-600 text-white hover:bg-green-700'
          ]"
        >
          <i :class="unlocking ? 'fas fa-spinner fa-spin' : 'fas fa-unlock'"></i>
          {{ unlocking ? 'Unlocking...' : requirement.lead_info?.is_full ? 'No Spots Available' : `Unlock Contact (${requirement.unlock_price || 0} coins)` }}
        </button>
        <button 
          v-if="requirement.has_unlocked"
          @click="contact" 
          class="flex-1 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center justify-center gap-2"
        >
          <i class="fas fa-phone"></i>
          Show Contact Details
        </button>
        <button 
          @click="share" 
          class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
        >
          <i class="fas fa-share-alt"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Unlock Terms & Conditions Modal -->
  <div v-if="showUnlockModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-bold text-gray-800">Unlock Contact Details</h2>
          <button @click="closeUnlockModal" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>

        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
          <div class="flex items-center">
            <i class="fas fa-coins text-yellow-600 text-2xl mr-3"></i>
            <div>
              <p class="font-semibold text-yellow-800">{{ requirement.unlock_price || 0 }} Coins Required</p>
            </div>
          </div>
        </div>

        <div class="mb-6">
          <label class="flex items-start gap-3 text-sm text-gray-700">
            <input type="checkbox" v-model="acceptedPolicies" class="mt-1" />
            <span>
              I have read and agree to the
              <router-link to="/terms-and-conditions" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">Terms and Conditions</router-link>
              and the
              <router-link to="/safety-documents" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">Safety Documents</router-link>.
            </span>
          </label>
        </div>

        <div class="flex gap-3">
          <button 
            @click="closeUnlockModal" 
            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-3 rounded-lg font-medium transition">
            Reject
          </button>
          <button 
            @click="confirmUnlock"
            :disabled="unlocking || !acceptedPolicies || requirement.lead_info?.is_full"
            :class="unlocking || !acceptedPolicies || requirement.lead_info?.is_full ? 'bg-gray-400' : 'bg-green-600 hover:bg-green-700'"
            class="flex-1 text-white py-3 rounded-lg font-medium transition">
            <span v-if="unlocking">
              <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
            </span>
            <span v-else>Accept & Unlock ({{ requirement.unlock_price || 0 }} coins)</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useUserStore } from '../store';
import axios from 'axios';

export default {
  name: 'RequirementDetail',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const userStore = useUserStore();
    const requirement = ref({});
    const loading = ref(true);
    const unlocking = ref(false);
    const showUnlockModal = ref(false);
    const acceptedPolicies = ref(false);

    function isProfileNotApproved(error) {
      const status = error?.response?.status;
      const message = String(error?.response?.data?.message || '').toLowerCase();
      return status === 403 && (message.includes('not verified') || message.includes('not approved'));
    }

    async function loadRequirement() {
      loading.value = true;
      try {
        const res = await axios.get(`/api/enquiries/${route.params.id}`);
        requirement.value = res.data.enquiry || res.data;
      } catch (error) {
        console.error('Error loading requirement:', error);
        if (isProfileNotApproved(error)) {
          router.push('/tutor/profile/not-approved');
          return;
        }
        if (error.response?.status === 404) {
          router.push('/tutor-jobs');
        }
      } finally {
        loading.value = false;
      }
    }

    async function unlock() {
      if (unlocking.value) return;
      
      unlocking.value = true;
      try {
        const res = await axios.post(`/api/enquiries/${route.params.id}/unlock`);
        
        // Update requirement with unlocked data
        requirement.value = res.data.enquiry;
        
        // Update wallet balance in user store if coins were charged
        if (res.data.charged && userStore.user) {
          userStore.user.coins -= (requirement.value.unlock_price || 0);
        }
        
        alert(`Contact unlocked successfully! ${res.data.charged ? `${requirement.value.unlock_price || 0} coins deducted from your balance.` : 'Already unlocked.'}`);
      } catch (error) {
        console.error('Error unlocking requirement:', error);
        if (isProfileNotApproved(error)) {
          router.push('/tutor/profile/not-approved');
          return;
        }
        const message = error.response?.data?.message || 'Failed to unlock requirement';
        alert(message);
      } finally {
        unlocking.value = false;
      }
    }

    function openUnlockModal() {
      acceptedPolicies.value = false;
      showUnlockModal.value = true;
    }

    function closeUnlockModal() {
      showUnlockModal.value = false;
      acceptedPolicies.value = false;
    }

    async function confirmUnlock() {
      if (!acceptedPolicies.value) {
        alert('Please accept the Terms and Conditions and Safety Documents.');
        return;
      }
      await unlock();
      if (!unlocking.value) {
        closeUnlockModal();
      }
    }

    function formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    }

    function getBudgetDisplay() {
      if (requirement.value.budget_min && requirement.value.budget_max) {
        return `₹${requirement.value.budget_min} - ₹${requirement.value.budget_max}`;
      }
      if (requirement.value.budget_min) {
        return `₹${requirement.value.budget_min}+`;
      }
      if (requirement.value.budget_max) {
        return `Up to ₹${requirement.value.budget_max}`;
      }
      return 'Negotiable';
    }

    function getModeText() {
      const modes = {
        'online': 'Online',
        'offline': 'Offline',
        'both': 'Both Online & Offline'
      };
      return modes[requirement.value.mode] || requirement.value.mode;
    }

    function getStatusColor() {
      const colors = {
        'open': 'bg-green-100 text-green-600',
        'closed': 'bg-red-100 text-red-600',
        'pending': 'bg-yellow-100 text-yellow-600'
      };
      return colors[requirement.value.status] || 'bg-gray-100 text-gray-600';
    }

    function contact() {
      if (requirement.value.phone) {
        const message = `Student Contact Details:\n\nName: ${requirement.value.student_name || 'Not provided'}\nPhone: ${requirement.value.phone}${requirement.value.alternate_phone ? '\nAlternate: ' + requirement.value.alternate_phone : ''}`;
        alert(message);
      } else {
        alert('Contact information not available. Please unlock first.');
      }
    }

    function share() {
      if (navigator.share) {
        navigator.share({
          title: requirement.value.subject?.name,
          text: `Check out this tutoring opportunity: ${requirement.value.subject?.name}`,
          url: window.location.href
        });
      } else {
        alert('Share feature not supported');
      }
    }

    onMounted(() => {
      loadRequirement();
    });

    return {
      requirement,
      loading,
      unlocking,
      showUnlockModal,
      acceptedPolicies,
      formatDate,
      getBudgetDisplay,
      getModeText,
      getStatusColor,
      unlock,
      openUnlockModal,
      closeUnlockModal,
      confirmUnlock,
      contact,
      share
    };
  }
};
</script>
