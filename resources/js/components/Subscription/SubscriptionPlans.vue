<template>
  <div class="subscription-container">
    <!-- Navigation Menu -->
    <div class="subscription-menu">
      <div class="menu-header">
        <h2 class="menu-title">Subscription Plans</h2>
      </div>
      <div class="menu-tabs">
        <button 
          v-if="hasActiveSubscription"
          class="menu-tab" 
          :class="{ active: activeTab === 'current' }"
          @click="activeTab = 'current'"
        >
          <span class="tab-icon">✓</span> Current Plan
        </button>
        <button 
          class="menu-tab" 
          :class="{ active: activeTab === 'browse' }"
          @click="activeTab = 'browse'"
        >
          <span class="tab-icon">📋</span> Browse Plans
        </button>
        <button 
          class="menu-tab" 
          :class="{ active: activeTab === 'history' }"
          @click="activeTab = 'history'"
        >
          <span class="tab-icon">📜</span> History
        </button>
      </div>
      <div class="country-info">
        <span v-if="isIndiaUser" class="badge badge-india">🇮🇳 India - INR (with 18% GST)</span>
        <span v-else class="badge badge-foreign">🌍 International - USD</span>
      </div>
    </div>

    <!-- Current Subscription Tab -->
    <div v-if="hasActiveSubscription && activeTab === 'current'" class="status-section">
      <div class="status-card">
        <div class="card-header">
          <h2>Your Current Subscription</h2>
          <span class="badge-active">Active</span>
        </div>
        
        <div class="status-details">
          <div class="detail-group">
            <h3>Plan Information</h3>
            <div class="status-item">
              <label>Plan Name:</label>
              <span class="highlight">{{ subscription.plan_name }}</span>
            </div>
            <div class="status-item">
              <label>Price:</label>
              <span class="highlight">
                <span v-if="isIndiaUser">₹{{ subscription.display_price }}</span>
                <span v-else>${{ subscription.display_price }}</span>
              </span>
            </div>
            <div v-if="isIndiaUser && subscription.gst_amount > 0" class="status-item">
              <label>GST (18%):</label>
              <span>₹{{ (subscription.gst_amount).toFixed(2) }}</span>
            </div>
          </div>

          <div class="detail-group">
            <h3>Access Details</h3>
            <div class="status-item">
              <label>Views Allowed:</label>
              <span v-if="subscription.unlimited_views" class="highlight">Unlimited</span>
              <span v-else class="highlight">{{ subscription.views_allowed }} views</span>
            </div>
            <div v-if="!subscription.unlimited_views" class="status-item">
              <label>Views Used:</label>
              <span>{{ subscription.views_used }} / {{ subscription.views_allowed }}</span>
            </div>
            <div v-if="!subscription.unlimited_views" class="progress-bar">
              <div class="progress-fill" :style="{ width: (subscription.views_used / subscription.views_allowed * 100) + '%' }"></div>
            </div>
          </div>

          <div class="detail-group">
            <h3>Validity</h3>
            <div class="status-item">
              <label>Activated On:</label>
              <span>{{ formatDate(subscription.activated_at) }}</span>
            </div>
            <div class="status-item">
              <label>Expires On:</label>
              <span class="highlight">{{ formatDate(subscription.expires_at) }}</span>
            </div>
            <div class="status-item">
              <label>Days Remaining:</label>
              <span class="countdown">{{ subscription.remaining_days }} days</span>
            </div>
          </div>

          <div class="expiry-warning" v-if="subscription.remaining_days <= 7">
            <span class="warning-icon">⚠️</span>
            Your subscription will expire soon! Consider renewing now.
          </div>
        </div>

        <div class="status-actions">
          <button class="btn btn-primary btn-large" @click="getRenewOptions">
            ✓ Renew Subscription
          </button>
          <button class="btn btn-outline" @click="cancelSubscription" :disabled="cancelling">
            {{ cancelling ? 'Cancelling...' : '✕ Cancel Subscription' }}
          </button>
        </div>
      </div>

      <!-- Renew Options -->
      <div v-if="showRenewOptions" class="renew-section">
        <h3>Renew Your Subscription</h3>
        <p>Select a plan to extend your access</p>
        <div class="plans-grid">
          <div
            v-for="plan in plans"
            :key="plan.id"
            class="plan-card"
            @click="selectPlan(plan)"
          >
            <h4>{{ plan.name }}</h4>
            <p class="price">
              <span v-if="isIndiaUser">₹{{ plan.display_price }}</span>
              <span v-else>${{ plan.display_price }}</span>
              <span v-if="isIndiaUser && plan.gst_amount > 0" class="gst-info">(+₹{{ (plan.gst_amount).toFixed(2) }} GST)</span>
            </p>
            <p class="views-text">{{ plan.views_text }}</p>
            <button class="btn btn-primary">Select</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Browse Plans Tab -->
    <div v-if="activeTab === 'browse' && !showPurchaseFlow" class="plans-section">
      <h2 class="plans-title">Choose Your Plan</h2>
      <p class="plans-description">
        Select a subscription plan to unlock access to tutor profiles and requirements with the specified view limits.
      </p>

      <div class="plans-grid">
        <div
          v-for="plan in plans"
          :key="plan.id"
          class="plan-card"
          :class="{ popular: plan.id === 1 }"
        >
          <div class="plan-header">
            <h3 class="plan-name">{{ plan.name }}</h3>
            <span v-if="plan.id === 1" class="badge-popular">Most Popular</span>
          </div>

          <div class="plan-price">
            <span v-if="isIndiaUser" class="currency">₹</span>
            <span v-else class="currency">$</span>
            <span class="amount">{{ plan.display_price }}</span>
            <span class="period">/month</span>
          </div>

          <div v-if="isIndiaUser && plan.gst_amount > 0" class="gst-breakdown">
            <small>Base: ₹{{ (plan.price - plan.gst_amount).toFixed(2) }}</small>
            <small>+ GST (18%): ₹{{ (plan.gst_amount).toFixed(2) }}</small>
          </div>

          <div class="plan-features">
            <p class="feature">
              ✓ Valid for {{ plan.validity_text }}
            </p>
            <p class="feature">
              ✓ {{ plan.views_text }}
            </p>
            <p v-if="plan.description" class="description">{{ plan.description }}</p>
          </div>

          <button
            class="btn-subscribe"
            :class="{ 'btn-primary': plan.id === 1, 'btn-secondary': plan.id !== 1 }"
            @click="selectPlan(plan)"
            :disabled="loading"
          >
            {{ loading ? 'Processing...' : 'Subscribe Now' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Current Subscription Status -->
    <div v-if="hasActiveSubscription && !showPurchaseFlow && activeTab === 'browse'" class="status-section">
      <div class="status-card">
        <h2>Current Subscription</h2>
        
        <div class="status-details">
          <div class="status-item">
            <label>Plan:</label>
            <span>{{ subscription.plan_name }}</span>
          </div>
          <div class="status-item">
            <label>Price:</label>
            <span>
              <span v-if="isIndiaUser">₹{{ subscription.display_price }}</span>
              <span v-else>${{ subscription.display_price }}</span>
            </span>
          </div>
          <div v-if="isIndiaUser && subscription.gst_amount > 0" class="status-item">
            <label>GST (18%):</label>
            <span>₹{{ (subscription.gst_amount).toFixed(2) }}</span>
          </div>
          <div class="status-item">
            <label>Expires:</label>
            <span>{{ subscription.expires_at }}</span>
          </div>
          <div class="status-item">
            <label>Days Remaining:</label>
            <span class="highlight">{{ subscription.remaining_days }} days</span>
          </div>
          <div class="status-item">
            <label>Views:</label>
            <span>
              <span v-if="subscription.unlimited_views" class="highlight">Unlimited</span>
              <span v-else class="highlight">
                {{ subscription.views_used }} / {{ subscription.views_allowed }}
              </span>
            </span>
          </div>
        </div>

        <div class="status-actions">
          <button class="btn btn-secondary" @click="getRenewOptions">
            Renew Subscription
          </button>
          <button class="btn btn-outline" @click="cancelSubscription" :disabled="cancelling">
            {{ cancelling ? 'Cancelling...' : 'Cancel Subscription' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Purchase Flow -->
    <div v-if="showPurchaseFlow" class="purchase-section">
      <button class="btn-back" @click="cancelPurchase">
        ← Back to Plans
      </button>

      <div class="purchase-card">
        <h2>Complete Your Purchase</h2>

        <div class="order-summary">
          <h3>Order Summary</h3>
          <div class="summary-row">
            <span>Plan:</span>
            <strong>{{ selectedPlan.name }}</strong>
          </div>
          <div class="summary-row">
            <span>Validity:</span>
            <strong>{{ selectedPlan.validity_text }}</strong>
          </div>
          <div class="summary-row">
            <span>Views:</span>
            <strong>{{ selectedPlan.views_text }}</strong>
          </div>
          <div v-if="isIndiaUser && selectedPlan.gst_amount > 0" class="summary-row">
            <span>Base Price:</span>
            <strong>₹{{ (selectedPlan.price - selectedPlan.gst_amount).toFixed(2) }}</strong>
          </div>
          <div v-if="isIndiaUser && selectedPlan.gst_amount > 0" class="summary-row">
            <span>GST (18%):</span>
            <strong>₹{{ (selectedPlan.gst_amount).toFixed(2) }}</strong>
          </div>
          <div class="summary-row total">
            <span>Total Amount:</span>
            <strong>
              <span v-if="isIndiaUser">₹{{ selectedPlan.price.toFixed(2) }}</span>
              <span v-else>${{ selectedPlan.price.toFixed(2) }}</span>
            </strong>
          </div>
        </div>

        <div class="payment-method">
          <h3>Payment Method</h3>
          <p class="info">
            <i class="fas fa-info-circle mr-2"></i> You will be redirected to Razorpay to complete the payment securely.
          </p>

          <button
            class="btn btn-primary btn-large"
            @click="initiatePayment"
            :disabled="loading"
          >
            {{ loading ? 'Processing...' : 'Proceed to Payment' }}
          </button>
        </div>

        <div class="payment-info">
          <p>
            <i class="fas fa-lock mr-2"></i> Your payment information is secure and encrypted.
          </p>
          <p>
            <i class="fas fa-shield-alt mr-2"></i> We accept all major credit/debit cards and digital wallets.
          </p>
        </div>
      </div>
    </div>

    <!-- Subscription History -->
    <div v-if="activeTab === 'history'" class="history-section">
      <h2>Subscription History</h2>

      <div v-if="history.length === 0" class="no-history">
        <p>No subscription history yet.</p>
      </div>

      <table v-else class="history-table">
        <thead>
          <tr>
            <th>Plan</th>
            <th>Price</th>
            <th>Activated</th>
            <th>Expires</th>
            <th>Views</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="sub in history" :key="sub.id">
            <td>{{ sub.plan_name }}</td>
            <td>
              <span v-if="sub.is_india_user">₹{{ Number(sub.price).toFixed(2) }}</span>
              <span v-else>${{ Number(sub.price).toFixed(2) }}</span>
            </td>
            <td>{{ formatDate(sub.activated_at) }}</td>
            <td>{{ formatDate(sub.expires_at) }}</td>
            <td>{{ sub.views_text }}</td>
            <td>
              <span class="badge" :class="`status-${sub.status}`">
                {{ sub.status }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="alert alert-error">
      {{ error }}
      <button class="btn-close" @click="error = null">×</button>
    </div>

    <!-- Success Message -->
    <div v-if="success" class="alert alert-success">
      {{ success }}
      <button class="btn-close" @click="success = null">×</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'SubscriptionPlans',
  data() {
    return {
      plans: [],
      subscription: null,
      hasActiveSubscription: false,
      selectedPlan: null,
      showPurchaseFlow: false,
      showRenewOptions: false,
      activeTab: 'current', // current, browse, history
      history: [],
      loading: false,
      cancelling: false,
      error: null,
      success: null,
      isIndiaUser: false,
      razorpayReady: !!(typeof window !== 'undefined' && window.Razorpay),
    };
  },
  computed: {
    resolvedRazorpayKey() {
      // Prefer Vite env variable, with safe checks
      const envKey = (import.meta && import.meta.env && import.meta.env.VITE_RAZORPAY_KEY) || '';
      return envKey || null;
    }
  },
  async mounted() {
    this.loadPlans();
    this.loadSubscriptionStatus();
  },
  watch: {
    activeTab(newTab) {
      if (newTab === 'history' && this.history.length === 0) {
        this.loadHistory();
      }
    },
  },
  methods: {
    async loadPlans() {
      try {
        const response = await axios.get('/api/subscriptions/plans');
        this.plans = response.data.data;
        this.isIndiaUser = response.data.is_india_user;
        
        // If no active subscription, show plans tab
        if (!this.hasActiveSubscription) {
          this.activeTab = 'browse';
        }
      } catch (error) {
        this.error = 'Failed to load subscription plans';
        console.error(error);
      }
    },

    async loadSubscriptionStatus() {
      try {
        const response = await axios.get('/api/subscriptions/status');
        const status = response.data.data;

        if (status.has_active_subscription) {
          this.hasActiveSubscription = true;
          this.subscription = status;
          this.activeTab = 'current';
        } else {
          this.hasActiveSubscription = false;
          this.activeTab = 'browse';
        }
      } catch (error) {
        this.hasActiveSubscription = false;
        this.activeTab = 'browse';
      }
    },

    async loadHistory() {
      try {
        const response = await axios.get('/api/subscriptions/history');
        this.history = response.data.data;
      } catch (error) {
        this.error = 'Failed to load subscription history';
      }
    },

    getRenewOptions() {
      this.showRenewOptions = true;
    },

    selectPlan(plan) {
      this.selectedPlan = plan;
      this.showPurchaseFlow = true;
      this.showRenewOptions = false;
      window.scrollTo(0, 0);
    },

    cancelPurchase() {
      this.showPurchaseFlow = false;
      this.selectedPlan = null;
    },

    async initiatePayment() {
      if (!this.selectedPlan) return;

      this.loading = true;
      this.error = null;

      try {
        // Ensure Razorpay script is loaded
        await this.loadRazorpayScript();

        const orderResponse = await axios.post('/api/subscriptions/purchase', {
          plan_id: this.selectedPlan.id,
        });

        const orderData = orderResponse.data.data;
        this.openRazorpayCheckout(orderData);
      } catch (error) {
        console.error('Subscription order error:', error);
        console.error('Response data:', error.response?.data);
        this.error = error.response?.data?.message || 'Failed to create order';
        this.loading = false;
      }
    },

    openRazorpayCheckout(orderData) {
      // Check if Razorpay is loaded
      if (!window.Razorpay) {
        console.error('Razorpay SDK not loaded');
        this.error = 'Payment gateway not loaded. Please refresh the page and try again.';
        this.loading = false;
        return;
      }

      // Get Razorpay key from computed property
      const razorpayKey = this.resolvedRazorpayKey;
      
      if (!razorpayKey) {
        console.error('Razorpay key not configured', {
          envKey: import.meta.env.VITE_RAZORPAY_KEY,
          computed: this.resolvedRazorpayKey
        });
        this.error = 'Payment gateway is not properly configured. Please contact support.';
        this.loading = false;
        return;
      }

      // Validate Razorpay order ID exists
      if (!orderData.razorpay_order_id) {
        console.error('No Razorpay order ID received from server', orderData);
        this.error = 'Payment order creation failed. Please try again.';
        this.loading = false;
        return;
      }
      
      const options = {
        key: razorpayKey,
        order_id: orderData.razorpay_order_id, // Use ACTUAL Razorpay order ID
        amount: orderData.amount,
        currency: orderData.currency,
        name: 'Namate24',
        description: `${orderData.plan_name} Subscription`,
        handler: (response) => {
          this.verifyPayment(orderData.order_id, response, 'success');
        },
        prefill: {
          name: this.$store?.state?.user?.name || '',
          email: this.$store?.state?.user?.email || '',
          contact: this.$store?.state?.user?.phone || '',
        },
        theme: {
          color: '#4CAF50',
        },
      };

      const rzp = new window.Razorpay(options);
      
      rzp.on('payment.failed', (response) => {
        // Handle payment failure from Razorpay
        this.handlePaymentFailure(response, orderData.order_id);
      });

      rzp.on('payment.authorized', (response) => {
        // This is called for pending payments that may complete later
        this.verifyPayment(orderData.order_id, response, 'pending');
      });

      rzp.open();
    },

    async handlePaymentFailure(razorpayResponse, orderId) {
      try {
        this.loading = true;
        // Notify backend that payment failed
        const response = await axios.post('/api/subscriptions/payment-failed', {
          order_id: orderId,
          error_message: razorpayResponse?.error?.description || 'Payment processing failed',
          error_reason: razorpayResponse?.error?.reason || 'unknown',
          razorpay_error: razorpayResponse?.error?.code || 'GATEWAY_ERROR',
        });

        this.error = 'Payment failed. Please try again or contact support.';
        this.loading = false;

        setTimeout(() => {
          this.showPurchaseFlow = false;
          this.selectedPlan = null;
          this.loadPlans();
        }, 3000);
      } catch (error) {
        this.error = 'Error processing payment failure. Please contact support.';
        this.loading = false;
      }
    },

    async verifyPayment(orderId, razorpayResponse, paymentStatus = 'success') {
      try {
        const response = await axios.post('/api/subscriptions/verify-payment', {
          order_id: orderId,
          razorpay_payment_id: razorpayResponse.razorpay_payment_id,
          razorpay_order_id: razorpayResponse.razorpay_order_id,
          razorpay_signature: razorpayResponse.razorpay_signature,
          payment_status: paymentStatus,
        });

        if (response.data.status === 'failed') {
          this.error = 'Payment failed. Please try again.';
          this.loading = false;
          return;
        }

        if (response.data.status === 'pending') {
          this.success = 'Payment is being processed. We will notify you once it completes.';
          this.loading = false;
          
          setTimeout(() => {
            this.showPurchaseFlow = false;
            this.selectedPlan = null;
            this.loadSubscriptionStatus();
            this.loadPlans();
          }, 3000);
          return;
        }

        // Success case
        this.success = 'Subscription activated successfully!';
        this.loading = false;

        setTimeout(() => {
          this.showPurchaseFlow = false;
          this.selectedPlan = null;
          this.loadSubscriptionStatus();
          this.loadPlans();
        }, 2000);
      } catch (error) {
        const errorMessage = error.response?.data?.message || 'Payment verification failed';
        const status = error.response?.data?.status;

        if (status === 'failed') {
          this.error = 'Payment failed. Please try again.';
        } else if (status === 'pending') {
          this.success = 'Payment is pending. We will notify you when it completes.';
          setTimeout(() => {
            this.showPurchaseFlow = false;
            this.selectedPlan = null;
          }, 3000);
        } else {
          this.error = errorMessage;
        }
        this.loading = false;
      }
    },

    async cancelSubscription() {
      if (!confirm('Are you sure you want to cancel your subscription? This cannot be undone.')) {
        return;
      }

      this.cancelling = true;
      this.error = null;

      try {
        await axios.post('/api/subscriptions/cancel', {
          reason: 'User requested cancellation',
        });

        this.success = 'Subscription cancelled successfully';
        this.cancelling = false;
        this.hasActiveSubscription = false;
        this.subscription = null;
        this.activeTab = 'browse';
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to cancel subscription';
        this.cancelling = false;
      }
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
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
    },
  },
};
</script>

<style scoped lang="css">
.subscription-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

/* Navigation Menu */
.subscription-menu {
  background: white;
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 30px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.menu-header {
  margin-bottom: 15px;
}

.menu-title {
  font-size: 24px;
  font-weight: bold;
  color: #333;
  margin: 0;
}

.menu-tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
  border-bottom: 2px solid #f0f0f0;
  overflow-x: auto;
}

.menu-tab {
  background: transparent;
  border: none;
  padding: 12px 20px;
  font-size: 15px;
  font-weight: 500;
  color: #666;
  cursor: pointer;
  border-bottom: 3px solid transparent;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.menu-tab:hover {
  color: #4CAF50;
}

.menu-tab.active {
  color: #4CAF50;
  border-bottom-color: #4CAF50;
}

.tab-icon {
  margin-right: 8px;
  font-size: 16px;
}

.country-info {
  text-align: center;
}

.badge {
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
}

.badge-india {
  background: #fff3cd;
  color: #856404;
  border: 1px solid #ffc107;
}

.badge-foreign {
  background: #cfe2ff;
  color: #084298;
  border: 1px solid #0d6efd;
}

.badge-active {
  background: #d4edda;
  color: #155724;
  padding: 6px 12px;
  font-size: 12px;
  border-radius: 4px;
}

/* Current Subscription Status */
.status-section {
  margin-bottom: 30px;
}

.status-card {
  background: linear-gradient(135deg, #f0f9f0 0%, #ffffff 100%);
  border: 2px solid #4CAF50;
  border-radius: 12px;
  padding: 30px;
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 2px solid #e0f2e0;
}

.card-header h2 {
  margin: 0;
  color: #333;
  font-size: 22px;
}

.status-details {
  margin-bottom: 30px;
}

.detail-group {
  background: white;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 15px;
}

.detail-group h3 {
  margin: 0 0 15px 0;
  color: #4CAF50;
  font-size: 14px;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-item {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid #f0f0f0;
}

.status-item:last-child {
  border-bottom: none;
}

.status-item label {
  font-weight: 600;
  color: #666;
  min-width: 150px;
}

.status-item .highlight {
  color: #4CAF50;
  font-weight: bold;
  font-size: 16px;
}

.countdown {
  color: #4CAF50;
  font-weight: bold;
  font-size: 18px;
}

.progress-bar {
  background: #f0f0f0;
  height: 8px;
  border-radius: 4px;
  overflow: hidden;
  margin: 10px 0;
}

.progress-fill {
  background: linear-gradient(90deg, #4CAF50, #66bb6a);
  height: 100%;
  transition: width 0.3s ease;
}

.expiry-warning {
  background: #fff3cd;
  border: 1px solid #ffc107;
  color: #856404;
  padding: 12px 16px;
  border-radius: 6px;
  margin-top: 20px;
  display: flex;
  align-items: center;
  font-weight: 500;
}

.warning-icon {
  margin-right: 10px;
  font-size: 18px;
}

.status-actions,
.plans-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 15px;
  margin-bottom: 30px;
}

.status-actions {
  grid-template-columns: 1fr 1fr;
}

/* Plans Grid */
.plans-grid {
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}

.plan-card {
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  padding: 30px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.plan-card:hover {
  border-color: #4CAF50;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
  transform: translateY(-5px);
}

.plan-card.popular {
  border-color: #4CAF50;
  background: #f0f9f0;
  box-shadow: 0 5px 20px rgba(76, 175, 80, 0.2);
}

.plan-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 15px;
}

.plan-name {
  font-size: 22px;
  font-weight: bold;
  color: #333;
  margin: 0;
}

.badge-popular {
  background: #4CAF50;
  color: white;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: bold;
}

.plan-price {
  display: flex;
  align-items: baseline;
  margin-bottom: 10px;
}

.currency {
  font-size: 18px;
  color: #4CAF50;
  margin-right: 5px;
}

.amount {
  font-size: 36px;
  font-weight: bold;
  color: #333;
}

.period {
  font-size: 16px;
  color: #999;
  margin-left: 5px;
}

.gst-breakdown,
.gst-info {
  font-size: 12px;
  color: #999;
  margin-top: 5px;
}

.gst-breakdown small {
  display: block;
  margin: 4px 0;
}

.plan-features {
  margin-bottom: 20px;
}

.feature {
  margin: 10px 0;
  color: #666;
  font-size: 14px;
  display: flex;
  align-items: center;
}

.feature::before {
  content: '✓';
  color: #4CAF50;
  font-weight: bold;
  margin-right: 10px;
}

.description {
  font-size: 13px;
  color: #999;
  font-style: italic;
}

.views-text {
  font-size: 13px;
  color: #999;
  margin: 10px 0;
}

/* Buttons */
.btn-subscribe,
.btn,
.btn-large {
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 5px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary {
  background: #4CAF50;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #45a049;
}

.btn-secondary {
  background: #f0f0f0;
  color: #333;
}

.btn-secondary:hover:not(:disabled) {
  background: #e0e0e0;
}

.btn-outline {
  background: transparent;
  color: #999;
  border: 1px solid #e0e0e0;
}

.btn-outline:hover:not(:disabled) {
  background: #f5f5f5;
}

.btn:disabled,
.btn-subscribe:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-large {
  padding: 16px;
  font-size: 18px;
  margin-top: 20px;
}

.btn-back {
  background: transparent;
  border: none;
  color: #4CAF50;
  font-size: 16px;
  cursor: pointer;
  margin-bottom: 20px;
  padding: 0;
}

.btn-back:hover {
  text-decoration: underline;
}

.btn-close {
  background: transparent;
  border: none;
  color: #999;
  font-size: 24px;
  cursor: pointer;
  float: right;
}

/* Purchase Section */
.purchase-section {
  background: white;
  border-radius: 10px;
  padding: 30px;
}

.purchase-card {
  background: linear-gradient(135deg, #ffffff 0%, #f9f9f9 100%);
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  padding: 30px;
}

.purchase-card h2 {
  color: #333;
  margin-top: 0;
}

.purchase-card h3 {
  color: #666;
  font-size: 18px;
  margin-bottom: 15px;
}

.order-summary {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 30px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #f0f0f0;
  font-size: 14px;
}

.summary-row.total {
  border-bottom: none;
  border-top: 2px solid #e0e0e0;
  padding-top: 15px;
  font-size: 18px;
  color: #4CAF50;
}

.payment-method {
  margin-bottom: 30px;
}

.info {
  background: #f0f9f0;
  border-left: 4px solid #4CAF50;
  padding: 12px 15px;
  border-radius: 4px;
  color: #4CAF50;
  font-size: 14px;
  margin-bottom: 20px;
}

.payment-info {
  text-align: center;
  color: #999;
  font-size: 13px;
}

.payment-info p {
  margin: 10px 0;
}

/* History Section */
.history-section {
  background: white;
  border-radius: 10px;
  padding: 30px;
  margin-bottom: 30px;
}

.history-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.history-table th {
  background: #f5f5f5;
  padding: 12px;
  text-align: left;
  font-weight: 600;
  color: #666;
  border-bottom: 2px solid #e0e0e0;
}

.history-table td {
  padding: 12px;
  border-bottom: 1px solid #e0e0e0;
}

.history-table tr:hover {
  background: #f9f9f9;
}

.no-history {
  text-align: center;
  padding: 40px 20px;
  color: #999;
}

.status-active {
  background: #d4edda;
  color: #155724;
}

.status-expired {
  background: #f8d7da;
  color: #721c24;
}

.status-cancelled {
  background: #e2e3e5;
  color: #383d41;
}

/* Alerts */
.alert {
  padding: 15px 20px;
  border-radius: 5px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.alert-error {
  background: #ffebee;
  color: #c62828;
  border: 1px solid #ef5350;
}

.alert-success {
  background: #e8f5e9;
  color: #2e7d32;
  border: 1px solid #66bb6a;
}

/* Utility */
.renew-section {
  margin-top: 30px;
  padding-top: 30px;
  border-top: 2px solid #e0e0e0;
}

.plans-section {
  margin-bottom: 40px;
}

.plans-title {
  font-size: 32px;
  font-weight: bold;
  margin-bottom: 10px;
  color: #333;
}

.plans-description {
  font-size: 16px;
  color: #666;
  margin-bottom: 30px;
}

/* Responsive */
@media (max-width: 768px) {
  .menu-tabs {
    flex-wrap: wrap;
  }

  .plans-grid {
    grid-template-columns: 1fr;
  }

  .status-actions {
    grid-template-columns: 1fr;
  }

  .plans-title {
    font-size: 24px;
  }

  .status-item {
    flex-direction: column;
  }

  .status-item span {
    margin-top: 5px;
  }

  .history-table {
    font-size: 14px;
  }

  .history-table th,
  .history-table td {
    padding: 8px;
  }

  .card-header {
    flex-direction: column;
    gap: 10px;
  }
}
</style>
