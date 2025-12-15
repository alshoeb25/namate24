<template>
  <div class="max-w-7xl mx-auto px-4 mt-10 mb-20">

    <h1 class="text-2xl font-bold mb-6">Welcome, {{ user?.name || 'Tutor' }}</h1>

    <!-- Desktop View -->
    <section class="hidden md:flex justify-center gap-8">

      <!-- Upload Photo -->
      <router-link to="/tutor/profile/photo" class="w-64 bg-[#F16F63] text-white text-center rounded-xl py-8 shadow-lg hover:scale-105 transition-all">
        <img src="https://img.icons8.com/?size=60&id=60001&format=png" class="w-16 h-16 mx-auto mb-3" alt="Upload Photo">
        <p class="font-semibold text-xl">Upload Photo</p>
      </router-link>

      <!-- Verify Mobile -->
      <router-link to="/tutor/profile/phone" class="w-64 bg-[#F16F63] text-white text-center rounded-xl py-8 shadow-lg hover:scale-105 transition-all">
        <img src="https://img.icons8.com/?size=100&id=aYmX3YWIU9T0&format=png" class="w-16 h-16 mx-auto mb-3" alt="Verify Mobile">
        <p class="font-semibold text-xl">Verify Mobile</p>
      </router-link>

      <!-- Intro Video -->
      <router-link to="/tutor/profile/video" class="w-64 bg-[#FFF8D9] text-blue-700 text-center rounded-xl py-8 shadow-lg hover:scale-105 transition-all">
        <img src="https://img.icons8.com/?size=100&id=Eyki1QLaDgud&format=gif&color=f7f7f7" class="w-16 h-16 mx-auto mb-3" alt="Intro Video">
        <p class="font-semibold text-xl">Introduction Video</p>
      </router-link>

      <!-- My Profile -->
      <router-link to="/tutor/profile/view" class="w-64 bg-[#D8EEFB] text-blue-700 text-center rounded-xl py-8 shadow-lg hover:scale-105 transition-all">
        <img src="https://img.icons8.com/?size=100&id=7877&format=png" class="w-16 h-16 mx-auto mb-3" alt="My Profile">
        <p class="font-semibold text-xl">My Profile</p>
      </router-link>

    </section>


    <!-- Mobile View -->
    <section class="grid grid-cols-2 gap-6 md:hidden">

      <!-- Upload Photo -->
      <router-link to="/tutor/profile/photo" class="bg-[#F16F63] text-white text-center rounded-xl py-6 shadow-md hover:scale-105 transition-all">
        <img src="https://img.icons8.com/?size=60&id=60001&format=png" class="w-12 h-12 mx-auto mb-2" alt="Upload Photo">
        <p class="font-semibold text-base">Upload photo</p>
      </router-link>

      <!-- Verify Mobile -->
      <router-link to="/tutor/profile/phone" class="bg-[#F16F63] text-white text-center rounded-xl py-6 shadow-md hover:scale-105 transition-all">
        <img src="https://img.icons8.com/?size=100&id=aYmX3YWIU9T0&format=png" class="w-12 h-12 mx-auto mb-2" alt="Verify Mobile">
        <p class="font-semibold text-base">Verify Mobile</p>
      </router-link>

      <!-- Intro Video -->
      <router-link to="/tutor/profile/video" class="bg-[#FFF8D9] text-blue-700 text-center rounded-xl py-6 shadow-md hover:scale-105 transition-all">
        <img src="https://img.icons8.com/?size=100&id=Eyki1QLaDgud&format=gif&color=f7f7f7" class="w-12 h-12 mx-auto mb-2" alt="Intro Video">
        <p class="font-semibold text-base">Introduction Video</p>
      </router-link>

      <!-- My Profile -->
      <router-link to="/tutor/profile/view" class="bg-[#D8EEFB] text-blue-700 text-center rounded-xl py-6 shadow-md hover:scale-105 transition-all">
        <img src="https://img.icons8.com/?size=100&id=7877&format=png" class="w-12 h-12 mx-auto mb-2" alt="My Profile">
        <p class="font-semibold text-base">My Profile</p>
      </router-link>

    </section>

  </div>
</template>

<script>
import { computed, onMounted } from 'vue';
import { useUserStore } from '../store';

export default {
  name: 'TutorDashboard',
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
</style>
