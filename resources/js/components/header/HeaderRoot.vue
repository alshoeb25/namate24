<template>
  <div>
    <!-- Guest Header (not logged in) -->
    <GuestHeader v-if="!isAuthenticated" />
    
    <!-- Authenticated Header (Two-level) -->
    <div v-else>
      <AuthTopBar />
      <TutorSecondaryMenu v-if="shouldShowTutorMenu" />
      <StudentSecondaryMenu v-else-if="shouldShowStudentMenu" />
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
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
    const route = useRoute();
    
    const isAuthenticated = computed(() => userStore.token !== null);
    const user = computed(() => userStore.user);
    
    // Check if user has both roles
    const hasBothRoles = computed(() => {
      return (user.value?.tutor && user.value?.student);
    });
    
    // Check if current route/URL matches tutor/teacher paths and user is tutor
    const shouldShowTutorMenu = computed(() => {
      const currentPath = route.path.toLowerCase();
      
      // Routes that should show menu based on user role
      const isSharedRoute = 
        currentPath === '/' || 
        currentPath === '/search' || 
        currentPath === '/tutors' ||
        currentPath === '/tutor-jobs';
      
      // If user has both roles
      if (hasBothRoles.value) {
        // On shared routes, default to tutor menu
        if (isSharedRoute) {
          return userStore.activeRole ? userStore.activeRole === 'tutor' : true;
        }
        return userStore.activeRole === 'tutor';
      }
      
      // Single role user
      const isTutorPath = currentPath.includes('tutor') || currentPath.includes('teacher');
      const isTutorUser = user.value?.role === 'tutor' || user.value?.tutor;
      
      // Show tutor menu on shared routes if user is tutor
      if (isSharedRoute && isTutorUser && !user.value?.student) {
        return true;
      }
      
      return isTutorPath && isTutorUser;
    });
    
    // Check if current route/URL matches student paths and user is student
    const shouldShowStudentMenu = computed(() => {
      const currentPath = route.path.toLowerCase();
      
      // Routes that should show menu based on user role
      const isSharedRoute = 
        currentPath === '/' || 
        currentPath === '/search' || 
        currentPath === '/tutors' ||
        currentPath === '/tutor-jobs';
      
      // If user has both roles
      if (hasBothRoles.value) {
        // On shared routes, default to tutor (so return false here)
        if (isSharedRoute) {
          return userStore.activeRole === 'student';
        }
        return userStore.activeRole === 'student';
      }
      
      // Single role user
      const isStudentPath = currentPath.includes('student');
      const isStudentUser = user.value?.role === 'student' || user.value?.student;
      
      // Show student menu on shared routes if user is student only
      if (isSharedRoute && isStudentUser && !user.value?.tutor) {
        return true;
      }
      
      return isStudentPath && isStudentUser;
    });

    return { 
      isAuthenticated, 
      user,
      shouldShowTutorMenu,
      shouldShowStudentMenu,
      hasBothRoles
    };
  }
};
</script>
