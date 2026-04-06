<template>
  <div>
    <!-- Custom Coins Purchase -->
    <div>
      <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-shopping-cart mr-2"></i>Buy Coins
      </h2>
      
      <div class="bg-white rounded-xl shadow-md p-8 max-w-md mx-auto">
        <!-- Coin Input -->
        <div class="mb-6">
          <label class="block text-sm font-semibold text-gray-700 mb-3">
            <i class="fas fa-coins mr-2 text-yellow-500"></i>Number of Coins
          </label>
          <div class="relative">
            <input
              v-model.number="customCoins"
              type="number"
              min="99"
              step="1"
              placeholder="Enter amount (minimum 99 coins)"
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-pink-500 focus:outline-none text-lg font-semibold"
            />
            <span class="absolute right-4 top-3 text-gray-500 text-sm">coins</span>
          </div>
          <p v-if="customCoins < 99 && customCoins > 0" class="text-red-600 text-xs mt-2">
            <i class="fas fa-exclamation-circle mr-1"></i>Minimum 99 coins required
          </p>
          <p class="text-gray-500 text-xs mt-2">Minimum: 99 coins</p>
        </div>

        <!-- Price Display -->
        <div v-if="customCoins >= 99" class="mb-6 p-4 bg-gradient-to-r from-pink-50 to-purple-50 rounded-lg border border-pink-200">
          <div class="flex justify-between items-center mb-2">
            <span class="text-gray-700 font-medium">Amount:</span>
            <span class="text-2xl font-bold" :class="isIndiaUser ? 'text-blue-600' : 'text-blue-600'">
              {{ calculatedPrice.symbol }}{{ calculatedPrice.total }}
            </span>
          </div>
          <p v-if="isIndiaUser" class="text-xs text-gray-600">
            Base: ₹{{ calculatedPrice.base }} + GST (18%): ₹{{ calculatedPrice.tax }}
          </p>
          <p v-else class="text-xs text-gray-600">
            Special Rate: 99 coins = $15 (for non-India)
          </p>
        </div>

        <!-- Buy Button -->
        <button
          @click="buyCustomCoins"
          :disabled="customCoins < 99 || isProcessing"
          class="w-full bg-gradient-to-r from-pink-500 to-purple-600 text-white font-semibold py-3 px-4 rounded-lg hover:from-pink-600 hover:to-purple-700 transition transform hover:scale-105 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:scale-100"
        >
          <span v-if="isProcessing">
            <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
          </span>
          <span v-else>
            <i class="fas fa-shopping-cart mr-2"></i>Buy {{ customCoins >= 99 ? customCoins : 0 }} Coins
          </span>
        </button>
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
    // Optional: country code (e.g., 'IN') of current user to show pricing hints
    userCountryCode: {
      type: String,
      default: 'IN'
    },
    // Default currency (INR for India)
    defaultCurrency: {
      type: String,
      default: 'INR'
    },
    // Razorpay public key (required to open checkout)
    razorpayKey: {
      type: String,
      default: ''
    },
    // Backend endpoint to create Razorpay order for custom coins
    createOrderUrl: {
      type: String,
      default: '/api/wallet/purchase-custom-coins'
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
      customCoins: 99,
      isProcessing: false,
      razorpayReady: !!(typeof window !== 'undefined' && window.Razorpay),
      currentTransactionId: null
    };
  },
  computed: {
    resolvedRazorpayKey() {
      // Prefer prop, fallback to Vite env variable
      const envKey = (import.meta && import.meta.env && import.meta.env.VITE_RAZORPAY_KEY) || '';
      return this.razorpayKey || envKey;
    },
    isIndiaUser() {
      const countryCode = String(this.userCountryCode || '').toUpperCase();
      return ['IN', 'IND', '91'].includes(countryCode);
    },
    calculatedPrice() {
      if (this.customCoins < 99) {
        return { symbol: '', base: '0', tax: '0', total: '0' };
      }

      // Special pricing: 99 coins = $15 for non-Indian users
      if (this.customCoins === 99 && !this.isIndiaUser) {
        return {
          symbol: '$',
          base: '15.00',
          tax: '0.00',
          total: '15.00'
        };
      }

      // Formula-based pricing: 1.25 USD per 100 coins
      const basePricePerCoin = 1.25 / 100;
      const priceInUSD = this.customCoins * basePricePerCoin;

      if (this.isIndiaUser) {
        // INR with 18% GST
        const conversionRate = 83.5;
        const priceInINR = priceInUSD * conversionRate;
        const gstRate = 0.18;
        const taxAmount = priceInINR * gstRate;
        const totalAmount = priceInINR + taxAmount;
        return {
          symbol: '₹',
          base: priceInINR.toFixed(2),
          tax: taxAmount.toFixed(2),
          total: totalAmount.toFixed(2)
        };
      } else {
        // USD pricing
        return {
          symbol: '$',
          base: priceInUSD.toFixed(2),
          tax: '0.00',
          total: priceInUSD.toFixed(2)
        };
      }
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

    async buyCustomCoins() {
      if (this.customCoins < 99) {
        console.error('Minimum 99 coins required');
        return;
      }

      try {
        if (!this.resolvedRazorpayKey) {
          console.error('Missing razorpayKey prop');
          return;
        }
        this.isProcessing = true;
        await this.ensureRazorpay();

        // Create order on backend for custom coins
        const { data } = await axios.post(this.createOrderUrl, { 
          coins: this.customCoins,
          reason: 'custom_purchase'
        });

        const order = data?.order || data;
        if (!order || !order.id) {
          throw new Error('Invalid order response');
        }

        this.currentTransactionId = data?.transaction_id || null;

        this.$emit('order-created', { coins: this.customCoins, order });
        this.openCheckout(order);
      } catch (err) {
        console.error('Custom coin order creation failed', err);
        this.$emit('payment-failed', { error: err, isRetryable: false });
        this.isProcessing = false;
      }
    },

    openCheckout(order) {
      if (!window.Razorpay) {
        console.error('Razorpay not ready');
        this.isProcessing = false;
        return;
      }

      const options = {
        key: this.resolvedRazorpayKey,
        amount: order.amount,
        currency: order.currency || this.defaultCurrency,
        name: 'Namate24',
        description: `Buy ${this.customCoins} Coins`,
        order_id: order.id || order.razorpay_order_id,
        notes: order.notes || {},
        prefill: {
          name: this.prefill.name || '',
          email: this.prefill.email || '',
          contact: this.prefill.contact || ''
        },
        theme: { color: '#ec4899' },
        handler: (response) => {
          this.handlePaymentSuccess(response, order);
        },
        modal: {
          ondismiss: async () => {
            try {
              await axios.post('/api/wallet/payment-cancelled', {
                order_id: order.id,
                transaction_id: this.currentTransactionId,
                reason: 'user_dismissed'
              });
            } catch (err) {
              console.error('Failed to record payment cancellation', err);
            } finally {
              this.isProcessing = false;
            }
          }
        }
      };

      const rzp = new window.Razorpay(options);
      rzp.on('payment.failed', (resp) => {
        this.markPaymentFailed(order, resp);
      });
      rzp.open();
    },

    async handlePaymentSuccess(response, order) {
      try {
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
          message: `${this.customCoins} coins credited to your wallet. View transaction history.`,
          ctaUrl: '/wallet/payment-history'
        };
        this.$emit('payment-success', { coins: this.customCoins, response, result: data });
        this.$emit('payment-notify', notify);
      } catch (err) {
        console.error('Payment verification failed', err);
        const notify = {
          type: 'error',
          title: 'Payment failed',
          message: 'Your payment was not completed. Check transactions or retry.',
          ctaUrl: '/wallet/payment-history'
        };
        this.$emit('payment-failed', { error: err, isRetryable: false });
        this.$emit('payment-notify', notify);
      } finally {
        this.isProcessing = false;
      }
    },

    async markPaymentFailed(order, response) {
      try {
        const payload = {
          order_id: order.id,
          transaction_id: this.currentTransactionId,
          razorpay_error: response?.error?.code || 'GATEWAY_ERROR',
          error_description: response?.error?.description || 'Payment gateway error',
          error_reason: response?.error?.reason || 'unknown'
        };

        await axios.post('/api/wallet/payment-failed', payload);
        console.log('Payment failure recorded', payload);

        const notify = {
          type: 'error',
          title: 'Payment failed',
          message: response?.error?.description || 'Payment gateway declined the transaction',
          ctaUrl: '/wallet/payment-history'
        };

        this.$emit('payment-failed', { error: response?.error, isRetryable: true });
        this.$emit('payment-notify', notify);
      } catch (err) {
        console.error('Failed to record payment failure', err);
        this.$emit('payment-failed', { error: response?.error, isRetryable: true });
      } finally {
        this.isProcessing = false;
      }
    }
  }
};
</script>
