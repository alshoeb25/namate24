<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Coin Transactions</h1>
        <p class="text-gray-600">View credits and debits in your wallet</p>
      </div>

      <div class="bg-white rounded-xl p-6 border border-gray-200 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div class="flex flex-wrap items-center gap-2">
            <h3 class="text-sm font-semibold text-gray-700">Category</h3>
            <button
              v-for="c in categoryFilters"
              :key="c.value"
              @click="currentCategory = c.value"
              class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
              :class="currentCategory === c.value ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            >
              {{ c.label }}
            </button>
          </div>
          <div class="relative w-full md:w-auto">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-search text-gray-400"></i>
            </div>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by description..."
              class="pl-10 w-full md:w-72 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
            />
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-xl font-bold text-gray-800">Recent Coin Transactions</h2>
          <p class="text-gray-600 text-sm">Showing {{ filteredTransactions.length }} transactions</p>
        </div>

        <div class="overflow-x-auto hidden md:block">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Transaction ID</th>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Date & Time</th>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Type</th>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Amount</th>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Balance</th>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="tx in filteredTransactions" :key="tx.id" class="border-b border-gray-100 hover:bg-gray-50">
                <td class="py-4 px-6"><span class="font-mono font-medium text-gray-800">{{ encryptTransactionId(tx.id) }}</span></td>
                <td class="py-4 px-6">{{ formatDate(tx.created_at) }}</td>
                <td class="py-4 px-6"><span class="px-3 py-1 rounded-full text-xs font-medium" :class="getTypeClass(tx.type)">{{ formatType(tx.type) }}</span></td>
                <td class="py-4 px-6">
                  <span class="font-bold" :class="(tx.amount_coins || tx.amount) > 0 ? 'text-green-600' : 'text-red-600'">
                    {{ (tx.amount_coins || tx.amount) > 0 ? '+' : '' }}{{ tx.amount_coins || tx.amount }}
                    <i class="fas fa-coins text-yellow-500 text-sm ml-1"></i>
                  </span>
                </td>
                <td class="py-4 px-6"><span class="text-gray-800">{{ tx.balance_after ?? '—' }}</span></td>
                <td class="py-4 px-6"><span class="text-gray-400 text-sm">—</span></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="md:hidden p-4 space-y-4">
          <div v-for="tx in filteredTransactions" :key="tx.id" class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="flex justify-between items-start mb-3">
              <div>
                <h4 class="font-mono font-bold text-gray-800">{{ encryptTransactionId(tx.id) }}</h4>
                <p class="text-gray-600 text-sm">{{ formatDate(tx.created_at) }}</p>
              </div>
              <span class="px-3 py-1 rounded-full text-xs font-medium" :class="getTypeClass(tx.type)">{{ formatType(tx.type) }}</span>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-gray-600 text-sm">Amount</p>
                <p class="font-bold" :class="(tx.amount_coins || tx.amount) > 0 ? 'text-green-600' : 'text-red-600'">
                  {{ (tx.amount_coins || tx.amount) > 0 ? '+' : '' }}{{ tx.amount_coins || tx.amount }}
                  <i class="fas fa-coins text-yellow-500 text-sm"></i>
                </p>
              </div>
              <div>
                <p class="text-gray-600 text-sm">Balance</p>
                <p class="font-bold text-gray-800">{{ tx.balance_after ?? '—' }}</p>
              </div>
            </div>
          </div>
        </div>

        <div v-if="paginationInfo.total > 0" class="mt-8 flex items-center justify-between p-6">
          <div class="text-gray-600 text-sm">
            Showing <span class="font-semibold">{{ paginationInfo.from }}</span> to 
            <span class="font-semibold">{{ paginationInfo.to }}</span> of 
            <span class="font-semibold">{{ paginationInfo.total }}</span> transactions
          </div>
          <div class="flex gap-2">
            <button @click="previousPage" :disabled="!paginationInfo.prev_page_url" class="px-4 py-2 rounded-lg font-medium transition" :class="paginationInfo.prev_page_url ? 'bg-pink-500 hover:bg-pink-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'">
              <i class="fas fa-chevron-left mr-2"></i>Previous
            </button>
            <button @click="nextPage" :disabled="!paginationInfo.next_page_url" class="px-4 py-2 rounded-lg font-medium transition" :class="paginationInfo.next_page_url ? 'bg-pink-500 hover:bg-pink-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'">
              Next<i class="fas fa-chevron-right ml-2"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, reactive, watch } from 'vue';
import axios from 'axios';

export default {
  name: 'CoinTransactions',
  setup() {
    const transactions = ref([]);
    const searchQuery = ref('');
    const currentCategory = ref('all');
    const currentPage = ref(1);
    const perPage = ref(20);
    const paginationInfo = reactive({ current_page: 1, per_page: 20, total: 0, last_page: 1, from: 0, to: 0, has_more_pages: false, next_page_url: null, prev_page_url: null });

    const categoryFilters = [
      { value: 'all', label: 'All' },
      { value: 'purchase', label: 'Purchase' },
      { value: 'post_requirement', label: 'Post Requirement' },
      { value: 'enquiry', label: 'Enquiry' },
      { value: 'referrals', label: 'Referrals' }
    ];

    const filteredTransactions = computed(() => {
      // Server-side filtering now, so just return the transactions as-is
      return transactions.value;
    });

    const fetchTransactions = async (page = 1) => {
      try {
        const params = {
          type: currentCategory.value,
          search: searchQuery.value || undefined,
          page: page,
          per_page: perPage.value
        };
        const { data } = await axios.get('/api/wallet/coin-transactions', { params });
        const list = data.transactions.data || [];
        transactions.value = list;
        if (data.transactions.pagination) {
          Object.assign(paginationInfo, data.transactions.pagination);
          currentPage.value = data.transactions.pagination.current_page;
        }
      } catch (err) {
        console.error('Failed to fetch coin transactions:', err);
      }
    };

    const nextPage = () => { if (paginationInfo.has_more_pages) fetchTransactions(currentPage.value + 1); };
    const previousPage = () => { if (currentPage.value > 1) fetchTransactions(currentPage.value - 1); };

    const formatDate = (date) => new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    const formatType = (type) => ({ purchase: 'Purchase', enquiry_post: 'Posted Requirement', enquiry_unlock: 'Unlocked Contact', referral_bonus: 'Referral Bonus', referral_reward: 'Referral Reward', booking: 'Booking', refund: 'Refund', admin_credit: 'Admin Credit', admin_debit: 'Admin Debit' }[type] || type);
    const getTypeClass = (type) => ({ purchase: 'bg-green-100 text-green-700', enquiry_post: 'bg-purple-100 text-purple-700', enquiry_unlock: 'bg-indigo-100 text-indigo-700', referral_bonus: 'bg-blue-100 text-blue-700', referral_reward: 'bg-purple-100 text-purple-700', booking: 'bg-red-100 text-red-700', refund: 'bg-yellow-100 text-yellow-700', admin_credit: 'bg-green-100 text-green-700', admin_debit: 'bg-red-100 text-red-700' }[type] || 'bg-gray-100 text-gray-700');

    const encryptTransactionId = (id) => { const s = String(id || ''); if (!s || s === '—') return '—'; if (s.length <= 8) return s; const start = s.substring(0, 4); const end = s.substring(s.length - 4); const middle = '*'.repeat(Math.min(s.length - 8, 8)); return `${start}${middle}${end}`; };

    watch(searchQuery, () => { currentPage.value = 1; fetchTransactions(1); });
    watch(currentCategory, () => { currentPage.value = 1; fetchTransactions(1); });
    onMounted(() => { fetchTransactions(); });

    return { transactions, filteredTransactions, searchQuery, currentCategory, categoryFilters, paginationInfo, nextPage, previousPage, formatDate, formatType, getTypeClass, encryptTransactionId };
  }
};
</script>

<style scoped>
</style>
