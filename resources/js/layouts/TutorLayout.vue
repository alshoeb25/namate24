<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header with Navigation -->
    <TutorHeader />

    <!-- Main Content Area -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <slot />
    </main>

    <!-- Footer (Optional) -->
    <footer class="bg-gray-900 text-gray-300 mt-20 py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p>&copy; 2025 NaMate24. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script>
import TutorHeader from './TutorHeader.vue';

export default {
  name: 'TutorLayout',
  components: {
    TutorHeader,
  },
};
</script>

<style scoped>
/* Layout styling */
</style>
