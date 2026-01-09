<template>
  <transition name="fade">
    <div
      v-if="visible"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
      aria-modal="true"
      role="dialog"
    >
      <div class="relative w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-400">Transaction</p>
            <h3 class="text-2xl font-bold text-gray-900">{{ title }}</h3>
          </div>
          <button @click="$emit('close')" class="rounded-full p-2 text-gray-500 hover:bg-gray-100" aria-label="Close modal">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>

        <div class="max-h-[75vh] space-y-6 overflow-y-auto px-6 py-6">
          <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
            <div>
              <p class="text-xs uppercase tracking-wide text-gray-500">Transaction ID</p>
              <p class="font-mono font-semibold text-gray-900">{{ encryptedTransactionId }}</p>
            </div>
            <span :class="statusBadgeClass">{{ statusLabel }}</span>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="rounded-xl border border-gray-100 px-4 py-3">
              <p class="text-xs uppercase tracking-wide text-gray-500">Amount</p>
              <p class="text-2xl font-bold text-gray-900">{{ formatAmount(displayAmount) }}</p>
              <p v-if="details.paymentId" class="text-xs text-gray-500 mt-1">Payment ID: {{ details.paymentId }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 px-4 py-3">
              <p class="text-xs uppercase tracking-wide text-gray-500">Coins Added</p>
              <p class="text-2xl font-bold text-gray-900">{{ coinBreakdown.total }}</p>
              <p class="text-xs text-gray-500 mt-1">Base: {{ coinBreakdown.base }} · Bonus: {{ coinBreakdown.bonus }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="rounded-xl bg-gray-50 px-4 py-3">
              <p class="text-xs uppercase tracking-wide text-gray-500">Date</p>
              <p class="font-semibold text-gray-900">{{ formattedDate }}</p>
            </div>
            <div class="rounded-xl bg-gray-50 px-4 py-3">
              <p class="text-xs uppercase tracking-wide text-gray-500">Time</p>
              <p class="font-semibold text-gray-900">{{ formattedTime }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="rounded-xl border border-gray-100 px-4 py-3">
              <p class="text-xs uppercase tracking-wide text-gray-500">Plan</p>
              <p class="font-semibold text-gray-900">{{ details.plan || 'Wallet Top-up' }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 px-4 py-3">
              <p class="text-xs uppercase tracking-wide text-gray-500">Payment Method</p>
              <p class="font-semibold text-gray-900">{{ details.paymentMethod || 'Razorpay' }}</p>
            </div>
          </div>

          <div class="rounded-xl bg-gray-50 px-4 py-4">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-3">Payment Details</p>
            <div class="space-y-2 text-sm">
              <div class="flex items-center justify-between">
                <span class="text-gray-600">Base Price</span>
                <span class="font-semibold text-gray-900">{{ formatAmount(details.basePrice) }}</span>
              </div>
              <div v-if="details.discount" class="flex items-center justify-between">
                <span class="text-gray-600">Discount</span>
                <span class="font-semibold text-green-600">-{{ formatAmount(details.discount) }}</span>
              </div>
              <div v-if="gstAmount > 0" class="flex items-center justify-between">
                <span class="text-gray-600">GST ({{ gstRate }}%)</span>
                <span class="font-semibold text-gray-900">{{ formatAmount(gstAmount) }}</span>
              </div>
              <div v-if="details.tax && !gstAmount" class="flex items-center justify-between">
                <span class="text-gray-600">Tax</span>
                <span class="font-semibold text-gray-900">{{ formatAmount(details.tax) }}</span>
              </div>
              <div class="flex items-center justify-between border-t border-gray-200 pt-2 text-base font-bold text-gray-900">
                <span>Total</span>
                <span>{{ formatAmount(details.totalAmount) }}</span>
              </div>
            </div>
          </div>

          <div v-if="status === 'pending'" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
            <p class="font-semibold">Payment is pending</p>
            <p class="text-sm">It may take a few minutes for Razorpay to confirm this payment. Please wait for 10-20 minutes; the status will update automatically.</p>
          </div>

          <div v-if="status === 'failed'" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            <p class="font-semibold">Payment failed</p>
            <p class="text-sm">{{ details.errorMessage || 'Your payment was not completed. You can retry with a new transaction.' }}</p>
          </div>
        </div>

        <div class="flex flex-wrap gap-3 border-t border-gray-100 px-6 py-4">
          <button
            v-if="status === 'success'"
            @click="viewInvoice"
            class="flex-1 rounded-xl bg-pink-500 px-4 py-3 font-semibold text-white transition hover:bg-pink-600"
          >
            <i class="fas fa-file-invoice mr-2"></i>View Invoice
          </button>
          <button
            v-if="status === 'failed'"
            @click="retryPayment"
            class="flex-1 rounded-xl bg-pink-500 px-4 py-3 font-semibold text-white transition hover:bg-pink-600"
          >
            <i class="fas fa-redo mr-2"></i>Retry Payment
          </button>
          <button
            @click="goToPaymentTransactions"
            class="flex-1 rounded-xl border border-pink-500 px-4 py-3 font-semibold text-pink-600 transition hover:bg-pink-50"
          >
            <i class="fas fa-list mr-2"></i>View All Transactions
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import axios from 'axios';

export default {
  name: 'TransactionDetailsModal',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    status: {
      type: String,
      default: 'success' // success | failed | pending
    },
    details: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ['close', 'download', 'support', 'retry'],
  computed: {
    statusLabel() {
      if (this.status === 'failed') return 'Failed';
      if (this.status === 'pending') return 'Pending';
      return 'Successful';
    },
    statusBadgeClass() {
      const base = 'rounded-full px-3 py-1 text-xs font-semibold';
      if (this.status === 'failed') return `${base} bg-red-100 text-red-700`;
      if (this.status === 'pending') return `${base} bg-amber-100 text-amber-700`;
      return `${base} bg-green-100 text-green-700`;
    },
    formattedDate() {
      const date = this.details.date ? new Date(this.details.date) : new Date();
      return date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    },
    formattedTime() {
      const date = this.details.date ? new Date(this.details.date) : new Date();
      return date.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
    },
    coinBreakdown() {
      const base = Number(this.details.baseCoins || 0);
      const bonus = Number(this.details.bonusCoins || 0);
      return {
        base,
        bonus,
        total: base + bonus
      };
    },
    displayAmount() {
      const amount = this.details.totalAmount ?? this.details.amount;
      if (amount && amount > 1000) return amount / 100;
      return amount;
    },
    currency() {
      // Get currency from transaction details or default to INR
      return this.details.currency || 'INR';
    },
    currencySymbol() {
      return this.currency === 'USD' ? '$' : '₹';
    },
    title() {
      if (this.status === 'failed') return 'Payment Failed';
      if (this.status === 'pending') return 'Payment Pending';
      return 'Payment Successful';
    },
    encryptedTransactionId() {
      const id = String(this.details.transactionId || '');
      if (!id || id === '—') return '—';
      // Show first 4 and last 4 characters, mask middle
      if (id.length <= 8) return id;
      const start = id.substring(0, 4);
      const end = id.substring(id.length - 4);
      const middle = '*'.repeat(Math.min(id.length - 8, 8));
      return `${start}${middle}${end}`;
    },
    gstAmount() {
      // Try to get GST from details.gstAmount or calculate from tax
      if (this.details.gstAmount !== undefined) {
        return Number(this.details.gstAmount);
      }
      if (this.details.tax !== undefined) {
        return Number(this.details.tax);
      }
      // Calculate from basePrice and totalAmount if available
      const base = Number(this.details.basePrice || 0);
      const total = Number(this.details.totalAmount || 0);
      const discount = Number(this.details.discount || 0);
      if (base > 0 && total > 0) {
        return total - base + discount;
      }
      return 0;
    },
    gstRate() {
      // Try to get GST rate from details or calculate
      if (this.details.gstRate !== undefined) {
        return Number(this.details.gstRate * 100).toFixed(0);
      }
      const base = Number(this.details.basePrice || 0);
      if (base > 0 && this.gstAmount > 0) {
        const rate = (this.gstAmount / base) * 100;
        return rate.toFixed(0);
      }
      return '18'; // default GST rate
    }
  },
  methods: {
    formatAmount(value) {
      const num = Number(value ?? 0);
      const currency = this.currency;
      const locale = currency === 'USD' ? 'en-US' : 'en-IN';
      return new Intl.NumberFormat(locale, { 
        style: 'currency', 
        currency: currency 
      }).format(num);
    },
    viewInvoice() {
      const orderId = this.details?.orderId;
      const invoiceId = this.details?.invoiceId;
      
      if (invoiceId) {
        window.location.href = `/api/wallet/invoice/${invoiceId}/download`;
      } else if (orderId) {
        window.location.href = `/api/orders/${orderId}/receipt`;
      } else {
        this.$emit('download');
      }
    },
    retryPayment() {
      const orderId = this.details?.orderId;
      
      if (!orderId) {
        this.$emit('retry');
        return;
      }

      // Call retry API to create new order/transaction
      axios.post(`/api/wallet/order/${orderId}/retry`)
        .then(response => {
          if (response.data.success) {
            // New order created, emit retry with new order data
            this.$emit('retry', response.data);
            // Navigate to payment transactions
            this.goToPaymentTransactions();
          } else {
            alert(response.data.message || 'Failed to retry payment');
          }
        })
        .catch(error => {
          console.error('Retry failed:', error);
          alert('Failed to retry payment. Please try again.');
        });
    },
    goToPaymentTransactions() {
      // Close modal first
      this.$emit('close');
      
      // Determine user role from store or current route
      const currentPath = window.location.pathname;
      let targetPath = '/student/wallet/payment-history';
      
      if (currentPath.includes('/tutor/')) {
        targetPath = '/tutor/wallet/payment-history';
      }
      
      // Navigate using router if available, otherwise use window.location
      if (this.$router) {
        this.$router.push(targetPath);
      } else {
        window.location.href = targetPath;
      }
    },
    downloadReceipt() {
      const orderId = this.details?.orderId;
      if (!orderId) {
        window.print();
        return;
      }

      // Direct download without authentication check - backend will handle auth
      window.location.href = `/api/orders/${orderId}/receipt`;
    }
  }
};
</script>
