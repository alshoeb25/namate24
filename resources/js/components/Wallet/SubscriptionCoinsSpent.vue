<template>
  <div class="bg-white rounded-2xl shadow-md overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4 text-white">
      <h2 class="text-2xl font-bold flex items-center gap-2">
        <i class="fas fa-coins"></i>Subscription Coins Spent
      </h2>
      <p class="text-indigo-100 text-sm mt-1">Track how you've used coins via subscriptions</p>
    </div>

    <!-- Balance Display -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 px-6 py-4 bg-gradient-to-b from-indigo-50 to-white border-b border-gray-200">
      <div class="text-center">
        <p class="text-sm text-gray-600 font-medium">Available Coins</p>
        <p class="text-2xl font-bold text-indigo-600 flex items-center justify-center gap-1 mt-1">
          <i class="fas fa-coins text-yellow-500"></i>{{ stats.wallet_balance || 0 }}
        </p>
      </div>
      <div class="text-center">
        <p class="text-sm text-gray-600 font-medium">Total Spent</p>
        <p class="text-2xl font-bold text-red-600 flex items-center justify-center gap-1 mt-1">
          <i class="fas fa-minus-circle"></i>{{ stats.total_coins_spent || 0 }}
        </p>
      </div>
      <div class="text-center">
        <p class="text-sm text-gray-600 font-medium">On Requirements</p>
        <p class="text-lg font-bold text-orange-600 flex items-center justify-center gap-1 mt-1">
          <i class="fas fa-eye"></i>{{ stats.coins_spent_on_requirement_views || 0 }}
        </p>
      </div>
      <div class="text-center">
        <p class="text-sm text-gray-600 font-medium">Lapsed PRO Spend</p>
        <p class="text-lg font-bold text-purple-600 flex items-center justify-center gap-1 mt-1">
          <i class="fas fa-hourglass-end"></i>{{ stats.coins_spent_by_lapsed || 0 }}
        </p>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
      <div class="flex flex-col sm:flex-row gap-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search transactions..."
          class="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
        <button
          @click="fetchTransactions"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition"
        >
          <i class="fas fa-search mr-2"></i>Search
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="px-6 py-12 text-center">
      <div class="inline-block">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        <p class="text-gray-600 mt-4">Loading transactions...</p>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="transactions.length === 0" class="px-6 py-12 text-center">
      <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
      <p class="text-gray-600 text-lg">No subscription coin transactions yet</p>
      <p class="text-gray-500 text-sm mt-2">Your subscription usage will appear here</p>
    </div>

    <!-- Transactions List -->
    <div v-else class="divide-y divide-gray-200">
      <div v-for="tx in transactions" :key="tx.id" class="px-6 py-4 hover:bg-gray-50 transition">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <!-- Left: Icon and Details -->
          <div class="flex items-start gap-4">
            <!-- Icon -->
            <div
              class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 mt-1"
              :class="getIconClass(tx.type)"
            >
              <i :class="getIcon(tx.type)" class="text-lg"></i>
            </div>

            <!-- Details -->
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <p class="font-semibold text-gray-800">{{ tx.type_label }}</p>
                <span
                  v-if="tx.is_lapsed"
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700"
                >
                  <i class="fas fa-hourglass-end"></i>Lapsed PRO
                </span>
              </div>
              <p class="text-sm text-gray-600">{{ tx.description }}</p>
              <div class="flex items-center gap-2 mt-2 text-xs text-gray-500">
                <i class="fas fa-calendar-alt"></i>{{ tx.created_date }} {{ tx.created_time }}
              </div>
            </div>
          </div>

          <!-- Right: Amount and Balance -->
          <div class="sm:text-right">
            <p class="text-lg font-bold text-red-600 flex items-center justify-between sm:justify-end gap-2">
              <i class="fas fa-minus"></i>{{ tx.amount_spent }}
              <i class="fas fa-coins text-yellow-500"></i>
            </p>
            <p class="text-xs text-gray-500 mt-1">Balance: {{ tx.balance_after }} coins</p>
            <p class="text-xs text-gray-600 mt-1 font-medium">
              {{ tx.subscription_status }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination && pagination.total > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} transactions
        </div>
        <div class="flex gap-2">
          <button
            v-if="pagination.current_page > 1"
            @click="currentPage--; fetchTransactions()"
            class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition"
          >
            <i class="fas fa-chevron-left"></i>
          </button>
          <button
            v-for="page in pageNumbers"
            :key="page"
            @click="currentPage = page; fetchTransactions()"
            :class="[
              'px-3 py-2 rounded-lg font-medium transition',
              currentPage === page
                ? 'bg-indigo-600 text-white'
                : 'border border-gray-300 text-gray-700 hover:bg-gray-100'
            ]"
          >
            {{ page }}
          </button>
          <button
            v-if="pagination.has_more_pages"
            @click="currentPage++; fetchTransactions()"
            class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition"
          >
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

export default {
  name: 'SubscriptionCoinsSpent',
  setup() {
    const transactions = ref([]);
    const loading = ref(false);
    const searchQuery = ref('');
    const currentPage = ref(1);
    const perPage = 15;
    const pagination = ref(null);
    const stats = ref({
      wallet_balance: 0,
      total_coins_spent: 0,
      coins_spent_on_requirement_views: 0,
      coins_spent_by_lapsed: 0,
    });

    const fetchTransactions = async () => {
      try {
        loading.value = true;
        const { data } = await axios.get('/api/wallet/subscription-coins-spent', {
          params: {
            page: currentPage.value,
            per_page: perPage,
            search: searchQuery.value || undefined,
          },
        });

        transactions.value = data.transactions.data;
        pagination.value = data.transactions.pagination;
        stats.value = {
          wallet_balance: data.wallet.balance,
          ...data.stats,
        };
      } catch (error) {
        console.error('Failed to fetch subscription coins spent:', error);
      } finally {
        loading.value = false;
      }
    };

    const pageNumbers = computed(() => {
      if (!pagination.value) return [];
      const pages = [];
      const maxPages = 5;
      let start = Math.max(1, pagination.value.current_page - Math.floor(maxPages / 2));
      let end = Math.min(pagination.value.last_page, start + maxPages - 1);
      if (end - start < maxPages - 1) {
        start = Math.max(1, end - maxPages + 1);
      }
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      return pages;
    });

    const getIcon = (type) => {
      const icons = {
        requirement_view: 'fas fa-eye text-orange-600',
        tutor_requirement_view: 'fas fa-eye text-orange-600',
        tutor_requirement_view_lapsed: 'fas fa-eye text-purple-600',
        student_contact_unlock: 'fas fa-phone text-blue-600',
        student_contact_unlock_lapsed: 'fas fa-phone text-purple-600',
        tutor_unlock_contact: 'fas fa-phone text-blue-600',
        tutor_approach: 'fas fa-handshake text-green-600',
        enquiry_unlock: 'fas fa-lock-open text-green-600',
        tutor_enquiry_unlock: 'fas fa-lock-open text-green-600',
        profile_unlock: 'fas fa-id-card text-cyan-600',
      };
      return icons[type] || 'fas fa-coins text-gray-600';
    };

    const getIconClass = (type) => {
      const classes = {
        requirement_view: 'bg-orange-100',
        tutor_requirement_view: 'bg-orange-100',
        tutor_requirement_view_lapsed: 'bg-purple-100',
        student_contact_unlock: 'bg-blue-100',
        student_contact_unlock_lapsed: 'bg-purple-100',
        tutor_unlock_contact: 'bg-blue-100',
        tutor_approach: 'bg-green-100',
        enquiry_unlock: 'bg-green-100',
        tutor_enquiry_unlock: 'bg-green-100',
        profile_unlock: 'bg-cyan-100',
      };
      return classes[type] || 'bg-gray-100';
    };

    onMounted(() => {
      fetchTransactions();
    });

    return {
      transactions,
      loading,
      searchQuery,
      currentPage,
      pagination,
      stats,
      pageNumbers,
      fetchTransactions,
      getIcon,
      getIconClass,
    };
  },
};
</script>

<style scoped>
/* Animations */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .md\:grid-cols-4 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>
