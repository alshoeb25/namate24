<template>
  <main class="w-full">

    <!-- HERO / DASHBOARD HEADER -->
    <section class="relative overflow-hidden rounded-b-3xl shadow-lg w-full bg-cover bg-center
                   bg-gradient-to-r from-blue-500 to-purple-600">
    
        <div class="hero-overlay absolute inset-0 z-0"></div>
    
        <div class="max-w-7xl mx-auto px-4 py-14 md:py-20 text-white relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-1.5 mb-4">
                        <i class="fas fa-user-graduate text-sm"></i>
                        <span class="text-sm font-medium">Student Dashboard</span>
                    </div>
                    <h1 class="text-3xl md:text-5xl font-bold mt-2">Welcome Back, <span
                            class="text-blue-100">{{ user?.name || 'Student' }}!</span></h1>
                    <p class="mt-3 text-lg md:text-xl opacity-90 max-w-2xl">
                        Find the perfect tutor, manage requirements, and track your learning journey.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- DASHBOARD CARDS -->
    <section class="max-w-7xl mx-auto px-4 mt-6 md:-mt-10 pb-20 relative z-20">

        <!-- DESKTOP GRID -->
        <div class="hidden md:grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Find Tutor -->
            <router-link to="/search"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container bg-gradient-to-br from-blue-500 to-blue-700">
                    <i class="fas fa-search text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Find Tutor</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Search for the perfect tutor</p>
            </router-link>

            <!-- Request Tutor -->
            <router-link to="/student/request-tutor"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container bg-gradient-to-br from-green-500 to-green-700">
                    <i class="fas fa-user-plus text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Request Tutor</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Post your requirement</p>
            </router-link>
            
            <!-- Requirements List -->
            <router-link to="/student/requirements"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container bg-gradient-to-br from-purple-500 to-purple-700">
                    <i class="fas fa-clipboard-list text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">My Requirements</h3>
                <p class="text-sm text-gray-500 text-center mt-1">View your posted requirements</p>
            </router-link>
            
            <!-- Wallet -->
            <router-link to="/student/wallet"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container bg-gradient-to-br from-yellow-500 to-orange-600">
                    <i class="fas fa-wallet text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Wallet</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Manage your credits</p>
            </router-link>

            <!-- Reviews -->
            <router-link to="/student/reviews"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container bg-gradient-to-br from-pink-500 to-red-500">
                    <i class="fas fa-star text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Reviews</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Rate your tutors</p>
            </router-link>

            <!-- Hired Tutors -->
            <router-link to="/student/hired-tutors"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container bg-gradient-to-br from-orange-500 to-red-600">
                    <i class="fas fa-users text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Hired Tutors</h3>
                <p class="text-sm text-gray-500 text-center mt-1">View your tutors</p>
            </router-link>

            <!-- Conversations -->
            <router-link to="/conversations"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container bg-gradient-to-br from-teal-500 to-cyan-600">
                    <i class="fas fa-comments text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Messages</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Chat with tutors</p>
            </router-link>

            <!-- Profile -->
            <router-link to="/profile"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container bg-gradient-to-br from-indigo-500 to-purple-600">
                    <i class="fas fa-user-circle text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">My Profile</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Update your information</p>
            </router-link>

            <!-- Settings -->
            <router-link to="/student/settings"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container bg-gradient-to-br from-gray-500 to-gray-700">
                    <i class="fas fa-cog text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Settings</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Account preferences</p>
            </router-link>
        </div>

        <!-- MOBILE VIEW -->
        <div class="md:hidden space-y-4">
            <!-- Find Tutor -->
            <router-link to="/search" class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container-mobile bg-gradient-to-br from-blue-500 to-blue-700">
                    <i class="fas fa-search text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Find Tutor</p>
                    <p class="text-sm text-gray-500">Search for the perfect tutor</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>

            <!-- Request Tutor -->
            <router-link to="/student/request-tutor" class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container-mobile bg-gradient-to-br from-green-500 to-green-700">
                    <i class="fas fa-user-plus text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Request Tutor</p>
                    <p class="text-sm text-gray-500">Post your requirement</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>

            <!-- Requirements List -->
            <router-link to="/student/requirements" class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container-mobile bg-gradient-to-br from-purple-500 to-purple-700">
                    <i class="fas fa-clipboard-list text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">My Requirements</p>
                    <p class="text-sm text-gray-500">View your posted requirements</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>

            <!-- Wallet -->
            <router-link to="/student/wallet" class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container-mobile bg-gradient-to-br from-yellow-500 to-orange-600">
                    <i class="fas fa-wallet text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Wallet</p>
                    <p class="text-sm text-gray-500">Manage your credits</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>

            <!-- Reviews -->
            <router-link to="/student/reviews" class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container-mobile bg-gradient-to-br from-pink-500 to-red-500">
                    <i class="fas fa-star text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Reviews</p>
                    <p class="text-sm text-gray-500">Rate your tutors</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>

            <!-- Hired Tutors -->
            <router-link to="/student/hired-tutors" class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container-mobile bg-gradient-to-br from-orange-500 to-red-600">
                    <i class="fas fa-users text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Hired Tutors</p>
                    <p class="text-sm text-gray-500">View your tutors</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>

            <!-- Messages -->
            <router-link to="/conversations" class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container-mobile bg-gradient-to-br from-teal-500 to-cyan-600">
                    <i class="fas fa-comments text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Messages</p>
                    <p class="text-sm text-gray-500">Chat with tutors</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>

            <!-- Profile -->
            <router-link to="/profile" class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container-mobile bg-gradient-to-br from-indigo-500 to-purple-600">
                    <i class="fas fa-user-circle text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">My Profile</p>
                    <p class="text-sm text-gray-500">Update your information</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>

            <!-- Settings -->
            <router-link to="/student/settings" class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container-mobile bg-gradient-to-br from-gray-500 to-gray-700">
                    <i class="fas fa-cog text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Settings</p>
                    <p class="text-sm text-gray-500">Account preferences</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>
        </div>

    </section>
  </main>
</template>

<script>
import { computed, onMounted } from 'vue';
import { useUserStore } from '../store';

export default {
  name: 'StudentDashboard',
  setup() {
    const userStore = useUserStore();
    const user = computed(() => userStore.user);

    onMounted(async () => {
      if (!userStore.user) {
        await userStore.fetchUser();
      }
    });

    return { user };
  }
}
</script>

<style scoped>
.hero-overlay {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.5) 0%, rgba(147, 51, 234, 0.5) 100%);
}

.dashboard-card {
  transition: all 0.3s ease;
}

.dashboard-card:hover {
  transform: translateY(-4px);
}

.icon-container {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.icon-container-mobile {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
</style>
