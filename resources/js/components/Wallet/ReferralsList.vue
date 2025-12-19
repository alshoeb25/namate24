<template>
  <div class="bg-white rounded-2xl shadow-md p-6">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-bold text-gray-800">
        <i class="fas fa-users mr-2 text-purple-600"></i>Your Referrals
      </h3>
      <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm font-semibold">
        {{ referrals.length }} Total
      </span>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <i class="fas fa-spinner fa-spin text-3xl text-purple-600"></i>
      <p class="text-gray-600 mt-2">Loading referrals...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!referrals || referrals.length === 0" class="text-center py-12">
      <div class="text-6xl mb-4">👥</div>
      <h4 class="text-lg font-semibold text-gray-800 mb-2">No referrals yet</h4>
      <p class="text-gray-600 mb-4">Share your referral code to start earning coins!</p>
    </div>

    <!-- Referrals List -->
    <div v-else class="space-y-4">
      <div
        v-for="referral in referrals"
        :key="referral.id"
        class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
      >
        <div class="flex items-center gap-4">
          <!-- Avatar -->
          <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-white font-bold text-lg">
            {{ getInitials(referral.referred?.name) }}
          </div>
          
          <!-- User Info -->
          <div>
            <p class="font-semibold text-gray-800">{{ referral.referred?.name || 'User' }}</p>
            <p class="text-sm text-gray-500">
              {{ referral.referred?.email }}
            </p>
            <p class="text-xs text-gray-400 mt-1">
              <i class="far fa-calendar mr-1"></i>
              Joined {{ formatDate(referral.created_at) }}
            </p>
          </div>
        </div>

        <!-- Coins Earned -->
        <div class="text-right">
          <div class="flex items-center gap-2 text-green-600 font-bold">
            <i class="fas fa-coins"></i>
            <span>+{{ referral.referrer_coins }}</span>
          </div>
          <div v-if="referral.reward_given" class="text-xs text-green-600 mt-1">
            <i class="fas fa-check-circle mr-1"></i>Rewarded
          </div>
        </div>
      </div>
    </div>

    <!-- View More Button -->
    <div v-if="referrals.length > 5 && !showAll" class="mt-6 text-center">
      <button
        @click="showAll = true"
        class="text-purple-600 hover:text-purple-700 font-semibold text-sm"
      >
        View All Referrals <i class="fas fa-arrow-down ml-1"></i>
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'ReferralsList',
  props: {
    referrals: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const showAll = ref(false);

    const displayedReferrals = computed(() => {
      if (showAll.value) {
        return props.referrals;
      }
      return props.referrals.slice(0, 5);
    });

    const getInitials = (name) => {
      if (!name) return '?';
      const parts = name.split(' ');
      if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
      }
      return name.substring(0, 2).toUpperCase();
    };

    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      const now = new Date();
      const diffTime = Math.abs(now - date);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      
      if (diffDays === 0) return 'Today';
      if (diffDays === 1) return 'Yesterday';
      if (diffDays < 7) return `${diffDays} days ago`;
      if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
      if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`;
      
      return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
      });
    };

    return {
      showAll,
      displayedReferrals,
      getInitials,
      formatDate
    };
  }
};
</script>
