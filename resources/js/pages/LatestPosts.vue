<template>
  <div class="min-h-screen bg-gray-50">
    <div class="bg-white border-b shadow-sm py-8">
      <div class="max-w-7xl mx-auto px-4 flex items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Latest Posts</h1>
          <p class="text-gray-600 mt-1">Recent student requirements</p>
        </div>
        <button @click="handleBecomeTutor"
          class="bg-gradient-to-r from-purple-600 to-purple-500 hover:from-purple-700 hover:to-purple-600 text-white text-sm font-medium px-5 py-3 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
          {{ user?.tutor ? 'View Profile' : 'Become Tutors' }}
        </button>
      </div>
    </div>

    <main class="max-w-7xl mx-auto px-4 py-8">
      <div v-if="loading" class="space-y-4">
        <div v-for="n in 6" :key="n" class="bg-white rounded-xl shadow-md p-4 animate-pulse">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
            <div class="flex-1">
              <div class="h-4 bg-gray-200 rounded w-1/3 mb-2"></div>
              <div class="h-3 bg-gray-200 rounded w-2/3"></div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="space-y-4">
        <div v-if="posts.length === 0" class="bg-white rounded-xl shadow-md p-10 text-center">
          <p class="text-gray-600">No posts found.</p>
        </div>

        <div v-for="post in posts" :key="post.id" class="bg-white rounded-xl shadow-md p-4">
          <div class="flex items-start gap-3">
            <img v-if="post.photo" :src="post.photo" class="w-10 h-10 rounded-full object-cover">
            <div v-else class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-semibold">
              {{ getInitials(post.name) }}
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-900">{{ post.name }}</p>
                <span class="text-xs text-gray-500">{{ formatTimeAgo(post.createdAt) }}</span>
              </div>
              <p class="text-sm text-gray-600 mt-1">{{ post.details }}</p>
              <div class="flex items-center gap-2 mt-2 flex-wrap">
                <span v-for="subject in post.subjects" :key="subject"
                  class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full">
                  {{ subject }}
                </span>
                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                  {{ post.location }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <EnrollmentModal
      :show="showEnrollModal"
      :type="enrollType"
      @close="closeEnrollModal"
    />
  </div>
</template>

<script>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useUserStore } from '../store';
import EnrollmentModal from '../components/EnrollmentModal.vue';
import axios from 'axios';

export default {
  name: 'LatestPosts',
  components: {
    EnrollmentModal,
  },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const userStore = useUserStore();
    const posts = ref([]);
    const loading = ref(false);
    const showEnrollModal = ref(false);
    const enrollType = ref('');
    const isAuthenticated = computed(() => userStore.token !== null);
    const user = computed(() => userStore.user);

    const formatTimeAgo = (dateValue) => {
      if (!dateValue) return 'Just now';
      const time = new Date(dateValue).getTime();
      if (Number.isNaN(time)) return 'Just now';

      const seconds = Math.floor((Date.now() - time) / 1000);
      if (seconds < 60) return 'Just now';
      const minutes = Math.floor(seconds / 60);
      if (minutes < 60) return `${minutes} min ago`;
      const hours = Math.floor(minutes / 60);
      if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
      const days = Math.floor(hours / 24);
      if (days < 7) return `${days} day${days > 1 ? 's' : ''} ago`;
      const weeks = Math.floor(days / 7);
      if (weeks < 5) return `${weeks} week${weeks > 1 ? 's' : ''} ago`;
      const months = Math.floor(days / 30);
      if (months < 12) return `${months} month${months > 1 ? 's' : ''} ago`;
      const years = Math.floor(days / 365);
      return `${years} year${years > 1 ? 's' : ''} ago`;
    };

    const getInitials = (name) => {
      const value = (name || '').trim();
      if (!value) return 'S';
      const parts = value.split(/\s+/).filter(Boolean);
      const first = parts[0]?.[0] || '';
      const last = parts.length > 1 ? parts[parts.length - 1]?.[0] : '';
      return `${first}${last}`.toUpperCase();
    };

    const fetchLatestPosts = async () => {
      loading.value = true;
      try {
        const response = await axios.get('/api/requirements/latest', {
          params: { limit: 20 }
        });
        const items = response.data.data || [];
        posts.value = items.map(req => {
          return {
            id: req.id,
            name: req.student_name || 'Student',
            photo: req.photo_url || req.avatar_url || '',
            subjects: Array.isArray(req.subjects) && req.subjects.length > 0 ? req.subjects : ['Subject'],
            location: req.location || 'Location not specified',
            details: req.details || 'New requirement posted',
            createdAt: req.posted_at || req.created_at
          };
        });
      } catch (error) {
        console.error('Failed to fetch latest posts:', error);
        posts.value = [];
      } finally {
        loading.value = false;
      }
    };

    const openEnrollModal = (type) => {
      enrollType.value = type;
      showEnrollModal.value = true;
    };

    const closeEnrollModal = () => {
      showEnrollModal.value = false;
      enrollType.value = '';
    };

    const handleBecomeTutor = async () => {
      if (!isAuthenticated.value) {
        router.push({ path: '/login', query: { redirect: '/latest-posts?enroll=teacher' } });
        return;
      }

      if (user.value?.tutor) {
        router.push('/tutor/profile');
        return;
      }

      openEnrollModal('teacher');
    };

    const tryOpenEnrollFromQuery = async () => {
      if (route.query.enroll === 'teacher' && isAuthenticated.value && !user.value?.tutor) {
        openEnrollModal('teacher');
      }
    };

    onMounted(() => {
      fetchLatestPosts();
      tryOpenEnrollFromQuery();
    });

    watch(
      () => [route.query.enroll, isAuthenticated.value, user.value?.tutor],
      () => {
        tryOpenEnrollFromQuery();
      }
    );

    return {
      posts,
      loading,
      formatTimeAgo,
      getInitials,
      showEnrollModal,
      enrollType,
      user,
      handleBecomeTutor,
      closeEnrollModal
    };
  }
};
</script>
