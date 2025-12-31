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
                  <span class="font-medium text-gray-800">{{ transaction.encrypted_id }}</span>
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
                <h4 class="font-bold text-gray-800">{{ transaction.encrypted_id }}</h4>
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

      <!-- Pagination -->
      <div v-if="paginationInfo.total > 0" class="mt-8 flex items-center justify-between">
        <div class="text-gray-600 text-sm">
          Showing <span class="font-semibold">{{ paginationInfo.from }}</span> to 
          <span class="font-semibold">{{ paginationInfo.to }}</span> of 
          <span class="font-semibold">{{ paginationInfo.total }}</span> transactions
        </div>
        
        <div class="flex gap-2">
          <button
            @click="previousPage"
            :disabled="!paginationInfo.prev_page_url"
            class="px-4 py-2 rounded-lg font-medium transition"
            :class="paginationInfo.prev_page_url ? 'bg-pink-500 hover:bg-pink-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
          >
            <i class="fas fa-chevron-left mr-2"></i>Previous
          </button>
          
          <div class="flex items-center gap-2">
            <span class="text-gray-600">Page</span>
            <div class="flex gap-1">
              <button
                v-for="page in pageNumbers"
                :key="page"
                @click="goToPage(page)"
                class="w-10 h-10 rounded-lg font-medium transition"
                :class="page === paginationInfo.current_page ? 'bg-pink-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
              >
                {{ page }}
              </button>
            </div>
            <span class="text-gray-600">of {{ paginationInfo.last_page }}</span>
          </div>
          
          <button
            @click="nextPage"
            :disabled="!paginationInfo.next_page_url"
            class="px-4 py-2 rounded-lg font-medium transition"
            :class="paginationInfo.next_page_url ? 'bg-pink-500 hover:bg-pink-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
          >
            Next<i class="fas fa-chevron-right ml-2"></i>
          </button>
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
    const currentPage = ref(1);
    const perPage = ref(20);
    const paginationInfo = reactive({
      current_page: 1,
      per_page: 20,
      total: 0,
      last_page: 1,
      from: 0,
      to: 0,
      has_more_pages: false,
      next_page_url: null,
      prev_page_url: null
    });
    
    const filters = [
      { value: 'all', label: 'All' },
      { value: 'purchase', label: 'Purchases' },
      { value: 'enquiry_post', label: 'Posted Requirements' },
      { value: 'enquiry_unlock', label: 'Unlocked Contacts' },
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

    const fetchTransactions = async (page = 1) => {
      try {
        const params = {
          type: currentFilter.value,
          search: searchQuery.value,
          page: page,
          per_page: perPage.value
        };
        const { data } = await axios.get('/api/wallet/payment-history', { params });
        transactions.value = data.transactions.data || [];
        
        // Update pagination info
        if (data.transactions.pagination) {
          Object.assign(paginationInfo, data.transactions.pagination);
          currentPage.value = data.transactions.pagination.current_page;
        }
        
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

    const nextPage = () => {
      if (paginationInfo.has_more_pages) {
        fetchTransactions(currentPage.value + 1);
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    };

    const previousPage = () => {
      if (currentPage.value > 1) {
        fetchTransactions(currentPage.value - 1);
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    };

    const goToPage = (page) => {
      if (page >= 1 && page <= paginationInfo.last_page) {
        fetchTransactions(page);
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    };

    const pageNumbers = computed(() => {
      const pages = [];
      const start = Math.max(1, currentPage.value - 2);
      const end = Math.min(paginationInfo.last_page, currentPage.value + 2);
      
      if (start > 1) pages.push(1);
      if (start > 2) pages.push('...');
      
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      
      if (end < paginationInfo.last_page - 1) pages.push('...');
      if (end < paginationInfo.last_page) pages.push(paginationInfo.last_page);
      
      return pages.filter(p => typeof p === 'number');
    });

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
        enquiry_post: 'Posted Requirement',
        enquiry_unlock: 'Unlocked Contact',
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
        enquiry_post: 'bg-purple-100 text-purple-700',
        enquiry_unlock: 'bg-indigo-100 text-indigo-700',
        referral_bonus: 'bg-blue-100 text-blue-700',
        referral_reward: 'bg-purple-100 text-purple-700',
        booking: 'bg-red-100 text-red-700',
        refund: 'bg-yellow-100 text-yellow-700',
        admin_credit: 'bg-green-100 text-green-700',
        admin_debit: 'bg-red-100 text-red-700'
      };
      return classes[type] || 'bg-gray-100 text-gray-700';
    };

    // Watch for filter changes and refetch from page 1
    watch(currentFilter, () => {
      currentPage.value = 1;
      fetchTransactions(1);
    });

    // Watch for search changes with debounce
    watch(searchQuery, () => {
      currentPage.value = 1;
      fetchTransactions(1);
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
      getTypeClass,
      currentPage,
      perPage,
      paginationInfo,
      nextPage,
      previousPage,
      goToPage,
      pageNumbers
    };
  }
};
</script>
