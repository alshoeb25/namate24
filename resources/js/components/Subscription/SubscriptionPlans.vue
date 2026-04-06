<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Tab Navigation -->
    <div class="bg-white rounded-2xl shadow-md mb-6 overflow-hidden">
      <div class="flex border-b border-gray-200 overflow-x-auto">
        <button
          v-if="hasActiveSubscription || hasLapsedSubscription"
          class="flex-1 min-w-[180px] px-4 md:px-6 py-3 md:py-4 font-semibold transition-all whitespace-nowrap"
          :class="[
            activeTab === 'current'
              ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white'
              : 'text-gray-600 hover:bg-gray-50'
          ]"
          @click="activeTab = 'current'"
        >
          <i class="fas fa-check mr-2"></i>{{ hasActiveSubscription ? 'Current Plan' : 'Lapsed' }}
        </button>
        <button
          class="flex-1 min-w-[160px] px-4 md:px-6 py-3 md:py-4 font-semibold transition-all whitespace-nowrap"
          :class="[
            activeTab === 'browse'
              ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white'
              : 'text-gray-600 hover:bg-gray-50'
          ]"
          @click="activeTab = 'browse'"
        >
          <i class="fas fa-list-alt mr-2"></i>Browse Plans
        </button>
        <button
          class="flex-1 min-w-[160px] px-4 md:px-6 py-3 md:py-4 font-semibold transition-all whitespace-nowrap"
          :class="[
            activeTab === 'history'
              ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white'
              : 'text-gray-600 hover:bg-gray-50'
          ]"
          @click="activeTab = 'history'"
        >
          <i class="fas fa-history mr-2"></i>History
        </button>
      </div>
    </div>

    <!-- Lapsed Subscription Alert -->
    <div v-if="hasLapsedSubscription && !hasActiveSubscription" class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-6">
      <div class="flex gap-4">
        <div class="flex-shrink-0">
          <i class="fas fa-exclamation-circle text-yellow-600 text-2xl mt-1"></i>
        </div>
        <div class="flex-1">
          <h3 class="text-lg font-bold text-yellow-800 mb-2">Subscription Expired</h3>
          <p class="text-yellow-700 mb-4">
            Your {{ subscription.plan_name }} subscription expired {{ subscription.days_since_expiry }} day(s) ago.
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <div class="bg-white rounded-lg p-3">
              <div class="text-xs text-gray-600 uppercase font-semibold">Remaining Coins</div>
              <div class="text-2xl font-bold text-green-600">{{ subscription.remaining_coins }}</div>
              <div class="text-xs text-gray-500 mt-1">{{ subscription.coins_display }}</div>
            </div>
            <div class="bg-white rounded-lg p-3">
              <div class="text-xs text-gray-600 uppercase font-semibold">View Delay</div>
              <div class="text-2xl font-bold text-orange-600">{{ subscription.delay_hours }}h</div>
              <div class="text-xs text-gray-500 mt-1">After expiry</div>
            </div>
            <div class="bg-white rounded-lg p-3">
              <div class="text-xs text-gray-600 uppercase font-semibold">Cost per View</div>
              <div class="text-2xl font-bold text-blue-600">{{ subscription.view_cost_with_coins }}</div>
              <div class="text-xs text-gray-500 mt-1">coins required</div>
            </div>
          </div>
          <div class="bg-white rounded-lg p-4 mb-4 border border-yellow-200">
            <p class="text-sm text-gray-700">
              <i class="fas fa-info-circle text-blue-500 mr-2"></i>
              {{ subscription.delay_message }}
            </p>
          </div>
          <div class="flex gap-3">
            <button 
              v-if="subscription.can_view_with_coins"
              class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition"
              @click="activeTab = 'browse'"
            >
              <i class="fas fa-eye mr-2"></i>Use Coins to Browse
            </button>
            <button 
              class="flex-1 px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold rounded-lg transition"
              @click="activeTab = 'browse'; scrollToPlans = true"
            >
              <i class="fas fa-refresh mr-2"></i>Re-Subscribe Now
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Current Subscription Tab -->
    <div v-if="(hasActiveSubscription || hasLapsedSubscription) && activeTab === 'current'">
      <!-- Current Plan Card -->
      <div class="bg-white rounded-2xl shadow-md p-6 md:p-8 mb-6">
        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-6">
          <div>
            <h2 class="text-2xl font-bold text-gray-800">
              <i class="fas fa-check-circle text-green-600 mr-2"></i>Your Current Subscription
            </h2>
          </div>
          <span class="inline-block px-4 py-1 rounded-full bg-green-100 text-green-700 font-semibold text-sm">
            <i class="fas fa-check mr-1"></i>Active
          </span>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <!-- Left Column -->
          <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Plan Information</h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                <span class="text-gray-600">Plan Name:</span>
                <span class="font-semibold text-gray-800">{{ subscription.plan_name }}</span>
              </div>
              <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                <span class="text-gray-600">Plan Price:</span>
                <span class="font-semibold text-blue-600">
                  <span v-if="isIndiaUser">₹{{ subscription.base_price }}</span>
                  <span v-else>${{ subscription.display_price }}</span>
                </span>
              </div>
              <div v-if="isIndiaUser && subscription.gst_amount > 0" class="flex justify-between items-center pb-2 border-b border-gray-100">
                <span class="text-gray-600">GST (18%):</span>
                <span class="text-gray-700">₹{{ (subscription.gst_amount).toFixed(2) }}</span>
              </div>
              <div class="flex justify-between items-center pt-2 bg-blue-50 px-3 py-2 rounded-lg">
                <span class="font-semibold text-gray-700">Total Amount:</span>
                <span class="font-bold text-blue-600 text-lg">
                  <span v-if="isIndiaUser">₹{{ Number(subscription.price).toFixed(2) }}</span>
                  <span v-else>${{ Number(subscription.price).toFixed(2) }}</span>
                </span>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Access & Validity</h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                <span class="text-gray-600">Views Allowed:</span>
                <span class="font-semibold" :class="subscription.unlimited_views ? 'text-green-600' : 'text-gray-800'">
                  {{ subscription.unlimited_views ? 'Unlimited' : subscription.views_allowed + ' views' }}
                </span>
              </div>
              <div v-if="!subscription.unlimited_views" class="pb-2 border-b border-gray-100">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-gray-600">Views Used:</span>
                  <span class="text-gray-700">{{ subscription.views_used }} / {{ subscription.views_allowed }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div class="bg-blue-500 h-2 rounded-full transition-all" :style="{ width: (subscription.views_used / subscription.views_allowed * 100) + '%' }"></div>
                </div>
              </div>
              <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                <span class="text-gray-600">Remaining:</span>
                <span class="font-semibold text-orange-600">{{ subscription.remaining_days }} days</span>
              </div>
              <div class="flex justify-between items-center pt-2 bg-yellow-50 px-3 py-2 rounded-lg" v-if="subscription.remaining_days <= 7">
                <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                <span class="text-yellow-700 text-sm font-medium">Renew soon!</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Plan Details Section -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-200 mb-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Plan Details</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-start gap-3">
              <i class="fas fa-coins text-yellow-500 mt-0.5 font-bold"></i>
              <div class="flex-1">
                <span class="text-gray-700 font-semibold">{{ subscription.coins_included_text }}</span>
                <p class="text-xs text-gray-500">{{ subscription.cost_per_view_text }}</p>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <i class="fas fa-bolt text-green-500 mt-0.5 font-bold"></i>
              <span class="text-gray-700">{{ subscription.access_delay_text }}</span>
            </div>
            <div class="flex items-start gap-3">
              <i class="fas fa-redo text-indigo-500 mt-0.5 font-bold"></i>
              <span class="text-gray-700 font-semibold" :class="subscription.coins_carry_forward ? 'text-green-600' : 'text-orange-600'">
                {{ subscription.coins_carry_forward_text }}
              </span>
            </div>
            <div class="flex items-start gap-3">
              <i class="fas fa-headset text-blue-500 mt-0.5 font-bold"></i>
              <div class="flex-1">
                <p class="text-gray-700"><strong>Priority Support:</strong></p>
                <p class="text-xs text-gray-600">{{ subscription.priority_support || 'No' }}</p>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <i class="fas fa-users text-purple-500 mt-0.5 font-bold"></i>
              <div class="flex-1">
                <p class="text-gray-700"><strong>Backend Team Support:</strong></p>
                <p class="text-xs text-gray-600">{{ subscription.backend_team_support || 'No' }}</p>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <i class="fas fa-book text-orange-500 mt-0.5 font-bold"></i>
              <div class="flex-1">
                <p class="text-gray-700"><strong>eBooks & Content:</strong></p>
                <p class="text-xs text-gray-600">{{ subscription.ebooks_content || 'No' }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
          <button class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-indigo-700 transition shadow-md"
            @click="getRenewOptions">
            <i class="fas fa-refresh mr-2"></i>Renew Subscription
          </button>
          <button class="flex-1 px-6 py-3 border-2 border-red-500 text-red-600 font-semibold rounded-lg hover:bg-red-50 transition"
            @click="cancelSubscription" :disabled="cancelling">
            <i class="fas fa-times mr-2"></i>{{ cancelling ? 'Cancelling...' : 'Cancel' }}
          </button>
        </div>
      </div>

      <!-- Renew Options -->
      <div v-if="showRenewOptions" class="bg-white rounded-2xl shadow-md p-6 md:p-8 mb-6">
        <h3 class="text-2xl font-bold text-gray-800 mb-2">Renew Your Subscription</h3>
        <p class="text-gray-600 mb-6">Select a plan to extend your access</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
            <div class="mb-3 space-y-1 text-sm">
              <p class="text-gray-700"><strong>{{ plan.coins_included_text }}</strong></p>
              <p class="text-gray-600">{{ plan.cost_per_view_text }}</p>
              <p class="text-gray-600">{{ plan.views_text }}</p>
              <p class="text-gray-600">{{ plan.access_delay_text }}</p>
              <p class="text-gray-600 font-semibold" :class="plan.coins_carry_forward ? 'text-green-600' : 'text-orange-600'">{{ plan.coins_carry_forward_text }}</p>
              <p class="text-gray-600 mt-2 pt-2 border-t border-gray-200">
                <i class="fas fa-headset mr-1" :class="plan.has_priority_support ? 'text-green-600' : 'text-gray-400'"></i>
                <strong>Priority Support:</strong> {{ plan.priority_support }}
              </p>
              <p class="text-gray-600">
                <i class="fas fa-users mr-1" :class="plan.has_backend_team_support ? 'text-green-600' : 'text-gray-400'"></i>
                <strong>Backend Team:</strong> {{ plan.backend_team_support }}
              </p>
              <p class="text-gray-600">
                <i class="fas fa-book mr-1" :class="plan.has_ebook_content ? 'text-green-600' : 'text-gray-400'"></i>
                <strong>eBooks:</strong> {{ plan.ebooks_content }}
              </p>
            </div>
            <p class="views-text">Valid for {{ plan.validity_text }}</p>
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

      <!-- Region Toggle (Hidden - Auto-detect based on user location) -->
      <!-- <div class="region-toggle">
        <button
          class="region-btn"
          :class="{ active: selectedRegion === 'india' }"
          @click="selectedRegion = 'india'"
        >
          🇮🇳 India (INR)
        </button>
        <button
          class="region-btn"
          :class="{ active: selectedRegion === 'international' }"
          @click="selectedRegion = 'international'"
        >
          🌍 Foreign National (USD)
        </button>
      </div> -->

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="plan in plans"
          :key="plan.id"
          class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-lg transition-shadow border-2 duration-300"
          :class="plan.id === 1 ? 'border-blue-500 md:col-span-2 lg:col-span-1' : 'border-gray-200'"
        >
          <!-- Plan Header -->
          <div class="p-6 bg-gradient-to-r" :class="plan.id === 1 ? 'from-blue-500 to-indigo-600' : 'from-gray-50 to-gray-100'">
            <div class="flex justify-between items-start gap-4">
              <div>
                <h3 class="text-xl font-bold" :class="plan.id === 1 ? 'text-white' : 'text-gray-800'">
                  {{ plan.name }}
                </h3>
              </div>
              <span v-if="plan.id === 1" class="px-3 py-1 rounded-full transition text-white font-semibold text-sm bg-blue-400 whitespace-nowrap">
                <i class="fas fa-star mr-1"></i>Most Popular
              </span>
            </div>
          </div>

          <!-- Plan Pricing -->
          <div class="p-6 border-b border-gray-100">
            <!-- India Pricing -->
            <template v-if="selectedRegion === 'india'">
              <div class="mb-4">
                <div class="flex items-baseline gap-1">
                  <span class="text-gray-600 text-sm">₹</span>
                  <span class="text-4xl font-bold text-blue-600">{{ plan.base_price }}</span>
                  <span class="text-gray-600 text-sm">/month</span>
                </div>
                <div v-if="plan.gst_amount > 0" class="mt-3 text-sm text-gray-600 space-y-1">
                  <div class="flex justify-between">
                    <span>GST (18%):</span>
                    <span>₹{{ (plan.gst_amount).toFixed(2) }}</span>
                  </div>
                  <div class="flex justify-between font-semibold text-blue-600 pt-1 border-t border-gray-100">
                    <span>Total:</span>
                    <span>₹{{ Number(plan.price).toFixed(2) }}</span>
                  </div>
                </div>
              </div>
            </template>

            <!-- International / Foreign National Pricing -->
            <template v-else>
              <div class="mb-4">
                <div class="flex items-baseline gap-1">
                  <span class="text-gray-600 text-sm">$</span>
                  <span class="text-4xl font-bold text-blue-600">{{ foreignPrice(plan) }}</span>
                  <span class="text-gray-600 text-sm">/month</span>
                </div>
                <div class="mt-3 text-xs text-gray-500 italic">
                  No tax applied · Billed in USD
                </div>
              </div>
            </template>
          </div>

          <!-- Plan Features -->
          <div class="p-6 border-b border-gray-100">
            <div class="space-y-3">
              <div class="flex items-start gap-3">
                <i class="fas fa-coins text-yellow-500 mt-0.5 font-bold"></i>
                <div class="flex-1">
                  <span class="text-gray-700 font-semibold">{{ plan.coins_included_text }}</span>
                  <p class="text-xs text-gray-500">{{ plan.cost_per_view_text }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <i class="fas fa-eye text-blue-500 mt-0.5 font-bold"></i>
                <span class="text-gray-700">{{ plan.views_text }}</span>
              </div>
              <div class="flex items-start gap-3">
                <i class="fas fa-calendar text-purple-500 mt-0.5 font-bold"></i>
                <span class="text-gray-700">Valid for {{ plan.validity_text }}</span>
              </div>
              <div class="flex items-start gap-3">
                <i class="fas fa-bolt text-green-500 mt-0.5 font-bold"></i>
                <span class="text-gray-700">{{ plan.access_delay_text }}</span>
              </div>
              <div class="flex items-start gap-3">
                <i class="fas fa-redo text-indigo-500 mt-0.5 font-bold"></i>
                <span class="text-gray-700">{{ plan.coins_carry_forward_text }}</span>
              </div>
              <div class="border-t border-gray-200 pt-3 mt-3 space-y-2">
                <div class="flex items-start gap-3">
                  <i class="fas fa-headset mt-0.5 font-bold" :class="plan.has_priority_support ? 'text-green-600' : 'text-gray-400'"></i>
                  <div class="flex-1">
                    <p class="text-gray-700"><strong>Priority Support:</strong></p>
                    <p :class="plan.has_priority_support ? 'text-green-600 font-semibold' : 'text-gray-500'">{{ plan.priority_support }}</p>
                  </div>
                </div>
                <div class="flex items-start gap-3">
                  <i class="fas fa-users mt-0.5 font-bold" :class="plan.has_backend_team_support ? 'text-green-600' : 'text-gray-400'"></i>
                  <div class="flex-1">
                    <p class="text-gray-700"><strong>Backend Team Support:</strong></p>
                    <p :class="plan.has_backend_team_support ? 'text-green-600 font-semibold' : 'text-gray-500'">{{ plan.backend_team_support }}</p>
                  </div>
                </div>
                <div class="flex items-start gap-3">
                  <i class="fas fa-book mt-0.5 font-bold" :class="plan.has_ebook_content ? 'text-green-600' : 'text-gray-400'"></i>
                  <div class="flex-1">
                    <p class="text-gray-700"><strong>eBooks & Content:</strong></p>
                    <p :class="plan.has_ebook_content ? 'text-green-600 font-semibold' : 'text-gray-500'">{{ plan.ebooks_content }}</p>
                  </div>
                </div>
              </div>
              <div v-if="plan.description" class="text-xs text-gray-500 italic pt-2 border-t border-gray-100">
                {{ plan.description }}
              </div>
            </div>
          </div>

          <!-- Subscribe Button -->
          <div class="p-6">
            <button
              class="w-full py-3 px-4 font-semibold rounded-lg transition-all"
              :class="plan.id === 1 
                ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white hover:from-blue-600 hover:to-indigo-700 disabled:opacity-60'
                : 'border-2 border-gray-300 text-gray-700 hover:border-gray-400 hover:bg-gray-50 disabled:opacity-60'"
              @click="selectPlan(plan)"
              :disabled="loading"
            >
              {{ loading ? 'Processing...' : 'Subscribe Now' }}
            </button>
          </div>
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
            <label>Plan Price:</label>
            <span>
              <span v-if="isIndiaUser">₹{{ subscription.base_price }}</span>
              <span v-else>${{ subscription.display_price }}</span>
            </span>
          </div>
          <div v-if="isIndiaUser && subscription.gst_amount > 0" class="status-item">
            <label>GST (18%):</label>
            <span>₹{{ (subscription.gst_amount).toFixed(2) }}</span>
          </div>
          <div class="status-item">
            <label>Total Amount:</label>
            <span class="highlight">
              <span v-if="isIndiaUser">₹{{ Number(subscription.price).toFixed(2) }}</span>
              <span v-else>${{ Number(subscription.price).toFixed(2) }}</span>
            </span>
          </div>
          <div class="status-item">
            <label>Expired On:</label>
            <span>{{ formatDate(subscription.expires_at) }} <small>(+{{ subscription.validity_days }} days)</small></span>
          </div>
          <div class="status-item">
            <label>Remaining:</label>
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
    <div v-if="showPurchaseFlow" class="bg-gray-50 py-4">
      <button 
        class="mb-4 text-blue-600 hover:text-blue-700 font-medium flex items-center gap-2 transition-colors"
        @click="cancelPurchase"
      >
        <i class="fas fa-arrow-left"></i>Back to Plans
      </button>

      <div class="bg-white rounded-2xl shadow-md p-6 md:p-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Complete Your Purchase</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Order Summary -->
          <div class="md:col-span-2">
            <div class="bg-gray-50 rounded-xl p-5 mb-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Summary</h3>
              <div class="space-y-3">
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                  <span class="text-gray-600 text-sm">Plan:</span>
                  <strong class="text-gray-800">{{ selectedPlan.name }}</strong>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                  <span class="text-gray-600 text-sm">Validity:</span>
                  <strong class="text-gray-800">{{ selectedPlan.validity_text }}</strong>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                  <span class="text-gray-600 text-sm">Views:</span>
                  <strong class="text-gray-800">{{ selectedPlan.views_text }}</strong>
                </div>
                <div v-if="isIndiaUser && selectedPlan.gst_amount > 0" class="flex justify-between items-center pb-3 border-b border-gray-200">
                  <span class="text-gray-600 text-sm">Base Price:</span>
                  <strong class="text-gray-800">₹{{ (selectedPlan.price - selectedPlan.gst_amount).toFixed(2) }}</strong>
                </div>
                <div v-if="isIndiaUser && selectedPlan.gst_amount > 0" class="flex justify-between items-center pb-3 border-b border-gray-200">
                  <span class="text-gray-600 text-sm">GST (18%):</span>
                  <strong class="text-gray-800">₹{{ (selectedPlan.gst_amount).toFixed(2) }}</strong>
                </div>
              </div>
            </div>

            <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-5">
              <h3 class="text-lg font-semibold text-blue-900 mb-3">
                <i class="fas fa-credit-card mr-2"></i>Payment Method
              </h3>
              <p class="text-blue-800 mb-4 text-sm">
                <i class="fas fa-info-circle mr-2"></i> You will be redirected to Razorpay to complete the payment securely.
              </p>
              <button
                class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-indigo-700 transition shadow-md disabled:opacity-60 text-sm"
                @click="initiatePayment"
                :disabled="loading"
              >
                <i class="fas fa-lock mr-2"></i>{{ loading ? 'Processing...' : 'Proceed to Payment' }}
              </button>
            </div>
          </div>

          <!-- Price Summary Side Panel -->
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-5 h-fit border border-blue-200">
            <h3 class="text-lg font-bold text-gray-800 mb-3">Total Amount</h3>
            <div class="text-4xl font-bold text-blue-600 mb-2">
              <span v-if="isIndiaUser">₹{{ selectedPlan.price.toFixed(2) }}</span>
              <span v-else>${{ selectedPlan.price.toFixed(2) }}</span>
            </div>
            <p class="text-gray-600 text-xs mb-3">
              <span v-if="isIndiaUser">Including 18% GST</span>
              <span v-else>No additional taxes</span>
            </p>
            <div class="text-center text-gray-500 text-xs pt-3 border-t border-blue-200">
              <i class="fas fa-lock mr-1"></i> Secure payment encrypted
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Subscription History -->
    <div v-if="activeTab === 'history'" class="bg-white rounded-2xl shadow-md p-6 md:p-8">
      <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-history text-gray-600 mr-2"></i>Subscription History
      </h2>

      <div v-if="history.length === 0" class="text-center py-12">
        <i class="fas fa-inbox text-gray-300 text-4xl mb-4 block"></i>
        <p class="text-gray-500 text-lg">No subscription history yet.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b-2 border-gray-200">
              <th class="text-left py-4 px-4 font-semibold text-gray-700">Plan</th>
              <th class="text-left py-4 px-4 font-semibold text-gray-700">Price</th>
              <th class="text-left py-4 px-4 font-semibold text-gray-700">Activated</th>
              <th class="text-left py-4 px-4 font-semibold text-gray-700">Expires</th>
              <th class="text-left py-4 px-4 font-semibold text-gray-700">Views</th>
              <th class="text-left py-4 px-4 font-semibold text-gray-700">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="sub in history" :key="sub.id" class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
              <td class="py-4 px-4 font-medium text-gray-800">{{ sub.plan_name }}</td>
              <td class="py-4 px-4 text-gray-700">
                <span v-if="sub.is_india_user" class="font-semibold">₹{{ Number(sub.price).toFixed(2) }}</span>
                <span v-else class="font-semibold">${{ Number(sub.price).toFixed(2) }}</span>
              </td>
              <td class="py-4 px-4 text-gray-700">{{ formatDate(sub.activated_at) }}</td>
              <td class="py-4 px-4 text-gray-700">{{ formatDate(sub.expires_at) }}</td>
              <td class="py-4 px-4 text-gray-700">{{ sub.views_text }}</td>
              <td class="py-4 px-4">
                <span 
                  class="px-3 py-1 rounded-full text-sm font-medium whitespace-nowrap"
                  :class="{
                    'bg-green-100 text-green-700': sub.status === 'active',
                    'bg-red-100 text-red-700': sub.status === 'expired',
                    'bg-gray-100 text-gray-700': sub.status === 'cancelled'
                  }"
                >
                  <i class="fas mr-1" :class="{
                    'fa-check-circle': sub.status === 'active',
                    'fa-times-circle': sub.status === 'expired',
                    'fa-ban': sub.status === 'cancelled'
                  }"></i>
                  {{ sub.status.charAt(0).toUpperCase() + sub.status.slice(1) }}
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
      hasLapsedSubscription: false,
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
      selectedRegion: 'india',
      razorpayReady: !!(typeof window !== 'undefined' && window.Razorpay),
      scrollToPlans: false,
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
        // Auto-set region based on user's country
        this.selectedRegion = this.isIndiaUser ? 'india' : 'international';

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
          this.hasLapsedSubscription = false;
          this.subscription = status;
          this.activeTab = 'current';
        } else if (status.has_lapsed_subscription) {
          this.hasActiveSubscription = false;
          this.hasLapsedSubscription = true;
          this.subscription = status;
          this.activeTab = 'current'; // Show lapsed status
        } else {
          this.hasActiveSubscription = false;
          this.hasLapsedSubscription = false;
          this.activeTab = 'browse';
        }
      } catch (error) {
        this.hasActiveSubscription = false;
        this.hasLapsedSubscription = false;
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
          
          setTimeout(async () => {
            this.showPurchaseFlow = false;
            this.selectedPlan = null;
            await this.loadSubscriptionStatus();
            await this.loadPlans();
            this.activeTab = 'current';
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            // Clear success message after 5 seconds
            setTimeout(() => {
              this.success = null;
            }, 5000);
          }, 3000);
          return;
        }

        // Success case
        this.success = 'Subscription activated successfully!';
        this.loading = false;

        setTimeout(async () => {
          this.showPurchaseFlow = false;
          this.selectedPlan = null;
          await this.loadSubscriptionStatus();
          await this.loadPlans();
          this.activeTab = 'current';
          
          // Scroll to top to show the updated current subscription
          window.scrollTo({ top: 0, behavior: 'smooth' });
          
          // Clear success message after 5 seconds
          setTimeout(() => {
            this.success = null;
          }, 5000);
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

    foreignPrice(plan) {
      // Match backend getUSDPriceForPlan logic
      if (plan.id === 1) return '60.00';
      if (plan.id === 2) return '15.00';
      return (plan.base_price * 0.12).toFixed(2);
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

.plan-card h4 {
  font-size: 20px;
  font-weight: bold;
  color: #333;
  margin: 0 0 15px 0;
  padding: 0;
}

.plan-card .price {
  font-size: 18px;
  color: #4CAF50;
  font-weight: 600;
  margin: 10px 0 15px 0;
  padding: 0;
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

.gst-breakdown .total-price {
  color: #4CAF50;
  font-weight: 600;
}

.text-muted {
  color: #999;
  font-size: 13px;
  font-weight: normal;
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
  margin-bottom: 20px;
}

.region-toggle {
  display: flex;
  gap: 10px;
  margin-bottom: 30px;
  background: #f5f5f5;
  padding: 6px;
  border-radius: 30px;
  width: fit-content;
}

.region-btn {
  background: transparent;
  border: none;
  padding: 10px 22px;
  border-radius: 25px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  color: #666;
  transition: all 0.25s ease;
}

.region-btn.active {
  background: white;
  color: #333;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

.foreign-note {
  font-size: 12px;
  color: #999;
  margin-top: 5px;
  margin-bottom: 2px;
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
