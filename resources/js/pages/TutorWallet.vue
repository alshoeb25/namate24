<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <!-- Toast Notification -->
    <div
      v-if="toast.show"
      :class="[
        'fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-xl transition-all duration-300 transform',
        toast.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
      ]"
    >
      <div class="flex items-center gap-3">
        <i :class="toast.type === 'success' ? 'fas fa-check-circle text-2xl' : 'fas fa-exclamation-circle text-2xl'"></i>
        <span class="font-medium">{{ toast.message }}</span>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4">
      <!-- Header with Balance -->
      <div class="bg-gradient-to-r from-pink-500 to-purple-600 rounded-2xl shadow-lg p-8 mb-6 text-white">
        <div class="flex justify-between items-center flex-wrap gap-4">
          <div>
            <h1 class="text-3xl font-bold mb-2">
              <i class="fas fa-wallet mr-2"></i>My Wallet
            </h1>
            <p class="text-pink-100">Manage your coins and transactions</p>
          </div>
          <div class="text-right">
            <p class="text-sm opacity-90">Available Coins</p>
            <p class="text-5xl font-bold flex items-center gap-2">
              <i class="fas fa-coins text-yellow-300"></i>{{ wallet.balance || 0 }}
            </p>
          </div>
        </div>
        
        <!-- Referral Info -->
        <div v-if="wallet.referral_code" class="mt-6 pt-6 border-t border-pink-400/30">
          <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
              <p class="text-sm text-pink-100 mb-1">Your Referral Code</p>
              <div class="flex items-center gap-3">
                <code class="bg-white/20 px-4 py-2 rounded-lg text-lg font-bold">{{ wallet.referral_code }}</code>
                <button @click="copyReferralCode" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition">
                  <i class="fas fa-copy mr-2"></i>Copy
                </button>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm text-pink-100">Referrals: {{ wallet.referral_stats?.total_referrals || 0 }}</p>
              <p class="text-lg font-semibold">Earned: {{ wallet.referral_stats?.coins_earned || 0 }} coins</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="bg-white rounded-2xl shadow-md mb-6 overflow-hidden">
        <div class="flex border-b border-gray-200">
          <button
            @click="activeTab = 'buy'"
            :class="[
              'flex-1 px-6 py-4 font-semibold transition-all',
              activeTab === 'buy'
                ? 'bg-gradient-to-r from-pink-500 to-purple-600 text-white'
                : 'text-gray-600 hover:bg-gray-50'
            ]"
          >
            <i class="fas fa-shopping-cart mr-2"></i>Buy Coins
          </button>
          <button
            @click="activeTab = 'referral'"
            :class="[
              'flex-1 px-6 py-4 font-semibold transition-all',
              activeTab === 'referral'
                ? 'bg-gradient-to-r from-pink-500 to-purple-600 text-white'
                : 'text-gray-600 hover:bg-gray-50'
            ]"
          >
            <i class="fas fa-gift mr-2"></i>Refer & Earn
          </button>
          <button
            @click="activeTab = 'transactions'"
            :class="[
              'flex-1 px-6 py-4 font-semibold transition-all',
              activeTab === 'transactions'
                ? 'bg-gradient-to-r from-pink-500 to-purple-600 text-white'
                : 'text-gray-600 hover:bg-gray-50'
            ]"
          >
            <i class="fas fa-history mr-2"></i>Transactions
          </button>
        </div>
      </div>

      <!-- Tab Content -->
      <div class="mb-6">
        <!-- Buy Coins Tab -->
        <div v-show="activeTab === 'buy'">
          <BuyCoins
            :packages="packages"
            :loading="loading"
            @purchase="purchasePackage"
          />
        </div>

        <!-- Referral Tab -->
        <div v-show="activeTab === 'referral'">
          <ReferralShareCard
            v-if="wallet.referral_code"
            :referralCode="wallet.referral_code"
            :stats="wallet.referral_stats"
          />
        </div>

        <!-- Transactions Tab -->
        <div v-show="activeTab === 'transactions'">

          <!-- Transaction History -->
          <div class="bg-white rounded-2xl shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">
              <i class="fas fa-history mr-2"></i>Transaction History
            </h2>
            
            <div v-if="!wallet.transactions || wallet.transactions.data.length === 0" class="text-center py-12 text-gray-500">
              <i class="fas fa-receipt text-6xl mb-4 text-gray-300"></i>
              <p>No transactions yet</p>
            </div>
            
            <div v-else class="space-y-3">
              <div
                v-for="transaction in wallet.transactions.data"
                :key="transaction.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition"
              >
                <div class="flex items-center gap-4">
                  <div
                    class="w-12 h-12 rounded-full flex items-center justify-center"
                    :class="getTransactionIconClass(transaction.type)"
                  >
                    <i :class="getTransactionIcon(transaction.type)" class="text-lg"></i>
                  </div>
                  <div>
                    <p class="font-semibold text-gray-800">{{ transaction.description }}</p>
                    <p class="text-sm text-gray-500">{{ formatDate(transaction.created_at) }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p
                    class="text-lg font-bold"
                    :class="transaction.amount > 0 ? 'text-green-600' : 'text-red-600'"
                  >
                    {{ transaction.amount > 0 ? '+' : '' }}{{ transaction.amount }}
                    <i class="fas fa-coins text-yellow-500 text-sm"></i>
                  </p>
                  <p class="text-xs text-gray-500">Balance: {{ transaction.balance_after }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import ReferralShareCard from '../components/Wallet/ReferralShareCard.vue';
import BuyCoins from '../components/Wallet/BuyCoins.vue';

export default {
  name: 'TutorWallet',
  components: {
    ReferralShareCard,
    BuyCoins
  },
  setup() {
    const wallet = ref({});
    const packages = ref([]);
    const loading = ref(true);
    const activeTab = ref('buy');

    const fetchWallet = async () => {
      try {
        const { data } = await axios.get('/api/wallet');
        wallet.value = data;
      } catch (error) {
        console.error('Failed to fetch wallet:', error);
      }
    };

    const fetchPackages = async () => {
      try {
        loading.value = true;
        const { data } = await axios.get('/api/wallet/packages');
        packages.value = data;
      } catch (error) {
        console.error('Failed to fetch packages:', error);
      } finally {
        loading.value = false;
      }
    };

    const toast = ref({ show: false, message: '', type: 'success' });

    const showToast = (message, type = 'success') => {
      toast.value = { show: true, message, type };
      setTimeout(() => {
        toast.value.show = false;
      }, 3000);
    };

    const purchasePackage = async (pkg) => {
      if (!window.Razorpay) {
        showToast('Payment gateway not loaded. Please refresh the page.', 'error');
        return;
      }

      try {
        // Create Razorpay order
        const { data } = await axios.post('/api/wallet/purchase', {
          package_id: pkg.id
        });

        // Initialize Razorpay
        const options = {
          key: import.meta.env.VITE_RAZORPAY_KEY || 'rzp_test_xxxxxxxxxxxx',
          amount: data.order.amount,
          currency: 'INR',
          order_id: data.order.id,
          name: 'Namate24',
          description: `Purchase ${pkg.name}`,
          image: 'https://image2url.com/images/1765179057005-967d0875-ac5d-4a43-b65f-a58abd9f651d.png',
          handler: async function (response) {
            try {
              // Verify payment
              await axios.post('/api/wallet/verify-payment', {
                transaction_id: data.transaction_id,
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_order_id: response.razorpay_order_id,
                razorpay_signature: response.razorpay_signature
              });

              // Reload wallet
              await fetchWallet();
              showToast(`Payment successful! ${pkg.coins} coins added to your wallet.`, 'success');
            } catch (error) {
              console.error('Payment verification failed:', error);
              const errorMsg = error.response?.data?.message || 'Payment verification failed. Please contact support.';
              showToast(errorMsg, 'error');
            }
          },
          prefill: {
            name: '',
            email: '',
            contact: ''
          },
          theme: {
            color: '#ec4899'
          },
          modal: {
            ondismiss: function() {
              showToast('Payment cancelled', 'error');
            }
          }
        };

        const rzp = new window.Razorpay(options);
        rzp.on('payment.failed', function (response) {
          console.error('Payment failed:', response.error);
          showToast(`Payment failed: ${response.error.description}`, 'error');
        });
        rzp.open();
      } catch (error) {
        console.error('Failed to initiate purchase:', error);
        const errorMsg = error.response?.data?.message || 'Failed to initiate purchase. Please try again.';
        showToast(errorMsg, 'error');
      }
    };

    const copyReferralCode = () => {
      navigator.clipboard.writeText(wallet.value.referral_code);
      showToast('Referral code copied to clipboard!', 'success');
    };

    const getTransactionIconClass = (type) => {
      const classes = {
        purchase: 'bg-green-100',
        referral_bonus: 'bg-blue-100',
        referral_reward: 'bg-purple-100',
        booking: 'bg-red-100',
        refund: 'bg-yellow-100',
        admin_credit: 'bg-green-100',
        admin_debit: 'bg-red-100'
      };
      return classes[type] || 'bg-gray-100';
    };

    const getTransactionIcon = (type) => {
      const icons = {
        purchase: 'fas fa-shopping-cart text-green-600',
        referral_bonus: 'fas fa-gift text-blue-600',
        referral_reward: 'fas fa-users text-purple-600',
        booking: 'fas fa-calendar-check text-red-600',
        refund: 'fas fa-undo text-yellow-600',
        admin_credit: 'fas fa-plus text-green-600',
        admin_debit: 'fas fa-minus text-red-600'
      };
      return icons[type] || 'fas fa-circle text-gray-600';
    };

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('en-IN', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    onMounted(() => {
      fetchWallet();
      fetchPackages();
    });

    return {
      wallet,
      packages,
      loading,
      toast,
      activeTab,
      purchasePackage,
      copyReferralCode,
      getTransactionIconClass,
      getTransactionIcon,
      formatDate,
      showToast
    };
  }
};
</script>
