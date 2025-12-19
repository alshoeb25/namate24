<template>
  <div>
    <router-view />
    
  </div>
</template>

<script>
import Footer from '../Footer.vue';

export default {
  name: 'StudentLayout',
  components: {
    Footer
  }
};
</script>
