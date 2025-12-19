<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Payment History</h1>
        <p class="text-gray-600">View all your past transactions and payment records</p>
      </div>

      <!-- Stats Summary -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm">Total Spent</p>
              <p class="text-2xl font-bold text-gray-800">₹{{ stats.totalSpent.toLocaleString() }}</p>
            </div>
            <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center">
              <i class="fas fa-rupee-sign text-pink-600 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm">Total Coins</p>
              <p class="text-2xl font-bold text-gray-800">{{ stats.totalCoins.toLocaleString() }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
              <i class="fas fa-coins text-yellow-600 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm">Successful</p>
              <p class="text-2xl font-bold text-gray-800">{{ stats.successCount }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
              <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm">Failed</p>
              <p class="text-2xl font-bold text-gray-800">{{ stats.failedCount }}</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
              <i class="fas fa-times-circle text-red-600 text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl p-6 border border-gray-200 mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
          <div>
            <h2 class="text-lg font-bold text-gray-800 mb-2">Filter Transactions</h2>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="filter in filters"
                :key="filter.value"
                @click="currentFilter = filter.value"
                class="px-4 py-2 rounded-lg font-medium transition"
                :class="currentFilter === filter.value ? 'bg-pink-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              >
                {{ filter.label }}
              </button>
            </div>
          </div>

          <div class="w-full md:w-auto">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
              </div>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search by ID, amount..."
                class="pl-10 w-full md:w-64 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Transactions Table -->
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-xl font-bold text-gray-800">Recent Transactions</h2>
          <p class="text-gray-600 text-sm">Showing {{ filteredTransactions.length }} transactions</p>
        </div>

        <!-- Desktop Table -->
        <div class="overflow-x-auto hidden md:block">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Transaction ID</th>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Date & Time</th>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Type</th>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Amount</th>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Balance</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="transaction in filteredTransactions"
                :key="transaction.id"
                class="border-b border-gray-100 hover:bg-gray-50"
              >
                <td class="py-4 px-6">
                  <span class="font-medium text-gray-800">{{ transaction.id }}</span>
                </td>
                <td class="py-4 px-6">
                  <div>{{ formatDate(transaction.created_at) }}</div>
                </td>
                <td class="py-4 px-6">
                  <span class="px-3 py-1 rounded-full text-xs font-medium" :class="getTypeClass(transaction.type)">
                    {{ formatType(transaction.type) }}
                  </span>
                </td>
                <td class="py-4 px-6">
                  <span class="font-bold" :class="transaction.amount > 0 ? 'text-green-600' : 'text-red-600'">
                    {{ transaction.amount > 0 ? '+' : '' }}{{ transaction.amount }}
                    <i class="fas fa-coins text-yellow-500 text-sm ml-1"></i>
                  </span>
                </td>
                <td class="py-4 px-6">
                  <span class="text-gray-800">{{ transaction.balance_after }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden p-4 space-y-4">
          <div
            v-for="transaction in filteredTransactions"
            :key="transaction.id"
            class="bg-white border border-gray-200 rounded-xl p-4"
          >
            <div class="flex justify-between items-start mb-3">
              <div>
                <h4 class="font-bold text-gray-800">{{ transaction.id }}</h4>
                <p class="text-gray-600 text-sm">{{ formatDate(transaction.created_at) }}</p>
              </div>
              <span class="px-3 py-1 rounded-full text-xs font-medium" :class="getTypeClass(transaction.type)">
                {{ formatType(transaction.type) }}
              </span>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-gray-600 text-sm">Amount</p>
                <p class="font-bold" :class="transaction.amount > 0 ? 'text-green-600' : 'text-red-600'">
                  {{ transaction.amount > 0 ? '+' : '' }}{{ transaction.amount }}
                  <i class="fas fa-coins text-yellow-500 text-sm"></i>
                </p>
              </div>
              <div>
                <p class="text-gray-600 text-sm">Balance</p>
                <p class="font-bold text-gray-800">{{ transaction.balance_after }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- No Results -->
        <div v-if="filteredTransactions.length === 0" class="p-12 text-center">
          <i class="fas fa-receipt text-gray-300 text-6xl mb-4"></i>
          <h3 class="text-xl font-bold text-gray-800 mb-2">No transactions found</h3>
          <p class="text-gray-600">Try adjusting your filters</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, reactive, watch } from 'vue';
import axios from 'axios';

export default {
  name: 'PaymentHistory',
  setup() {
    const transactions = ref([]);
    const currentFilter = ref('all');
    const searchQuery = ref('');
    
    const filters = [
      { value: 'all', label: 'All' },
      { value: 'purchase', label: 'Purchases' },
      { value: 'referral_bonus', label: 'Referrals' },
      { value: 'booking', label: 'Bookings' },
    ];

    const statsData = reactive({
      totalSpent: 0,
      totalCoins: 0,
      successCount: 0,
      failedCount: 0
    });

    const stats = computed(() => statsData);

    const filteredTransactions = computed(() => {
      // API now handles filtering, so just return transactions
      return transactions.value;
    });

    const fetchTransactions = async () => {
      try {
        const params = {
          type: currentFilter.value,
          search: searchQuery.value,
        };
        const { data } = await axios.get('/api/wallet/payment-history', { params });
        transactions.value = data.transactions.data || [];
        
        // Update stats from API
        if (data.stats) {
          statsData.totalSpent = data.stats.total_spent || 0;
          statsData.totalCoins = data.stats.total_earned || 0;
          statsData.successCount = data.stats.total_purchases || 0;
          statsData.failedCount = data.stats.failed_payments || 0;
        }
      } catch (error) {
        console.error('Failed to fetch transactions:', error);
      }
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

    const formatType = (type) => {
      const types = {
        purchase: 'Purchase',
        referral_bonus: 'Referral Bonus',
        referral_reward: 'Referral Reward',
        booking: 'Booking',
        refund: 'Refund',
        admin_credit: 'Admin Credit',
        admin_debit: 'Admin Debit'
      };
      return types[type] || type;
    };

    const getTypeClass = (type) => {
      const classes = {
        purchase: 'bg-green-100 text-green-700',
        referral_bonus: 'bg-blue-100 text-blue-700',
        referral_reward: 'bg-purple-100 text-purple-700',
        booking: 'bg-red-100 text-red-700',
        refund: 'bg-yellow-100 text-yellow-700',
        admin_credit: 'bg-green-100 text-green-700',
        admin_debit: 'bg-red-100 text-red-700'
      };
      return classes[type] || 'bg-gray-100 text-gray-700';
    };

    // Watch for filter changes and refetch
    watch(currentFilter, () => {
      fetchTransactions();
    });

    // Watch for search changes with debounce
    watch(searchQuery, () => {
      fetchTransactions();
    });

    onMounted(() => {
      fetchTransactions();
    });

    return {
      transactions,
      currentFilter,
      searchQuery,
      filters,
      stats,
      filteredTransactions,
      formatDate,
      formatType,
      getTypeClass
    };
  }
};
</script>
