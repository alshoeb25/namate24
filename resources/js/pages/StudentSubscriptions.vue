<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl shadow-lg p-6 md:p-8 mb-6 text-white">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
          <div>
            <h1 class="text-3xl font-bold mb-2">
              <i class="fas fa-list mr-2"></i>Subscription Plans
            </h1>
            <p class="text-blue-100">Browse and manage subscription plans</p>
          </div>
        </div>
      </div>

      <!-- Subscription Plans Component -->
      <SubscriptionPlans />
    </div>
  </div>
</template>

<script>
import SubscriptionPlans from '../components/Subscription/SubscriptionPlans.vue';

export default {
  name: 'StudentSubscriptions',
  components: {
    SubscriptionPlans
  }
}
</script>
