<template>
  <div class="w-full bg-gray-50 border-b">
    <div class="max-w-7xl mx-auto">
      <!-- Desktop Menu -->
      <div class="hidden md:flex items-center justify-between px-4 py-3">
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-1">
          <img :src="'/storage/logo.png'" 
            alt="Namate 24 Logo" 
            class="h-10 sm:h-11 md:h-12 lg:h-14 w-auto object-contain">
        </router-link>

        <!-- Menu Items -->
        <nav class="flex items-center lg:gap-6">
          <router-link to="/student/dashboard" 
                       class="text-gray-700 hover:text-blue-600 font-medium transition"
                       active-class="text-blue-600 font-semibold">
            <i class="fas fa-home mr-2"></i>Dashboard
          </router-link>
          
          <!-- Posts Dropdown -->
          <div class="relative group">
            <button class="text-gray-700 hover:text-blue-600 font-medium transition flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-blue-50">
              <i class="fas fa-list-alt"></i>
              Posts
              <i class="fas fa-chevron-down text-xs"></i>
            </button>
            
            <div class="absolute left-0 mt-2 w-56 bg-white shadow-xl rounded-xl overflow-hidden border border-gray-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
              <router-link to="/student/requirements" class="px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center gap-3">
                <i class="fas fa-clipboard-list text-sm w-5"></i>
                Requirement List
              </router-link>
              <router-link to="/student/request-tutor" class="px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center gap-3">
                <i class="fas fa-user-plus text-sm w-5"></i>
                Request Tutor
              </router-link>
            </div>
          </div>

          <router-link to="/search" 
                       class="text-gray-700 hover:text-blue-600 font-medium transition"
                       active-class="text-blue-600 font-semibold">
            <i class="fas fa-search mr-2"></i>Find Tutor
          </router-link>

          <div class="relative group">
            <button class="text-gray-700 hover:text-blue-600 font-medium transition flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-blue-50">
              <i class="fas fa-wallet"></i>
              Wallet
              <i class="fas fa-chevron-down text-xs"></i>
            </button>
            
            <!-- Wallet Dropdown -->
            <div class="absolute left-0 mt-2 w-56 bg-white shadow-xl rounded-xl overflow-hidden border border-gray-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
              <router-link to="/student/wallet" class="px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center gap-3">
                <i class="fas fa-wallet text-sm w-5"></i>
                My Wallet
              </router-link>
              <router-link to="/student/wallet/payment-history" class="px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center gap-3">
                <i class="fas fa-history text-sm w-5"></i>
                Payment History
              </router-link>
              <router-link to="/student/wallet/coin-transactions" class="px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center gap-3">
                <i class="fas fa-coins text-sm w-5"></i>
                Coin Transactions
              </router-link>
              <router-link to="/student/wallet/referrals" class="px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center gap-3">
                <i class="fas fa-user-friends text-sm w-5"></i>
                Refer Friends
              </router-link>
            </div>
          </div>

          <router-link to="/student/reviews" 
                       class="text-gray-700 hover:text-blue-600 font-medium transition"
                       active-class="text-blue-600 font-semibold">
            <i class="fas fa-star mr-2"></i>Reviews
          </router-link>
        </nav>
      </div>

      <!-- Mobile Menu (shown when hamburger clicked) -->
      <div v-if="mobileMenuOpen" class="md:hidden bg-white border-t">
        <ul class="flex flex-col p-4 space-y-3">
          <li>
            <router-link to="/student/dashboard" @click="closeMobileMenu" 
                         class="block px-4 py-2 text-gray-700 font-medium hover:bg-gray-50 rounded-md">
              <i class="fas fa-home mr-2"></i>Dashboard
            </router-link>
          </li>
          
          <li>
            <details class="pl-2">
              <summary class="font-medium text-gray-700 cursor-pointer"><i class="fas fa-list-alt mr-2"></i>Posts</summary>
              <ul class="mt-2 pl-4 space-y-2">
                <li><router-link to="/student/requirements" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-blue-600"><i class="fas fa-clipboard-list text-xs w-5 mr-2"></i>Requirement List</router-link></li>
                <li><router-link to="/student/request-tutor" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-blue-600"><i class="fas fa-user-plus text-xs w-5 mr-2"></i>Request Tutor</router-link></li>
              </ul>
            </details>
          </li>

          <li>
            <router-link to="/search" @click="closeMobileMenu" 
                         class="block px-4 py-2 text-gray-700 font-medium hover:bg-gray-50 rounded-md">
              <i class="fas fa-search mr-2"></i>Find Tutor
            </router-link>
          </li>

          <li>
            <details class="pl-2">
              <summary class="font-medium text-gray-700 cursor-pointer"><i class="fas fa-wallet mr-2"></i>Wallet</summary>
              <ul class="mt-2 pl-4 space-y-2">
                <li><router-link to="/student/wallet" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-blue-600"><i class="fas fa-wallet text-xs w-5 mr-2"></i>My Wallet</router-link></li>
                <li><router-link to="/student/wallet/payment-history" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-blue-600"><i class="fas fa-history text-xs w-5 mr-2"></i>Payment History</router-link></li>
                <li><router-link to="/student/wallet/coin-transactions" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-blue-600"><i class="fas fa-coins text-xs w-5 mr-2"></i>Coin Transactions</router-link></li>
                <li><router-link to="/student/wallet/referrals" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-blue-600"><i class="fas fa-user-friends text-xs w-5 mr-2"></i>Refer Friends</router-link></li>
              </ul>
            </details>
          </li>

          <li>
            <router-link to="/student/reviews" @click="closeMobileMenu" 
                         class="block px-4 py-2 text-gray-700 font-medium hover:bg-gray-50 rounded-md">
              <i class="fas fa-star mr-2"></i>Reviews
            </router-link>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';

export default {
  name: 'StudentSecondaryMenu',
  setup() {
    const mobileMenuOpen = ref(false);

    const handleToggleMenu = () => {
      mobileMenuOpen.value = !mobileMenuOpen.value;
    };

    const closeMobileMenu = () => {
      mobileMenuOpen.value = false;
    };

    // Listen for toggle event from AuthTopBar
    onMounted(() => {
      window.addEventListener('toggle-mobile-menu', handleToggleMenu);
    });

    onUnmounted(() => {
      window.removeEventListener('toggle-mobile-menu', handleToggleMenu);
    });

    return { mobileMenuOpen, closeMobileMenu };
  }
};
</script>
