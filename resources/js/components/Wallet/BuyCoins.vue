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
            @click="$emit('purchase', pkg)"
            class="w-full bg-gradient-to-r from-pink-500 to-purple-600 text-white font-semibold py-3 rounded-lg hover:from-pink-600 hover:to-purple-700 transition transform hover:scale-105"
          >
            <i class="fas fa-shopping-cart mr-2"></i>Buy Now
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
    }
  },
  emits: ['purchase']
};
</script>
