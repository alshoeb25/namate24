<template>
  <div>
    <!-- Coin Packages -->
    <div>
      <h2 class="text-2xl font-bold text-gray-800 mb-4">
        <i class="fas fa-shopping-cart mr-2"></i>Buy Coins
      </h2>
      <div v-if="loading" class="text-center py-12">
        <i class="fas fa-spinner fa-spin text-4xl text-pink-600"></i>
        <p class="text-gray-600 mt-4">Loading packages...</p>
      </div>
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="pkg in packages"
          :key="pkg.id"
          class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all p-6 relative border-2"
          :class="pkg.is_popular ? 'border-pink-500' : 'border-transparent'"
        >
          <!-- Popular Badge -->
          <div v-if="pkg.is_popular" class="absolute -top-3 right-4 bg-pink-500 text-white px-3 py-1 rounded-full text-xs font-bold">
            <i class="fas fa-star mr-1"></i>POPULAR
          </div>
          
          <!-- Package Content -->
          <div class="text-center mb-4">
            <div class="mb-4">
              <div class="w-20 h-20 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center mx-auto shadow-lg">
                <i class="fas fa-coins text-white text-3xl"></i>
              </div>
            </div>
            
            <h3 class="text-xl font-bold text-gray-800 mb-2">{{ pkg.name }}</h3>
            
            <div class="text-4xl font-bold text-pink-600 mb-1">
              {{ pkg.coins }}
            </div>
            <p class="text-sm text-gray-500 mb-2">Coins</p>
            
            <p v-if="pkg.bonus_coins > 0" class="text-sm text-green-600 font-semibold bg-green-50 rounded-full px-3 py-1 inline-block mb-3">
              <i class="fas fa-gift mr-1"></i>+ {{ pkg.bonus_coins }} Bonus Coins
            </p>
            
            <div class="mt-3 pt-3 border-t border-gray-200">
              <p class="text-3xl font-bold text-gray-800">₹{{ pkg.price }}</p>
              <p v-if="pkg.description" class="text-xs text-gray-500 mt-2">{{ pkg.description }}</p>
            </div>
          </div>
          
          <!-- Buy Button -->
          <button
            @click="onBuy(pkg)"
            :disabled="isProcessing || processingId === pkg.id"
            class="w-full bg-gradient-to-r from-pink-500 to-purple-600 text-white font-semibold py-3 rounded-lg hover:from-pink-600 hover:to-purple-700 transition transform hover:scale-105 disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <span v-if="processingId === pkg.id || isProcessing">
              <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
            </span>
            <span v-else>
              <i class="fas fa-shopping-cart mr-2"></i>Buy Now
            </span>
          </button>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-if="!loading && packages.length === 0" class="text-center py-12">
        <i class="fas fa-box-open text-gray-300 text-6xl mb-4"></i>
        <p class="text-gray-600 text-lg">No coin packages available at the moment.</p>
      </div>
    </div>
    
    <!-- Payment Benefits -->
    <div class="mt-8 bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl p-6 border border-blue-200">
      <h3 class="text-lg font-bold text-gray-800 mb-4">
        <i class="fas fa-shield-alt mr-2 text-blue-600"></i>Safe & Secure Payment
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="flex items-start gap-3">
          <i class="fas fa-lock text-green-600 text-xl mt-1"></i>
          <div>
            <p class="font-semibold text-gray-800">Secure Payment</p>
            <p class="text-sm text-gray-600">256-bit SSL encryption</p>
          </div>
        </div>
        <div class="flex items-start gap-3">
          <i class="fas fa-redo text-blue-600 text-xl mt-1"></i>
          <div>
            <p class="font-semibold text-gray-800">Instant Delivery</p>
            <p class="text-sm text-gray-600">Coins added immediately</p>
          </div>
        </div>
        <div class="flex items-start gap-3">
          <i class="fas fa-headset text-purple-600 text-xl mt-1"></i>
          <div>
            <p class="font-semibold text-gray-800">24/7 Support</p>
            <p class="text-sm text-gray-600">We're here to help</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'BuyCoins',
  props: {
    packages: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    },
    // Razorpay public key (required to open checkout)
    razorpayKey: {
      type: String,
      default: ''
    },
    // Backend endpoint to create Razorpay order
    createOrderUrl: {
      type: String,
      default: '/api/wallet/purchase'
    },
    // Backend endpoint to verify payment and credit coins
    verifyPaymentUrl: {
      type: String,
      default: '/api/wallet/verify-payment'
    },
    // Prefill user info in Razorpay checkout
    prefill: {
      type: Object,
      default: () => ({ name: '', email: '', contact: '' })
    }
  },
  emits: ['purchase', 'order-created', 'payment-success', 'payment-failed', 'payment-notify'],
  data() {
    return {
      processingId: null,
      isProcessing: false, // global lock to prevent multiple simultaneous purchases
      razorpayReady: !!(typeof window !== 'undefined' && window.Razorpay),
      currentTransactionId: null,
      failedPackage: null
    };
  },
  computed: {
    resolvedRazorpayKey() {
      // Prefer prop, fallback to Vite env variable
      const envKey = (import.meta && import.meta.env && import.meta.env.VITE_RAZORPAY_KEY) || '';
      return this.razorpayKey || envKey;
    }
  },
  mounted() {
    this.ensureRazorpay();
  },
  methods: {
    ensureRazorpay() {
      if (typeof window === 'undefined') return;
      if (window.Razorpay) {
        this.razorpayReady = true;
        return;
      }
      const scriptId = 'razorpay-checkout-js';
      if (document.getElementById(scriptId)) return;
      const s = document.createElement('script');
      s.id = scriptId;
      s.src = 'https://checkout.razorpay.com/v1/checkout.js';
      s.async = true;
      s.onload = () => { this.razorpayReady = true; };
      s.onerror = () => { console.error('Failed to load Razorpay checkout.js'); };
      document.body.appendChild(s);
    },

    async onBuy(pkg) {
      return this._createAndCheckout(pkg);
    },

    async _createAndCheckout(pkg) {
      try {
        if (!this.resolvedRazorpayKey) {
          console.error('Missing razorpayKey prop');
          return;
        }
        this.processingId = pkg.id;
        this.isProcessing = true;
        this.failedPackage = pkg;
        await this.ensureRazorpay();

        // Create order on backend (expects only package_id)
        const { data } = await axios.post(this.createOrderUrl, { package_id: pkg.id });

        // Expecting { order, transaction_id, user }
        const order = data?.order || data;
        if (!order || !order.id) {
          throw new Error('Invalid order response');
        }

        // Save transaction for later verification
        this.currentTransactionId = data?.transaction_id || null;

        this.$emit('order-created', { pkg, order });
        this.openCheckout(order, pkg);
      } catch (err) {
        console.error('Order creation failed', err);
        this.$emit('payment-failed', { pkg, error: err, isRetryable: false });
        this.processingId = null;
        this.isProcessing = false;
      }
    },

    async retryPayment() {
      if (this.failedPackage) {
        await this._createAndCheckout(this.failedPackage);
      }
    },

    openCheckout(order, pkg) {
      if (!window.Razorpay) {
        console.error('Razorpay not ready');
        this.processingId = null;
        this.isProcessing = false;
        return;
      }

      const options = {
        key: this.resolvedRazorpayKey,
        amount: order.amount || (order.price * 100),
        currency: order.currency || 'INR',
        name: 'Namate24',
        description: pkg.name,
        order_id: order.id || order.razorpay_order_id,
        notes: order.notes || {},
        prefill: {
          name: this.prefill.name || '',
          email: this.prefill.email || '',
          contact: this.prefill.contact || ''
        },
        theme: { color: '#ec4899' },
        handler: (response) => {
          this.handlePaymentSuccess(response, order, pkg);
        },
        modal: {
          ondismiss: () => {
            this.processingId = null;
            this.isProcessing = false;
          }
        }
      };

      const rzp = new window.Razorpay(options);
      rzp.on('payment.failed', (resp) => {
        const orderId = resp?.error?.metadata?.order_id;
        if (orderId) {
          axios.post(`/api/wallet/order/${orderId}/cancel`, {
            reason: resp?.error?.description || 'Payment failed',
          }).catch(() => {});
        }
        // Gateway failures are retryable; store package for retry
        this.failedPackage = this.failedPackage || pkg;
        // ensure we remember original package id if Razorpay metadata not set
        if (!this.failedPackage.id) {
          this.failedPackage = { ...pkg };
        }
        this.$emit('payment-failed', { pkg: this.failedPackage, error: resp.error, isRetryable: true });
        this.processingId = null;
        this.isProcessing = false;
      });
      rzp.open();
    },

    async handlePaymentSuccess(response, order, pkg) {
      try {
        // Send to backend for signature verification and wallet credit
        const verifyPayload = {
          transaction_id: this.currentTransactionId,
          razorpay_payment_id: response.razorpay_payment_id,
          razorpay_order_id: response.razorpay_order_id,
          razorpay_signature: response.razorpay_signature
        };
        const { data } = await axios.post(this.verifyPaymentUrl, verifyPayload);
        const notify = {
          type: 'success',
          title: 'Payment successful',
          message: 'Coins credited to your wallet. View transaction history.',
          ctaUrl: '/wallet/payment-history'
        };
        this.$emit('payment-success', { pkg, response, result: data });
        this.$emit('payment-notify', notify);
      } catch (err) {
        console.error('Payment verification failed', err);
        // Verification failures are NOT retryable (backend issue, not gateway)
        const notify = {
          type: 'error',
          title: 'Payment failed',
          message: 'Your payment was not completed. Check transactions or retry.',
          ctaUrl: '/wallet/payment-history'
        };
        this.$emit('payment-failed', { pkg, error: err, isRetryable: false });
        this.$emit('payment-notify', notify);
      } finally {
        this.processingId = null;
        this.isProcessing = false;
      }
    }
  }
};
</script>
