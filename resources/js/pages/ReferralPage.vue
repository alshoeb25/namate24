<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
          <i class="fas fa-gift mr-2 text-pink-600"></i>Refer & Earn
        </h1>
        <p class="text-gray-600">Invite friends and earn coins together!</p>
      </div>

      <!-- Referral Share Component -->
      <div class="mb-8">
        <ReferralShareCard
          v-if="referralCode"
          :referralCode="referralCode"
          :stats="referralStats"
        />
      </div>

      <!-- How It Works -->
      <div class="bg-white rounded-2xl shadow-md p-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
          <i class="fas fa-lightbulb mr-2 text-yellow-500"></i>How It Works
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="text-center">
            <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-share-alt text-pink-600 text-2xl"></i>
            </div>
            <h3 class="font-bold text-gray-800 mb-2">1. Share Your Code</h3>
            <p class="text-gray-600">Share your unique referral code or link with friends</p>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-user-plus text-blue-600 text-2xl"></i>
            </div>
            <h3 class="font-bold text-gray-800 mb-2">2. Friend Signs Up</h3>
            <p class="text-gray-600">Your friend registers using your referral code</p>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-coins text-green-600 text-2xl"></i>
            </div>
            <h3 class="font-bold text-gray-800 mb-2">3. Earn Rewards</h3>
            <p class="text-gray-600">You both get coins instantly!</p>
          </div>
        </div>

        <!-- Reward Info -->
        <div class="mt-8 bg-gradient-to-r from-pink-50 to-purple-50 border-2 border-pink-200 rounded-xl p-6">
          <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-pink-500 rounded-full flex items-center justify-center flex-shrink-0">
              <i class="fas fa-gift text-white text-xl"></i>
            </div>
            <div>
              <h4 class="font-bold text-gray-800 mb-2 text-lg">Earn 30 Coins Per Referral!</h4>
              <p class="text-gray-700">For each friend who signs up, you get <strong>30 coins</strong> and they get <strong>15 welcome coins</strong>. Rewards apply to your first <strong>5 referrals</strong>.</p>
              <div class="mt-4 flex flex-wrap gap-3">
                <div class="bg-white rounded-lg px-4 py-2 shadow-sm">
                  <span class="text-xs text-gray-500">You Earn</span>
                  <p class="font-bold text-pink-600">30 Coins</p>
                </div>
                <div class="bg-white rounded-lg px-4 py-2 shadow-sm">
                  <span class="text-xs text-gray-500">Friend Gets</span>
                  <p class="font-bold text-blue-600">15 Coins</p>
                </div>
                <div class="bg-white rounded-lg px-4 py-2 shadow-sm">
                  <span class="text-xs text-gray-500">Referral Limit</span>
                  <p class="font-bold text-green-600">5 referrals</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Referral History -->
      <ReferralsList
        :referrals="referrals"
        :loading="loading"
      />
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import ReferralShareCard from '../components/Wallet/ReferralShareCard.vue';
import ReferralsList from '../components/Wallet/ReferralsList.vue';

export default {
  name: 'ReferralPage',
  components: {
    ReferralShareCard,
    ReferralsList
  },
  setup() {
    const referralCode = ref('');
    const referralStats = ref({
      total_referrals: 0,
      total_coins_earned: 0
    });
    const referrals = ref([]);
    const loading = ref(false);

    const fetchReferralInfo = async () => {
      loading.value = true;
      try {
        const { data } = await axios.get('/api/wallet/referral');
        referralStats.value = data.stats;
        referrals.value = data.referrals || [];
        referralCode.value = data.stats.referral_code;
      } catch (error) {
        console.error('Failed to fetch referral info:', error);
      } finally {
        loading.value = false;
      }
    };

    onMounted(() => {
      fetchReferralInfo();
    });

    return {
      referralCode,
      referralStats,
      referrals,
      loading
    };
  }
};
</script>
