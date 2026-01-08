<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
      <!-- Header -->
      <div class="">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Payment History</h1>
        <p class="text-gray-600">View all your past transactions and payment records</p>
      </div>

      <div class="bg-white rounded-xl p-6 border border-gray-200">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="rounded-lg border border-gray-200 p-4 bg-gray-50 flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm">Successful</p>
              <p class="text-2xl font-bold text-gray-800">{{ stats.successCount }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
              <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
          </div>
          <div class="rounded-lg border border-gray-200 p-4 bg-gray-50 flex items-center justify-between">
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
      <div class="bg-white rounded-xl p-6 border border-gray-200">
        <div class="flex flex-col gap-4 mb-6">
          <div>
            <h2 class="text-lg font-bold text-gray-800 mb-2">Filter Transactions</h2>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
              <div class="flex flex-wrap items-center gap-2">
                <h3 class="text-sm font-semibold text-gray-700">Status</h3>
                <button
                  v-for="s in statusFilters"
                  :key="s.value"
                  @click="currentStatus = s.value"
                  class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
                  :class="currentStatus === s.value ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                >
                  {{ s.label }}
                </button>
              </div>
              <div class="w-full md:w-auto">
                <label class="sr-only" for="tx-search">Search</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                  </div>
                  <input
                    id="tx-search"
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search by ID, amount, or notes"
                    class="pl-10 w-full md:w-80 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                  />
                </div>
              </div>
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
                <th class="py-4 px-6 text-left text-gray-700 font-medium hidden">Transaction ID</th>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Date & Time</th>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Type</th>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Amount</th>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Coins</th>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Status</th>
                <th class="py-4 px-6 text-left text-gray-700 font-medium">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="transaction in filteredTransactions"
                :key="transaction.id"
                class="border-b border-gray-100 hover:bg-gray-50"
              >
                <td class="py-4 px-6 hidden">
                  <span class="font-mono font-medium text-gray-800">{{ encryptTransactionId(transaction.id) }}</span>
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
                  <span class="text-gray-800">{{ transaction.coins }}</span>
                </td>
                <td class="py-4 px-6">
                  <span class="px-3 py-1 rounded-full text-xs font-medium" :class="getStatusClass(transaction.status)">
                    {{ formatStatus(transaction.status) }}
                  </span>
                </td>
                <td class="py-4 px-6">
                  <button
                    v-if="shouldShowInvoice(transaction)"
                    @click="viewTransactionDetails(transaction)"
                    class="text-pink-600 hover:text-pink-700 font-medium text-sm flex items-center gap-1"
                  >
                    <i class="fas fa-file-invoice"></i>
                    <span>View Invoice</span>
                  </button>
                  <button
                    v-else-if="shouldShowDetails(transaction)"
                    @click="viewTransactionDetails(transaction)"
                    class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center gap-1"
                  >
                    <i class="fas fa-eye"></i>
                    <span>View Details</span>
                  </button>
                  <span v-else class="text-gray-400 text-sm">—</span>
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
                <p class="text-gray-600 text-sm">Coins</p>
                <p class="font-bold text-gray-800">{{ transaction.coins }}</p>
              </div>
            </div>
            <div v-if="shouldShowInvoice(transaction) || shouldShowDetails(transaction)" class="mt-3 pt-3 border-t border-gray-200">
              <button
                @click="viewTransactionDetails(transaction)"
                class="w-full py-2 text-pink-600 hover:text-pink-700 font-medium text-sm flex items-center justify-center gap-2"
              >
                <i :class="shouldShowInvoice(transaction) ? 'fas fa-file-invoice' : 'fas fa-eye'"></i>
                <span>{{ shouldShowInvoice(transaction) ? 'View Invoice' : 'View Details' }}</span>
              </button>
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

    <!-- Transaction Details Modal -->
    <TransactionDetailsModal
      :visible="showDetailsModal"
      :status="selectedTransaction?.status === 'SUCCESS' ? 'success' : selectedTransaction?.status === 'PENDING' ? 'pending' : 'failed'"
      :details="transactionDetails"
      @close="closeDetailsModal"
      @download="downloadInvoice"
      @support="contactSupport"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted, reactive, watch } from 'vue';
import axios from 'axios';
import TransactionDetailsModal from '@/components/Wallet/TransactionDetailsModal.vue';

export default {
  name: 'PaymentHistory',
  components: {
    TransactionDetailsModal
  },
  setup() {
    const transactions = ref([]);
    const currentStatus = ref('all');
    const searchQuery = ref('');
    const currentPage = ref(1);
    const perPage = ref(20);
    const showDetailsModal = ref(false);
    const selectedTransaction = ref(null);
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
    
    const statusFilters = [
      { value: 'all', label: 'All' },
      { value: 'success', label: 'Success' },
      { value: 'pending', label: 'Pending' },
      { value: 'failed', label: 'Failed' },
      { value: 'initiated', label: 'Initiated' },
    ];

    

    const statsData = reactive({
      totalSpent: 0,
      totalCoins: 0,
      successCount: 0,
      failedCount: 0
    });

    const stats = computed(() => statsData);

    const transactionDetails = computed(() => {
      if (!selectedTransaction.value) return {};
      const tx = selectedTransaction.value;
      const meta = tx.meta || {};
      const pricing = meta.pricing || {};
      
      return {
        transactionId: tx.id,
        orderId: tx.order_id || tx.razorpay_order_id,
        paymentId: tx.razorpay_payment_id,
        date: tx.created_at,
        plan: meta.package_name || 'Coin Package',
        paymentMethod: 'Razorpay',
        basePrice: tx.amount,
        tax: pricing.tax_amount_inr || null,
        gstAmount: pricing.tax_amount_inr || null,
        gstRate: pricing.gst_rate || null,
        totalAmount: tx.amount,
        amount: tx.amount,
        baseCoins: tx.coins || 0,
        bonusCoins: 0,
        status: tx.status
      };
    });

    const filteredTransactions = computed(() => {
      // API now handles filtering, so just return transactions
      return transactions.value;
    });

    const fetchTransactions = async (page = 1) => {
      try {
        const statusParam = currentStatus.value === 'all' ? undefined : currentStatus.value;
        const params = {
          status: statusParam,
          search: searchQuery.value || undefined,
          page: page,
          per_page: perPage.value
        };
        const { data } = await axios.get('/api/wallet/payment-transactions', { params });
        const pageTx = (data?.transactions?.data) || [];
        transactions.value = pageTx;
        console.log(pageTx);
        // Update pagination info
        if (data?.transactions?.pagination) {
          Object.assign(paginationInfo, data.transactions.pagination);
          currentPage.value = data.transactions.pagination.current_page;
        }
        
        // Update stats from API
        if (data?.stats) {
          const s = data.stats;
          statsData.successCount = s.success_count ?? 0;
          statsData.failedCount = s.failed_count ?? 0;
        } else {
          // Fallback: compute counts from current page
          const isSuccess = (st) => ['success','completed','SUCCESS','COMPLETED'].includes(String(st || '').toUpperCase());
          const isFailed = (st) => ['failed','FAILED'].includes(String(st || '').toUpperCase());
          statsData.successCount = pageTx.filter(t => isSuccess(t.status)).length;
          statsData.failedCount = pageTx.filter(t => isFailed(t.status)).length;
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

    const formatStatus = (status) => {
      const statuses = {
        SUCCESS: 'Success',
        PENDING: 'Pending',
        FAILED: 'Failed',
        INITIATED: 'Initiated'
      };
      return statuses[status] || status;
    };

    const getStatusClass = (status) => {
      const classes = {
        SUCCESS: 'bg-green-100 text-green-700',
        PENDING: 'bg-amber-100 text-amber-700',
        FAILED: 'bg-red-100 text-red-700',
        INITIATED: 'bg-blue-100 text-blue-700'
      };
      return classes[status] || 'bg-gray-100 text-gray-700';
    };

    const viewTransactionDetails = (transaction) => {
      selectedTransaction.value = transaction;
      showDetailsModal.value = true;
    };

    const closeDetailsModal = () => {
      showDetailsModal.value = false;
      selectedTransaction.value = null;
    };

    const downloadInvoice = () => {
      const tx = selectedTransaction.value;
      if (!tx) return;
      if (tx.invoice_id) {
        window.location.href = `/api/wallet/invoice/${tx.invoice_id}/download`;
      } else if (tx.order_id) {
        window.location.href = `/api/orders/${tx.order_id}/receipt`;
      }
    };

    const contactSupport = () => {
      // Navigate to support or open chat
      window.location.href = '/support';
    };

    const shouldShowInvoice = (transaction) => {
      // Show invoice for successful purchases (credit transactions with order or invoice)
      const status = (transaction.status || '').toString().toUpperCase();
      const isSuccess = status === 'SUCCESS' || status === 'COMPLETED';
      const isPurchase = transaction.type === 'CREDIT' ||
                        transaction.type === 'purchase' ||
                        (transaction.description && transaction.description.toLowerCase().includes('purchase'));
      const hasInvoice = !!transaction.invoice_id || !!transaction.invoice_number;
      const hasOrder = !!transaction.order_id;
      return isSuccess && isPurchase && (hasInvoice || hasOrder);
    };

    const shouldShowDetails = (transaction) => {
      // Show details for other successful transactions
      const isSuccess = transaction.status === 'SUCCESS' || transaction.status === 'COMPLETED' || transaction.status === 'success' || transaction.status === 'completed';
      return isSuccess && !shouldShowInvoice(transaction);
    };

    const encryptTransactionId = (id) => {
      const idStr = String(id ?? '').trim();
      if (!idStr) return '—';
      const masked = '********'; // fixed mask length to avoid leaking length
      // For short IDs, always hide almost everything
      if (idStr.length < 10) {
        const first = idStr.charAt(0) || '*';
        const last = idStr.slice(-1) || '*';
        return `${first}${masked}${last}`;
      }
      // For longer IDs, reveal first/last 4 and mask the middle with fixed stars
      const start = idStr.slice(0, 4);
      const end = idStr.slice(-4);
      return `${start}${masked}${end}`;
    };

    // Watch for status changes and refetch
    watch(currentStatus, () => {
      currentPage.value = 1;
      fetchTransactions(1);
    });

    

    // Watch for search changes with debounce
    let searchTimer = null;
    watch(searchQuery, () => {
      currentPage.value = 1;
      if (searchTimer) clearTimeout(searchTimer);
      searchTimer = setTimeout(() => fetchTransactions(1), 350);
    });

    onMounted(() => {
      fetchTransactions();
    });

    return {
      transactions,
      currentStatus,
      
      searchQuery,
      statusFilters,
      
      stats,
      filteredTransactions,
      formatDate,
      formatType,
      getTypeClass,
      formatStatus,
      getStatusClass,
      currentPage,
      perPage,
      paginationInfo,
      nextPage,
      previousPage,
      goToPage,
      pageNumbers,
      showDetailsModal,
      selectedTransaction,
      transactionDetails,
      viewTransactionDetails,
      closeDetailsModal,
      downloadInvoice,
      contactSupport,
      shouldShowInvoice,
      shouldShowDetails,
      encryptTransactionId
    };
  }
};
</script>
