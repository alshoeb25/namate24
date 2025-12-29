<template>
  <div class="w-full bg-white border-b shadow-sm relative z-50">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-4 py-2">
      <!-- Mobile: Hamburger + Logo -->
      <div class="flex md:hidden items-center gap-2">
        <button @click="toggleMobileMenu" class="p-2 rounded-md focus:outline-none">
          <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <router-link to="/" class="flex items-center gap-1">
          <img src="https://image2url.com/images/1765179057005-967d0875-ac5d-4a43-b65f-a58abd9f651d.png" 
               alt="Logo" 
               class="w-8 h-8 object-contain">
          <span class="text-pink-600 font-bold text-sm">Namate 24</span>
        </router-link>
      </div>

      <!-- Desktop: Empty spacer -->
      <div class="hidden md:block"></div>

      <!-- Right: Notification + Avatar (Desktop & Mobile) -->
      <div class="flex items-center gap-4">
        <!-- Notification Bell -->
        <div class="notification-bell-root">
          <NotificationBell />
        </div>

        <!-- Avatar Dropdown -->
        <div class="relative">
          <button @click="profileMenuOpen = !profileMenuOpen" 
                  class="flex items-center gap-2 px-2 py-1 rounded-full hover:bg-gray-100 transition">
            <img :src="getProfilePhoto()" 
                 alt="Profile" 
                 class="w-8 h-8 md:w-10 md:h-10 rounded-full object-cover">
            <span class="hidden md:inline text-sm font-medium text-gray-700">{{ user?.name }}</span>
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Dropdown Menu -->
          <div v-if="profileMenuOpen" 
               class="absolute right-0 mt-2 w-56 bg-white shadow-lg rounded-lg overflow-hidden border z-[999]">
            
            <!-- Teacher/Expert Section -->
            <div class="border-b">
              <div class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-600 uppercase">
                <i class="fas fa-chalkboard-teacher mr-2"></i>Teacher / Expert
              </div>
              <router-link v-if="user?.tutor" 
                           to="/tutor/profile" 
                           @click="switchToTutorDashboard" 
                           class="block px-4 py-2 text-gray-700 text-sm hover:bg-pink-50 hover:text-pink-600 transition-colors">
                <i class="fas fa-user-tie mr-2"></i>Tutor Dashboard
              </router-link>
              <button v-else 
                      @click="openEnrollModal('teacher')" 
                      class="w-full text-left px-4 py-2 text-gray-700 text-sm hover:bg-pink-50 hover:text-pink-600 transition-colors">
                <i class="fas fa-user-plus mr-2"></i>Enroll as Teacher
              </button>
            </div>

            <!-- Student/Parent Section -->
            <div class="border-b">
              <div class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-600 uppercase">
                <i class="fas fa-user-graduate mr-2"></i>Student / Parent
              </div>
              <router-link v-if="user?.student" 
                           to="/student/dashboard" 
                           @click="switchToStudentDashboard" 
                           class="block px-4 py-2 text-gray-700 text-sm hover:bg-blue-50 hover:text-blue-600 transition-colors">
                <i class="fas fa-graduation-cap mr-2"></i>Student Dashboard
              </router-link>
              <button v-else 
                      @click="openEnrollModal('student')" 
                      class="w-full text-left px-4 py-2 text-gray-700 text-sm hover:bg-blue-50 hover:text-blue-600 transition-colors">
                <i class="fas fa-user-plus mr-2"></i>Enroll as Student
              </button>
            </div>

            <!-- Common Section -->
            <router-link to="/profile" @click="profileMenuOpen = false" 
                         class="block px-4 py-2 text-gray-700 text-sm hover:bg-gray-100">
              <i class="fas fa-user-circle mr-2"></i>My Profile
            </router-link>
            <button @click="logout" 
                    class="w-full text-left px-4 py-2 text-gray-700 text-sm hover:bg-gray-100">
              <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Enrollment Modal -->
    <EnrollmentModal 
      :show="showEnrollModal" 
      :type="enrollType" 
      @close="closeEnrollModal" 
    />
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '../../store';
import EnrollmentModal from '../EnrollmentModal.vue';
import NotificationBell from './NotificationBell.vue';

export default {
  name: 'AuthTopBar',
  components: {
    EnrollmentModal,
    NotificationBell
  },
  emits: ['toggle-mobile-menu'],
  setup(props, { emit }) {
    const router = useRouter();
    const userStore = useUserStore();
    
    const profileMenuOpen = ref(false);
    const showEnrollModal = ref(false);
    const enrollType = ref('');
    const user = computed(() => userStore.user);

    const toggleMobileMenu = () => {
      window.dispatchEvent(new CustomEvent('toggle-mobile-menu'));
    };

    const logout = async () => {
      try {
        await userStore.logout(); 
      } catch (e) {}
      
      if (userStore.setToken) userStore.setToken(null);
      if (userStore.setUser) userStore.setUser(null);
      profileMenuOpen.value = false;
      router.push('/login');
    };

   const getProfilePhoto = () => {
    return (
      user.value?.avatar_url ||
      user.value?.tutor?.photo_url ||
      'https://via.placeholder.com/40'
    );
  };


    const openEnrollModal = (type) => {
      enrollType.value = type;
      showEnrollModal.value = true;
      profileMenuOpen.value = false;
    };

    const closeEnrollModal = () => {
      showEnrollModal.value = false;
      enrollType.value = '';
    };

    const switchToTutorDashboard = () => {
      userStore.setActiveRole('tutor');
      profileMenuOpen.value = false;
      
    };

    const switchToStudentDashboard = () => {
      userStore.setActiveRole('student');
      profileMenuOpen.value = false;
      
    };

    return { 
      profileMenuOpen, 
      user, 
      toggleMobileMenu,
      logout,
      getProfilePhoto,
      showEnrollModal,
      enrollType,
      openEnrollModal,
      closeEnrollModal,
      switchToTutorDashboard,
      switchToStudentDashboard,
    };
  }
};
</script>
