<template>
  <div class="bg-gradient-to-br from-pink-500 via-purple-500 to-indigo-600 rounded-2xl shadow-xl p-8 text-white">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold mb-2">
          <i class="fas fa-gift mr-2"></i>Earn 50 Coins Per Referral!
        </h2>
        <p class="text-white/90 text-sm">
          Share your code with friends and earn coins when they sign up
        </p>
      </div>
      <div class="text-right hidden md:block">
        <div class="bg-white/20 rounded-full px-4 py-2 backdrop-blur-sm">
          <i class="fas fa-users mr-2"></i>
          <span class="font-bold">{{ stats.total_referrals || 0 }}</span> Referrals
        </div>
      </div>
    </div>

    <!-- Referral Code Display -->
    <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 mb-6 border border-white/20">
      <label class="text-white/80 text-sm font-medium mb-2 block">Your Referral Code</label>
      <div class="flex items-center gap-3 flex-wrap">
        <code class="bg-white text-pink-600 px-6 py-3 rounded-lg text-2xl font-bold tracking-wider flex-grow text-center">
          {{ referralCode }}
        </code>
        <button
          @click="copyCode"
          class="bg-white/20 hover:bg-white/30 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center gap-2 backdrop-blur-sm"
        >
          <i :class="copied ? 'fas fa-check' : 'fas fa-copy'"></i>
          <span>{{ copied ? 'Copied!' : 'Copy Code' }}</span>
        </button>
      </div>
    </div>

    <!-- Referral Link -->
    <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 mb-6 border border-white/20">
      <label class="text-white/80 text-sm font-medium mb-2 block">Share This Link</label>
      <div class="flex items-center gap-3 flex-wrap">
        <input
          :value="referralLink"
          readonly
          class="bg-white/5 text-white px-4 py-3 rounded-lg flex-grow outline-none font-mono text-sm border border-white/10"
        />
        <button
          @click="copyLink"
          class="bg-white/20 hover:bg-white/30 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center gap-2 backdrop-blur-sm"
        >
          <i :class="linkCopied ? 'fas fa-check' : 'fas fa-link'"></i>
          <span>{{ linkCopied ? 'Copied!' : 'Copy Link' }}</span>
        </button>
      </div>
    </div>

    <!-- Share Options -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
      <button
        @click="shareVia('whatsapp')"
        class="bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2"
      >
        <i class="fab fa-whatsapp text-xl"></i>
        <span class="hidden sm:inline">WhatsApp</span>
      </button>
      <button
        @click="shareVia('facebook')"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2"
      >
        <i class="fab fa-facebook-f text-xl"></i>
        <span class="hidden sm:inline">Facebook</span>
      </button>
      <button
        @click="shareVia('twitter')"
        class="bg-sky-500 hover:bg-sky-600 text-white px-4 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2"
      >
        <i class="fab fa-twitter text-xl"></i>
        <span class="hidden sm:inline">Twitter</span>
      </button>
      <button
        @click="shareVia('email')"
        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2"
      >
        <i class="fas fa-envelope text-xl"></i>
        <span class="hidden sm:inline">Email</span>
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
      <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 text-center border border-white/20">
        <i class="fas fa-users text-2xl mb-2"></i>
        <p class="text-2xl font-bold">{{ stats.total_referrals || 0 }}</p>
        <p class="text-white/80 text-sm">Total Referrals</p>
      </div>
      <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 text-center border border-white/20">
        <i class="fas fa-coins text-yellow-300 text-2xl mb-2"></i>
        <p class="text-2xl font-bold">{{ stats.total_coins_earned || 0 }}</p>
        <p class="text-white/80 text-sm">Coins Earned</p>
      </div>
      <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 text-center border border-white/20 col-span-2 md:col-span-1">
        <i class="fas fa-gift text-2xl mb-2"></i>
        <p class="text-2xl font-bold">50 + 25</p>
        <p class="text-white/80 text-sm">You + Friend Reward</p>
      </div>
    </div>

    <!-- How it works -->
    <div class="mt-6 bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
      <p class="font-semibold mb-3 flex items-center gap-2">
        <i class="fas fa-lightbulb"></i>
        How It Works
      </p>
      <ol class="text-sm space-y-2 text-white/90">
        <li class="flex items-start gap-2">
          <span class="font-bold">1.</span>
          <span>Share your referral code or link with friends</span>
        </li>
        <li class="flex items-start gap-2">
          <span class="font-bold">2.</span>
          <span>They sign up using your code</span>
        </li>
        <li class="flex items-start gap-2">
          <span class="font-bold">3.</span>
          <span>You get <strong>50 coins</strong>, they get <strong>25 coins</strong> instantly!</span>
        </li>
        <li class="flex items-start gap-2">
          <span class="font-bold">4.</span>
          <span>There's <strong>no limit</strong> - refer as many friends as you want!</span>
        </li>
      </ol>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'ReferralShareCard',
  props: {
    referralCode: {
      type: String,
      required: true
    },
    stats: {
      type: Object,
      default: () => ({
        total_referrals: 0,
        total_coins_earned: 0
      })
    }
  },
  setup(props) {
    const copied = ref(false);
    const linkCopied = ref(false);

    const referralLink = computed(() => {
      return `${window.location.origin}/register?ref=${props.referralCode}`;
    });

    const shareMessage = computed(() => {
      return `Join Namate24 using my referral code ${props.referralCode} and get 25 bonus coins! 🎁`;
    });

    const copyCode = async () => {
      try {
        await navigator.clipboard.writeText(props.referralCode);
        copied.value = true;
        setTimeout(() => {
          copied.value = false;
        }, 2000);
      } catch (err) {
        console.error('Failed to copy code:', err);
      }
    };

    const copyLink = async () => {
      try {
        await navigator.clipboard.writeText(referralLink.value);
        linkCopied.value = true;
        setTimeout(() => {
          linkCopied.value = false;
        }, 2000);
      } catch (err) {
        console.error('Failed to copy link:', err);
      }
    };

    const shareVia = (platform) => {
      const url = referralLink.value;
      const text = shareMessage.value;
      
      let shareUrl = '';
      
      switch (platform) {
        case 'whatsapp':
          shareUrl = `https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`;
          break;
        case 'facebook':
          shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}&quote=${encodeURIComponent(text)}`;
          break;
        case 'twitter':
          shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`;
          break;
        case 'email':
          shareUrl = `mailto:?subject=${encodeURIComponent('Join Namate24!')}&body=${encodeURIComponent(text + '\n\n' + url)}`;
          break;
      }
      
      if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
      }
    };

    return {
      copied,
      linkCopied,
      referralLink,
      copyCode,
      copyLink,
      shareVia
    };
  }
};
</script>

<style scoped>
/* Add any additional styles if needed */
</style>
