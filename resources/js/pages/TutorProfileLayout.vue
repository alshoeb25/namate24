<template>
  <div>
    
    <!-- Profile Content Area -->
    <div class="max-w-7xl mx-auto px-4 mt-6 mb-20">
      <router-view />
    </div>
  </div>
</template>

<script>
import TutorHeader from '../components/tutor/TutorHeader.vue';

export default {
  name: 'TutorProfileLayout',
  components: {
    TutorHeader,
  },
};
</script>

<style scoped>
</style>
