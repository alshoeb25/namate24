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
          <router-link to="/tutor/profile/" 
                       class="text-gray-700 hover:text-pink-600 font-medium transition"
                       active-class="text-pink-600 font-semibold">
            <i class="fas fa-home mr-2"></i>Dashboard
          </router-link>
          
          <div class="relative group">
            <button class="text-gray-700 hover:text-pink-600 font-medium transition flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-pink-50">
              <i class="fas fa-user-edit"></i>
              Edit Profile
              <i class="fas fa-chevron-down text-xs"></i>
            </button>
            
            <!-- Dropdown -->
            <div class="absolute left-0 mt-2 w-64 bg-white shadow-xl rounded-xl overflow-hidden border border-gray-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
              <router-link to="/tutor/profile/personal-details" class="px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors flex items-center gap-3">
                <i class="fas fa-user text-sm w-5"></i>
                Personal Details
              </router-link>
              <router-link to="/tutor/profile/photo" class="px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors flex items-center gap-3">
                <i class="fas fa-camera text-sm w-5"></i>
                Photo
              </router-link>
              <router-link to="/tutor/profile/video" class="px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors flex items-center gap-3">
                <i class="fas fa-video text-sm w-5"></i>
                Introduction Video
              </router-link>
              <router-link to="/tutor/profile/subjects" class="px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors flex items-center gap-3">
                <i class="fas fa-book text-sm w-5"></i>
                Subjects
              </router-link>
              <router-link to="/tutor/profile/address" class="px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors flex items-center gap-3">
                <i class="fas fa-map-marker-alt text-sm w-5"></i>
                Address
              </router-link>
              <router-link to="/tutor/profile/education" class="px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors flex items-center gap-3">
                <i class="fas fa-graduation-cap text-sm w-5"></i>
                Education
              </router-link>
              <router-link to="/tutor/profile/experience" class="px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors flex items-center gap-3">
                <i class="fas fa-briefcase text-sm w-5"></i>
                Experience
              </router-link>
              <router-link to="/tutor/profile/teaching-details" class="px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors flex items-center gap-3">
                <i class="fas fa-chalkboard-teacher text-sm w-5"></i>
                Teaching Details
              </router-link>
              <router-link to="/tutor/profile/description" class="px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors flex items-center gap-3">
                <i class="fas fa-file-alt text-sm w-5"></i>
                Profile Description
              </router-link>
              <router-link to="/tutor/profile/phone" class="px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors flex items-center gap-3">
                <i class="fas fa-phone text-sm w-5"></i>
                Phone
              </router-link>
              <router-link to="/tutor/profile/courses" class="px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors flex items-center gap-3">
                <i class="fas fa-certificate text-sm w-5"></i>
                Courses
              </router-link>
              <router-link to="/tutor/profile/settings" class="px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors flex items-center gap-3">
                <i class="fas fa-cog text-sm w-5"></i>
                Settings
              </router-link>
            </div>
          </div>

          <div class="relative group">
            <button class="text-gray-700 hover:text-pink-600 font-medium transition flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-pink-50">
              <i class="fas fa-wallet"></i>
              Wallet
              <i class="fas fa-chevron-down text-xs"></i>
            </button>
            
            <!-- Wallet Dropdown -->
            <div class="absolute left-0 mt-2 w-56 bg-white shadow-xl rounded-xl overflow-hidden border border-gray-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
              <router-link to="/tutor/wallet" class="px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors flex items-center gap-3">
                <i class="fas fa-wallet text-sm w-5"></i>
                My Wallet
              </router-link>
              <router-link to="/tutor/wallet/payment-history" class="px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors flex items-center gap-3">
                <i class="fas fa-history text-sm w-5"></i>
                Payment History
              </router-link>
              <router-link to="/tutor/wallet/coin-transactions" class="px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors flex items-center gap-3">
                <i class="fas fa-coins text-sm w-5"></i>
                Coin Transactions
              </router-link>
              <router-link to="/tutor/wallet/referrals" class="px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors flex items-center gap-3">
                <i class="fas fa-user-friends text-sm w-5"></i>
                Refer Friends
              </router-link>
            </div>
          </div>

          <router-link to="/tutor/profile/view" 
                       class="text-gray-700 hover:text-pink-600 font-medium transition"
                       active-class="text-pink-600 font-semibold">
            <i class="fas fa-eye mr-2"></i>View Profile
          </router-link>

          <router-link to="/tutor-jobs"
                       class="text-gray-700 hover:text-pink-600 font-medium transition"
                       active-class="text-pink-600 font-semibold">
            <i class="fas fa-briefcase mr-2"></i>Student Leads
          </router-link>

          <router-link to="/tutor/profile/my-learners"
                       class="text-gray-700 hover:text-pink-600 font-medium transition"
                       active-class="text-pink-600 font-semibold">
            <i class="fas fa-user-graduate mr-2"></i>My Learners
          </router-link>
        </nav>
      </div>

      <!-- Mobile Menu (shown when hamburger clicked) -->
      <div v-if="mobileMenuOpen" class="md:hidden bg-white border-t">
        <ul class="flex flex-col p-4 space-y-3">
          <li>
            <router-link to="/tutor/profile/" @click="closeMobileMenu" 
                         class="block px-4 py-2 text-gray-700 font-medium hover:bg-gray-50 rounded-md">
              <i class="fas fa-home mr-2"></i>Dashboard
            </router-link>
          </li>
          
          <li>
            <details class="pl-2">
              <summary class="font-medium text-gray-700 cursor-pointer"><i class="fas fa-user-edit mr-2"></i>Edit Profile</summary>
              <ul class="mt-2 pl-4 space-y-2">
                <li><router-link to="/tutor/profile/personal-details" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600"><i class="fas fa-user text-xs w-5 mr-2"></i>Personal Details</router-link></li>
                <li><router-link to="/tutor/profile/photo" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600"><i class="fas fa-camera text-xs w-5 mr-2"></i>Photo</router-link></li>
                <li><router-link to="/tutor/profile/video" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600"><i class="fas fa-video text-xs w-5 mr-2"></i>Introduction Video</router-link></li>
                <li><router-link to="/tutor/profile/subjects" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600"><i class="fas fa-book text-xs w-5 mr-2"></i>Subjects</router-link></li>
                <li><router-link to="/tutor/profile/address" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600"><i class="fas fa-map-marker-alt text-xs w-5 mr-2"></i>Address</router-link></li>
                <li><router-link to="/tutor/profile/education" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600"><i class="fas fa-graduation-cap text-xs w-5 mr-2"></i>Education</router-link></li>
                <li><router-link to="/tutor/profile/experience" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600"><i class="fas fa-briefcase text-xs w-5 mr-2"></i>Experience</router-link></li>
                <li><router-link to="/tutor/profile/teaching-details" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600"><i class="fas fa-chalkboard-teacher text-xs w-5 mr-2"></i>Teaching Details</router-link></li>
                <li><router-link to="/tutor/profile/description" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600"><i class="fas fa-file-alt text-xs w-5 mr-2"></i>Profile Description</router-link></li>
                <li><router-link to="/tutor/profile/phone" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600"><i class="fas fa-phone text-xs w-5 mr-2"></i>Phone</router-link></li>
                <li><router-link to="/tutor/profile/courses" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600"><i class="fas fa-certificate text-xs w-5 mr-2"></i>Courses</router-link></li>
                <li><router-link to="/tutor/profile/settings" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600"><i class="fas fa-cog text-xs w-5 mr-2"></i>Settings</router-link></li>
              </ul>
            </details>
          </li>

          <li>
            <details class="pl-2">
              <summary class="font-medium text-gray-700 cursor-pointer"><i class="fas fa-wallet mr-2"></i>Wallet</summary>
              <ul class="mt-2 pl-4 space-y-2">
                <li><router-link to="/tutor/wallet" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600"><i class="fas fa-wallet text-xs w-5 mr-2"></i>My Wallet</router-link></li>
                <li><router-link to="/tutor/wallet/payment-history" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600"><i class="fas fa-history text-xs w-5 mr-2"></i>Payment History</router-link></li>
                <li><router-link to="/tutor/wallet/coin-transactions" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600"><i class="fas fa-coins text-xs w-5 mr-2"></i>Coin Transactions</router-link></li>
                <li><router-link to="/tutor/wallet/referrals" @click="closeMobileMenu" class="block py-1 text-sm text-gray-600 hover:text-pink-600"><i class="fas fa-user-friends text-xs w-5 mr-2"></i>Refer Friends</router-link></li>
              </ul>
            </details>
          </li>

          <li>
            <router-link to="/tutor/profile/view" @click="closeMobileMenu" 
                         class="block px-4 py-2 text-gray-700 font-medium hover:bg-gray-50 rounded-md">
              <i class="fas fa-eye mr-2"></i>View Profile
            </router-link>
          </li>

          <li>
            <router-link to="/tutor-jobs" @click="closeMobileMenu"
                         class="block px-4 py-2 text-gray-700 font-medium hover:bg-gray-50 rounded-md">
              <i class="fas fa-briefcase mr-2"></i>Student Leads
            </router-link>
          </li>

          <li>
            <router-link to="/tutor/profile/my-learners" @click="closeMobileMenu"
                         class="block px-4 py-2 text-gray-700 font-medium hover:bg-gray-50 rounded-md">
              <i class="fas fa-user-graduate mr-2"></i>My Learners
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
  name: 'TutorSecondaryMenu',
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
