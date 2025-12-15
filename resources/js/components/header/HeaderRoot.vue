<template>
  <div>
    <!-- Guest Header (not logged in) -->
    <GuestHeader v-if="!isAuthenticated" />
    
    <!-- Authenticated Header (Two-level) -->
    <div v-else>
      <AuthTopBar />
      <TutorSecondaryMenu v-if="user?.role === 'tutor'" />
      <StudentSecondaryMenu v-else-if="user?.role === 'student'" />
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';
import { useUserStore } from '../../store';
import GuestHeader from './GuestHeader.vue';
import AuthTopBar from './AuthTopBar.vue';
import TutorSecondaryMenu from './TutorSecondaryMenu.vue';
import StudentSecondaryMenu from './StudentSecondaryMenu.vue';

export default {
  name: 'HeaderRoot',
  components: {
    GuestHeader,
    AuthTopBar,
    TutorSecondaryMenu,
    StudentSecondaryMenu
  },
  setup() {
    const userStore = useUserStore();
    const isAuthenticated = computed(() => userStore.token !== null);
    const user = computed(() => userStore.user);

    return { isAuthenticated, user };
  }
};
</script>
