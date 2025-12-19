<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header with Navigation -->
    <TutorHeader />

    <!-- Main Content Area -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <slot />
    </main>

  </div>
</template>

<script>
import TutorHeader from './TutorHeader.vue';
import Footer from '../components/Footer.vue';

export default {
  name: 'TutorLayout',
  components: {
    TutorHeader,
    Footer,
  },
};
</script>

<style scoped>
/* Layout styling */
</style>
