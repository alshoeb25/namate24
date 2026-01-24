<template>
  <main class="w-full">

    <!-- HERO / DASHBOARD HEADER -->
    <section class="relative overflow-hidden rounded-b-3xl shadow-lg w-full bg-cover bg-center
                   bg-[url('https://image2url.com/images/1765773528174-f33a81da-0108-401a-a459-3371ca54cae8.png')]
                   md:bg-[url('https://image2url.com/images/1765773881397-02e1b232-0a58-4304-b4ce-36e93cf9caee.png')]">
    
        <div class="hero-overlay absolute inset-0 z-0"></div>
    
        <div class="max-w-7xl mx-auto px-4 py-14 md:py-20 text-white relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-1.5 mb-4">
                        <i class="fas fa-chalkboard-teacher text-sm"></i>
                        <span class="text-sm font-medium">Teacher Dashboard</span>
                    </div>
                    <h1 class="text-3xl md:text-5xl font-bold mt-2">Welcome Back, <span
                            class="text-blue-200">{{ user?.name || 'Teacher' }}!</span></h1>
                    <p class="mt-3 text-lg md:text-xl opacity-90 max-w-2xl">
                        Manage your profile, update information, and connect with students seamlessly.
                    </p>
    
                    <!-- Progress indicator -->
                    <div class="mt-6 bg-white/20 backdrop-blur-sm rounded-xl p-4 max-w-md">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-medium">Profile Completion</span>
                            <span class="font-bold">{{ profileCompletion }}%</span>
                        </div>
                        <div class="w-full bg-white/30 rounded-full h-2.5">
                            <div class="bg-blue-300 h-2.5 rounded-full transition-all duration-500" :style="{ width: profileCompletion + '%' }"></div>
                        </div>
                        <p class="text-sm mt-2 text-blue-100">
                            {{ profileCompletion === 100 ? 'Your profile is complete!' : 'Complete your profile to get more visibility' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- DASHBOARD CARDS -->
    <section class="max-w-7xl mx-auto px-4 mt-6 md:-mt-10 pb-20 relative z-20">

        <!-- DESKTOP GRID -->
        <div class="hidden md:grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Upload Documents -->
            <router-link to="/tutor/documents"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container">
                    <i class="fas fa-file-upload text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Upload Documents</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Upload certificates and ID for verification</p>
            </router-link>
            <!-- Upload Photo -->
            <router-link to="/tutor/profile/photo"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container">
                    <i class="fas fa-camera text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Upload Photo</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Add a professional profile picture</p>
            </router-link>

            <!-- Student Leads -->
            <router-link to="/tutor-jobs"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container">
                    <i class="fas fa-users text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Student Leads</h3>
                <p class="text-sm text-gray-500 text-center mt-1">View and unlock student requirements</p>
            </router-link>
            
            <!-- Verify Mobile -->
            <router-link to="/tutor/profile/phone"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container">
                    <i class="fas fa-mobile-alt text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Verify Mobile</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Secure your account with verification</p>
            </router-link>
            
            <!-- Introduction Video -->
            <router-link to="/tutor/profile/video"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container">
                    <i class="fas fa-video text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Introduction Video</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Introduce yourself to students</p>
            </router-link>
            
            <!-- My Profile -->
            <router-link to="/tutor/profile/view"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container">
                    <i class="fas fa-eye text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">My Profile</h3>
                <p class="text-sm text-gray-500 text-center mt-1">View how students see your profile</p>
            </router-link>

            <!-- Personal Details -->
            <router-link to="/tutor/profile/personal-details"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container">
                    <i class="fas fa-id-card text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Personal Details</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Update your personal information</p>
            </router-link>

            <!-- Subjects -->
            <router-link to="/tutor/profile/subjects"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container">
                    <i class="fas fa-book text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Subjects</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Manage subjects you teach</p>
            </router-link>

            <!-- Address -->
            <router-link to="/tutor/profile/address"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container">
                    <i class="fas fa-map-marker-alt text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Address</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Update your location information</p>
            </router-link>

            <!-- Education -->
            <router-link to="/tutor/profile/education"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container">
                    <i class="fas fa-graduation-cap text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Education</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Add your educational background</p>
            </router-link>

            <!-- Experience -->
            <router-link to="/tutor/profile/experience"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container">
                    <i class="fas fa-briefcase text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Experience</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Add your teaching experience</p>
            </router-link>

            <!-- Teaching Details -->
            <router-link to="/tutor/profile/teaching-details"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container">
                    <i class="fas fa-chalkboard-teacher text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Teaching Details</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Specify your teaching preferences</p>
            </router-link>

            <!-- Profile Description -->
            <router-link to="/tutor/profile/description"
                class="dashboard-card flex flex-col items-center justify-center gap-4 bg-white rounded-2xl p-6 shadow-md hover:shadow-xl">
                <div class="icon-container">
                    <i class="fas fa-file-alt text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 text-center">Profile Description</h3>
                <p class="text-sm text-gray-500 text-center mt-1">Write about your teaching philosophy</p>
            </router-link>

        </div>

        <!-- MOBILE VIEW -->
        <div class="md:hidden space-y-4">
            <!-- Upload Documents -->
            <router-link to="/tutor/documents" class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container">
                    <i class="fas fa-file-upload text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Upload Documents</p>
                    <p class="text-sm text-gray-500">Upload certificates and ID for verification</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>
            <!-- Upload Photo -->
            <router-link to="/tutor/profile/photo" class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container">
                    <i class="fas fa-camera text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Upload Photo</p>
                    <p class="text-sm text-gray-500">Add a professional profile picture</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>
            
            <!-- Verify Mobile -->
            <router-link to="/tutor/profile/phone" class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container">
                    <i class="fas fa-mobile-alt text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Verify Mobile</p>
                    <p class="text-sm text-gray-500">Secure your account with verification</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>

            <!-- Student Leads -->
            <router-link to="/tutor-jobs" class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container">
                    <i class="fas fa-users text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Student Leads</p>
                    <p class="text-sm text-gray-500">Browse and unlock enquiries</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>
            
            <!-- Introduction Video -->
            <router-link to="/tutor/profile/video"
                class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container">
                    <i class="fas fa-video text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Introduction Video</p>
                    <p class="text-sm text-gray-500">Introduce yourself to students</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>
            
            <!-- My Profile -->
            <router-link to="/tutor/profile/view"
                class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container">
                    <i class="fas fa-eye text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">My Profile</p>
                    <p class="text-sm text-gray-500">View how students see your profile</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>

            <!-- Personal Details -->
            <router-link to="/tutor/profile/personal-details"
                class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container">
                    <i class="fas fa-id-card text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Personal Details</p>
                    <p class="text-sm text-gray-500">Update your personal information</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>
            
            <!-- Subjects -->
            <router-link to="/tutor/profile/subjects"
                class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container">
                    <i class="fas fa-book text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Subjects</p>
                    <p class="text-sm text-gray-500">Manage subjects you teach</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>
            
            <!-- Address -->
            <router-link to="/tutor/profile/address"
                class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container">
                    <i class="fas fa-map-marker-alt text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Address</p>
                    <p class="text-sm text-gray-500">Update your location information</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>
            
            <!-- Education -->
            <router-link to="/tutor/profile/education"
                class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container">
                    <i class="fas fa-graduation-cap text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Education</p>
                    <p class="text-sm text-gray-500">Add your educational background</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>
            
            <!-- Experience -->
            <router-link to="/tutor/profile/experience"
                class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container">
                    <i class="fas fa-briefcase text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Experience</p>
                    <p class="text-sm text-gray-500">Add your teaching experience</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>
            
            <!-- Teaching Details -->
            <router-link to="/tutor/profile/teaching-details"
                class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container">
                    <i class="fas fa-chalkboard-teacher text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Teaching Details</p>
                    <p class="text-sm text-gray-500">Specify your teaching preferences</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
            </router-link>
            
            <!-- Profile Description -->
            <router-link to="/tutor/profile/description"
                class="dashboard-card flex items-center gap-4 bg-white rounded-2xl p-4 shadow-md">
                <div class="icon-container">
                    <i class="fas fa-file-alt text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800">Profile Description</p>
                    <p class="text-sm text-gray-500">Write about your teaching philosophy</p>
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
  name: 'TutorDashboard',
  setup() {
    const userStore = useUserStore();
    const user = computed(() => userStore.user);

    // Calculate profile completion percentage
    const profileCompletion = computed(() => {
      if (!user.value) return 0;

      const fields = [
        user.value.name,
        user.value.email,
        user.value.phone,
        user.value.photo,
        user.value.video,
        user.value.bio || user.value.description,
        user.value.address || user.value.city,
        user.value.subjects?.length > 0,
        user.value.education?.length > 0,
        user.value.experience?.length > 0,
        user.value.teaching_mode,
        user.value.hourly_rate
      ];

      const filledFields = fields.filter(field => {
        if (typeof field === 'boolean') return field;
        if (typeof field === 'number') return field > 0;
        return field && field !== '';
      }).length;

      return Math.round((filledFields / fields.length) * 100);
    });

    onMounted(async () => {
      if (!userStore.user) {
        await userStore.fetchUser();
      }
    });

    return { user, profileCompletion };
  }
}
</script>

<style scoped>
.hero-overlay {
  background: linear-gradient(135deg, rgba(236, 72, 153, 0.5) 0%, rgba(147, 51, 234, 0.5) 100%);
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
  background: linear-gradient(135deg, #ec4899 0%, #9333ea 100%);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);
}
</style>
