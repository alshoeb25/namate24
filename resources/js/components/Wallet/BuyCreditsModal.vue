<template>
  <div v-show="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold">Buy Credits</h3>
        <button class="text-gray-500 hover:text-gray-700" @click="close">&times;</button>
      </div>

      <div v-if="packages.length === 0" class="py-8 text-center text-gray-500">Loading packages...</div>

      <ul v-else class="space-y-4">
        <li v-for="p in packages" :key="p.id" class="flex items-center justify-between border rounded p-3">
          <div>
            <div class="font-medium">{{ p.name }}</div>
            <div class="text-sm text-gray-500">{{ p.credits }} credits • ₹{{ p.price }}</div>
          </div>
          <div class="flex items-center gap-2">
            <button class="bg-emerald-500 text-white px-3 py-1 rounded-md hover:bg-emerald-600"
                    @click="createOrderAndCheckout(p.id)" :disabled="loading">
              Buy
            </button>
          </div>
        </li>
      </ul>

      <div class="mt-4 text-right">
        <button class="text-sm text-gray-600 hover:underline" @click="close">Cancel</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  emits: ['bought'],
  data() {
    return {
      visible: false,
      packages: [],
      loading: false,
    };
  },
  methods: {
    async open() {
      this.visible = true;
      await this.load();
    },
    close() {
      this.visible = false;
    },
    async load() {
      try {
        const res = await axios.get('/api/credit-packages');
        this.packages = res.data;
      } catch (e) {
        console.error(e);
      }
    },

    // Create order on server and open Razorpay Checkout
    async createOrderAndCheckout(packageId) {
      this.loading = true;
      try {
        const res = await axios.post('/api/wallet/buy', { package_id: packageId });
        const { order, purchase_id } = res.data;

        // Ensure Razorpay script is loaded
        await this.loadRazorpayScript();

        const options = {
          key: import.meta.env.VITE_RAZORPAY_KEY, // expose VITE_RAZORPAY_KEY in .env
          amount: order.amount,
          currency: order.currency,
          name: document.title || 'Namate24',
          description: 'Credit purchase',
          order_id: order.id,
          handler: async (response) => {
            // response contains razorpay_payment_id, razorpay_order_id, razorpay_signature
            try {
              // Call backend to verify signature and finalize purchase
              await axios.post('/api/wallet/verify', {
                purchase_id,
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_order_id: response.razorpay_order_id,
                razorpay_signature: response.razorpay_signature
              });

              this.$emit('bought');
              alert('Payment successful. Credits added to your wallet.');
              this.close();
            } catch (err) {
              console.error('Verification failed', err);
              alert('Payment succeeded but verification failed. Please contact support.');
            }
          },
          modal: {
            ondismiss: function() {
              // optional: notify server or mark attempt
            },
          },
          prefill: {
            name: window?.NAMATE24?.user?.name || '',
            email: window?.NAMATE24?.user?.email || '',
            contact: window?.NAMATE24?.user?.phone || ''
          }
        };

        const rzp = new window.Razorpay(options);
        rzp.open();
      } catch (e) {
        console.error(e);
        alert('Could not create order. Try again.');
      } finally {
        this.loading = false;
      }
    },

    loadRazorpayScript() {
      return new Promise((resolve, reject) => {
        if (window.Razorpay) return resolve();
        const script = document.createElement('script');
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Razorpay SDK failed to load'));
        document.head.appendChild(script);
      });
    }
  }
};
</script>