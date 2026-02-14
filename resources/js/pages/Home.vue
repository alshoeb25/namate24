<template>
  <div class="w-full">

    <!-- MAIN CONTENT -->
    <main class="w-full">
      
      <!-- Search Component -->
      <HeroSearch />

      <!-- Trending Categories -->
      <section class="max-w-7xl mx-auto px-4 mt-8">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Trending Courses</h2>
          <router-link to="/tutors" class="text-blue-600 text-sm font-medium hover:underline">See All</router-link>
        </div>

        <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hidden">
          <div v-for="(category, idx) in categories" :key="idx" 
               class="flex flex-col items-center cursor-pointer transition-transform hover:scale-105 flex-shrink-0"
               style="width: 96px; max-width: 96px; min-width: 96px;"
               @click="searchBySubject(category.name)">
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center" :style="{ backgroundColor: category.bgColor }">
              <img :src="category.icon" class="w-8 h-8" />
            </div>
            <span class="mt-2 text-xs text-gray-700 text-center break-words leading-tight" style="width: 96px; word-wrap: break-word; overflow-wrap: break-word;">{{ category.name }}</span>
          </div>
        </div>
      </section>

      <!-- Featured Teachers -->
      <section v-if="showFeaturedSection" class="max-w-7xl mx-auto px-4 mt-10">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Featured Teachers</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="teacher in featuredTeachers" :key="teacher.id"
            class="bg-white rounded-2xl shadow-md p-4 flex items-center gap-4 border border-gray-100">
            <img :src="teacher.photo" class="w-16 h-16 rounded-xl object-cover">

            <div class="flex-1">
              <h3 class="font-bold text-gray-900 text-lg">{{ teacher.name }}</h3>
              <p class="text-sm text-gray-500">{{ teacher.mode }}</p>

              <div class="flex gap-2 mt-1">
                <span v-for="subject in teacher.subjects" :key="subject"
                  class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-lg">
                  {{ subject }}
                </span>
              </div>
            </div>

            <div class="flex flex-col items-end">
              <div class="flex items-center text-yellow-500 text-sm font-semibold">
                ⭐ {{ teacher.rating }}
              </div>
              <router-link :to="`/tutor/${teacher.id}`"
                class="mt-2 bg-blue-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700">
                Profile
              </router-link>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-center">
          <button @click="viewMoreTeachers"
            class="w-full max-w-sm py-3 bg-white text-blue-600 border border-gray-200 rounded-xl 
                   shadow-sm text-sm font-medium hover:bg-gray-50 transition">
            View More Tutors
          </button>
        </div>
      </section>

      <!-- Latest Tutors -->
      <section class="max-w-7xl mx-auto px-4 mt-10">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Latest Tutors</h2>
          <router-link to="/tutors" class="text-blue-600 text-sm font-medium hover:underline">See All</router-link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="teacher in latestTeachers" :key="teacher.id"
            @click="$router.push(`/tutor/${teacher.id}`)"
            class="bg-white rounded-2xl shadow-md p-4 flex items-center gap-4 border border-gray-100 cursor-pointer hover:shadow-lg transition">
            <img :src="teacher.photo" class="w-16 h-16 rounded-xl object-cover">

            <div class="flex-1">
              <h3 class="font-bold text-gray-900 text-lg">{{ teacher.name }}</h3>
              <p class="text-sm text-gray-500">{{ teacher.mode }}</p>

              <div class="flex gap-2 mt-1">
                <span v-for="subject in teacher.subjects" :key="subject"
                  class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-lg">
                  {{ subject }}
                </span>
              </div>
            </div>

            <div class="flex flex-col items-end">
              <div class="flex items-center text-pink-600 text-sm font-semibold">
                ⭐ {{ teacher.rating }}
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- ========== MODIFIED: STANDALONE CARDS REMOVED, POST BUTTONS MOVED INSIDE BOTTOM OF CURRENT POST SECTIONS ========== -->
        <!-- two-column layout – Current Student Posts / Current Teacher Posts with button at bottom -->
        <section class="max-w-7xl mx-auto px-4 mt-10 mb-6">
            <!-- grid container: two equal columns, left and right -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- LEFT COLUMN – STUDENT POSTS + BUTTON AT BOTTOM -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-5 flex flex-col h-full">
                    <!-- header: only heading + view all (unchanged) -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Latest Requirements</h3>
                      <router-link to="/latest-posts" class="text-xs text-blue-600 font-medium hover:underline">View All</router-link>
                    </div>

                    <!-- feed area – student posts (API) -->
                    <div class="flex-1">
                      <div v-for="(post, idx) in leftPosts" :key="post.id"
                        class="flex items-start gap-3 py-2"
                        :class="{ 'border-b border-gray-100': idx < leftPosts.length - 1 }">
                        <img :src="post.photo" class="w-8 h-8 rounded-full object-cover">
                        <div class="flex-1">
                          <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900">{{ post.name }}</p>
                            <span class="text-xs text-gray-500">{{ formatTimeAgo(post.createdAt) }}</span>
                          </div>
                          <p class="text-xs text-gray-600 mt-0.5">{{ post.details }}</p>
                          <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full">{{ post.subject }}</span>
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ post.location }}</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- MOVED BUTTON: student "Post Requirement" with tagline – now at bottom of left column (w-64, centered) -->
                    <div class="mt-6 flex flex-col items-center gap-2 pt-2 border-t border-gray-100">
                        <button @click="handlePostRequirement"
                            class="w-64 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white text-sm font-medium px-6 py-3.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Post Requirement
                        </button>
                        <p class="text-sm text-gray-500">Get help from expert teachers</p>
                    </div>
                </div>

                <!-- RIGHT COLUMN – LATEST TUTORS + BUTTON AT BOTTOM -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-5 flex flex-col h-full">
                    <!-- header: only heading + view all (unchanged) -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Latest Tutors</h3>
                        <router-link to="/tutors" class="text-xs text-purple-600 font-medium hover:underline">View All</router-link>
                    </div>

                    <!-- feed area – latest tutors (API, limit 3) -->
                    <div class="flex-1">
                      <div v-for="(teacher, idx) in latestTeachers.slice(0, 3)" :key="teacher.id"
                        class="flex items-start gap-3 py-2"
                        :class="{ 'border-b border-gray-100': idx < Math.min(latestTeachers.length, 3) - 1 }">
                        <img :src="teacher.photo" class="w-8 h-8 rounded-full object-cover">
                        <div class="flex-1">
                          <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900">{{ teacher.name }}</p>
                            <span class="text-xs text-gray-500">{{ teacher.mode }}</span>
                          </div>
                          <div class="flex items-center gap-1 mt-1 text-xs text-yellow-600 font-medium">
                            <span>⭐</span>
                            <span>{{ teacher.rating }}</span>
                          </div>
                          <div class="flex items-center gap-2 mt-1">
                            <span v-for="subject in teacher.subjects" :key="subject"
                              class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                              {{ subject }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- MOVED BUTTON: teacher "Post Requirement" with tagline – now at bottom of right column (w-64, centered) -->
                    <div class="mt-6 flex flex-col items-center gap-2 pt-2 border-t border-gray-100">
                        <button @click="handleBecomeTutor"
                            class="w-64 bg-gradient-to-r from-purple-600 to-purple-500 hover:from-purple-700 hover:to-purple-600 text-white text-sm font-medium px-6 py-3.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            {{ user?.tutor ? 'View Profile' : 'Become Tutors' }}
                        </button>
                        <p class="text-sm text-gray-500">Offer your teaching expertise</p>
                    </div>
                </div>
            </div>
        </section>

                  <EnrollmentModal
                  :show="showEnrollModal"
                  :type="enrollType"
                  :redirect-to="enrollRedirect"
                  @close="closeEnrollModal"
                  />

      <!-- Franchise CTA -->
      <section class="max-w-6xl mx-auto px-4 mt-10 mb-10">
        <div class="w-full max-w-6xl bg-gradient-to-r from-[#0F1C2E] to-[#1A2D45] 
                    text-white rounded-2xl p-6 shadow-lg
                    md:flex md:items-center md:justify-between md:p-8">
          <div>
            <h2 class="text-xl md:text-2xl font-semibold leading-tight">
              Are you looking for
            </h2>
            <p class="text-sm md:text-base mt-1 opacity-90">
              Partner with us and build a profitable future
            </p>
          </div>
          <router-link to="/contact-us"
                       class="mt-4 md:mt-0 bg-white text-gray-900 font-medium 
                              px-5 py-2.5 rounded-lg shadow hover:bg-gray-100 transition inline-flex items-center justify-center">
            Apply for Franchise
          </router-link>
        </div>
      </section>

      <!-- Blog Card -->
      <section class="max-w-6xl mx-auto px-4 mt-10 mb-10">
        <div class="relative bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
          <img src="https://images.pexels.com/photos/2982449/pexels-photo-2982449.jpeg"
            class="w-full h-72 object-cover">
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
          <div class="absolute inset-0 flex flex-col justify-end p-4 sm:p-5 text-white">
            <h2 class="text-lg sm:text-xl font-semibold mb-3 leading-tight">
              Here are the latest education updates
            </h2>
            <div class="flex items-center justify-between flex-wrap gap-3">
              <router-link to="/contact-us"
                           class="bg-white text-black text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-200 transition w-full sm:w-auto text-center inline-flex items-center justify-center">
                Click to know more
              </router-link>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useUserStore } from '../store';
import HeroSearch from '../components/HeroSearch.vue';
import EnrollmentModal from '../components/EnrollmentModal.vue';
import axios from 'axios';

export default {
  name: 'Home',
  components: {
    HeroSearch,
    EnrollmentModal,
  },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const userStore = useUserStore();

    const profileMenuOpen = ref(false);
    const mobileMenuOpen = ref(false);
    const searchSubject = ref('');
    const searchLocation = ref('');

    const categories = ref([]);
    const showFeaturedSection = ref(false);
    const showEnrollModal = ref(false);
    const enrollType = ref('');
    const enrollRedirect = ref('');

    // Icon mapping for subjects with colors
    const subjectIconMap = {
      'Mathematics': { icon: 'https://img.icons8.com/ios-filled/50/FD7E14/calculator.png', bgColor: '#FEF3C7' },
      'Math': { icon: 'https://img.icons8.com/ios-filled/50/FD7E14/calculator.png', bgColor: '#FEF3C7' },
      'Physics': { icon: 'https://img.icons8.com/ios-filled/50/1E90FF/test-tube.png', bgColor: '#DBEAFE' },
      'Chemistry': { icon: 'https://img.icons8.com/ios-filled/50/00BFA5/flask.png', bgColor: '#E0F2F1' },
      'Biology': { icon: 'https://cdn-icons-png.flaticon.com/128/1548/1548285.png', bgColor: '#E8F5E9' },
      'Science': { icon: 'https://img.icons8.com/ios-filled/50/1E90FF/test-tube.png', bgColor: '#DBEAFE' },
      'English': { icon: 'https://img.icons8.com/ios-filled/50/9C27B0/book.png', bgColor: '#E9D5FF' },
      'Coding': { icon: 'https://img.icons8.com/ios-filled/50/4CAF50/code.png', bgColor: '#DCFCE7' },
      'Programming': { icon: 'https://img.icons8.com/ios-filled/50/4CAF50/code.png', bgColor: '#DCFCE7' },
      'Computer Science': { icon: 'https://img.icons8.com/ios-filled/50/4CAF50/laptop.png', bgColor: '#DCFCE7' },
      'Music': { icon: 'https://img.icons8.com/ios-filled/50/E91E63/musical-notes.png', bgColor: '#FCE7F3' },
      'Art': { icon: 'https://img.icons8.com/ios-filled/50/FF5722/paint-palette.png', bgColor: '#FFEBEE' },
      'History': { icon: 'https://img.icons8.com/ios-filled/50/795548/hourglass.png', bgColor: '#EFEBE9' },
      'Geography': { icon: 'https://img.icons8.com/ios-filled/50/2196F3/globe.png', bgColor: '#E3F2FD' },
      'Economics': { icon: 'https://img.icons8.com/ios-filled/50/FFC107/money.png', bgColor: '#FFF9C4' },
      'Accountancy': { icon: 'https://img.icons8.com/ios-filled/50/4CAF50/accounting.png', bgColor: '#F1F8E9' },
      'Business Studies': { icon: 'https://img.icons8.com/ios-filled/50/607D8B/business.png', bgColor: '#ECEFF1' },
      'Political Science': { icon: 'https://img.icons8.com/ios-filled/50/3F51B5/vote.png', bgColor: '#E8EAF6' },
      'Sociology': { icon: 'https://img.icons8.com/ios-filled/50/9C27B0/people.png', bgColor: '#F3E5F5' },
      'Psychology': { icon: 'https://img.icons8.com/ios-filled/50/673AB7/brain.png', bgColor: '#EDE7F6' },
      'default': { icon: 'https://img.icons8.com/ios-filled/50/3F51B5/book.png', bgColor: '#E8EAF6' }
    };

    const featuredTeachers = ref([]);
    const latestTeachers = ref([]);
    const latestPosts = ref([]);

    const isAuthenticated = computed(() => userStore.token !== null);
    const user = computed(() => userStore.user);
    const leftPosts = computed(() => latestPosts.value.slice(0, 3));

    // Fetch subjects from database
    const fetchSubjects = async () => {
      try {
        const response = await axios.get('/api/subjects');
        const subjects = response.data;
        
        // Map subjects with icons, limit to 10
        categories.value = subjects.slice(0, 10).map(subject => {
          const iconData = subjectIconMap[subject.name] || subjectIconMap['default'];
          return {
            name: subject.name,
            bgColor: iconData.bgColor,
            icon: iconData.icon
          };
        });
      } catch (error) {
        console.error('Failed to fetch subjects:', error);
        // Fallback to default categories if fetch fails
        categories.value = [
          { name: 'Math', bgColor: '#FEF3C7', icon: 'https://img.icons8.com/ios-filled/50/FD7E14/calculator.png' },
          { name: 'Science', bgColor: '#DBEAFE', icon: 'https://img.icons8.com/ios-filled/50/1E90FF/test-tube.png' },
          { name: 'English', bgColor: '#E9D5FF', icon: 'https://img.icons8.com/ios-filled/50/9C27B0/book.png' },
          { name: 'Coding', bgColor: '#DCFCE7', icon: 'https://img.icons8.com/ios-filled/50/4CAF50/code.png' },
          { name: 'Music', bgColor: '#FCE7F3', icon: 'https://img.icons8.com/ios-filled/50/E91E63/musical-notes.png' },
        ];
      }
    };

    // Fetch featured teachers from API
    const fetchFeaturedTeachers = async () => {
      try {
        const response = await axios.get('/api/tutors/featured');
        featuredTeachers.value = response.data.data.map(tutor => ({
          id: tutor.user?.id || tutor.user_id,
          name: tutor.user?.name || 'Teacher',
          photo: tutor.user?.avatar_url || tutor.photo_url || 'https://via.placeholder.com/150',
          mode: tutor.teaching_mode === 'both' ? 'Online, Offline' : tutor.teaching_mode === 'online' ? 'Online' : 'Offline',
          subjects: (tutor.subjects || []).slice(0, 2).map(s => s.name),
          rating: tutor.rating_avg || 0
        }));
      } catch (error) {
        console.error('Failed to fetch featured teachers:', error);
        // Keep empty array on error
        featuredTeachers.value = [];
      }
    };

    // Fetch latest tutors from API
    const fetchLatestTeachers = async () => {
      try {
        const response = await axios.get('/api/tutors/latest');
        latestTeachers.value = response.data.data.map(tutor => ({
          id: tutor.user?.id || tutor.user_id || tutor.id,
          name: tutor.user?.name || 'Teacher',
          photo: tutor.user?.avatar_url || tutor.photo_url || 'https://via.placeholder.com/150',
          mode: tutor.teaching_mode === 'both' ? 'Online, Offline' : tutor.teaching_mode === 'online' ? 'Online' : 'Offline',
          subjects: (tutor.subjects || []).slice(0, 2).map(s => s.name),
          rating: tutor.rating_avg || 0
        }));
      } catch (error) {
        console.error('Failed to fetch latest tutors:', error);
        latestTeachers.value = [];
      }
    };

    const getSubjectLabel = (req) => {
      if (req.subjects && req.subjects.length > 0) {
        return req.subjects[0].name || req.subjects[0];
      }
      if (req.subject && req.subject.name) {
        return req.subject.name;
      }
      if (req.subject_name) {
        return req.subject_name;
      }
      return 'Subject';
    };

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

    const fetchLatestPosts = async () => {
      try {
        const response = await axios.get('/api/requirements/latest', {
          params: { limit: 3 }
        });
        const items = response.data.data || [];
        latestPosts.value = items.map(req => {
          return {
            id: req.id,
            name: req.student_name || 'Student',
            photo: 'https://via.placeholder.com/40',
            subject: Array.isArray(req.subjects) && req.subjects.length > 0 ? req.subjects[0] : 'Subject',
            location: req.location || 'Location not specified',
            details: req.details || 'New requirement posted',
            createdAt: req.posted_at || req.created_at
          };
        });
      } catch (error) {
        console.error('Failed to fetch latest posts:', error);
        latestPosts.value = [];
      }
    };

    const performSearch = () => {
      router.push({
        name: 'search',
        query: {
          subject: searchSubject.value,
          location: searchLocation.value,
        }
      });
    };

    const searchBySubject = (subjectName) => {
      router.push({
        name: 'tutors',
        query: {
          subject: subjectName,
        }
      });
    };

    const viewMoreTeachers = () => {
      router.push({ 
        name: 'tutors',
        query: { featured: 'true' }
      });
    };

    const logout = () => {
      userStore.logout();
      profileMenuOpen.value = false;
    };

    const openEnrollModal = (type, redirectTo = '') => {
      enrollType.value = type;
      enrollRedirect.value = redirectTo;
      showEnrollModal.value = true;
    };

    const closeEnrollModal = () => {
      showEnrollModal.value = false;
      enrollType.value = '';
      enrollRedirect.value = '';
    };

    const handleBecomeTutor = async () => {
      if (!isAuthenticated.value) {
        router.push({ path: '/login', query: { redirect: '/?enroll=teacher' } });
        return;
      }

      if (user.value?.tutor) {
        router.push('/tutor/profile/view');
        return;
      }

      openEnrollModal('teacher');
    };

    const handlePostRequirement = async () => {
      if (!isAuthenticated.value) {
        router.push({ path: '/login', query: { redirect: '/?enroll=student&next=/student/request-tutor' } });
        return;
      }

      if (user.value?.student) {
        router.push('/student/request-tutor');
        return;
      }

      openEnrollModal('student', '/student/request-tutor');
    };

    const tryOpenEnrollFromQuery = async () => {
      if (!isAuthenticated.value) return;

      if (route.query.enroll === 'teacher' && !user.value?.tutor) {
        openEnrollModal('teacher');
        return;
      }

      if (route.query.enroll === 'student' && !user.value?.student) {
        const nextPath = typeof route.query.next === 'string' ? route.query.next : '';
        openEnrollModal('student', nextPath);
      }
    };

    onMounted(async () => {
      await userStore.fetchUser();
      await fetchSubjects();
      await fetchFeaturedTeachers();
      await fetchLatestTeachers();
      await fetchLatestPosts();
      await tryOpenEnrollFromQuery();
    });

    watch(
      () => [route.query.enroll, isAuthenticated.value, user.value?.tutor],
      () => {
        tryOpenEnrollFromQuery();
      }
    );

    return {
      profileMenuOpen,
      mobileMenuOpen,
      searchSubject,
      searchLocation,
      categories,
      showFeaturedSection,
      featuredTeachers,
      latestTeachers,
      isAuthenticated,
      user,
      leftPosts,
      showEnrollModal,
      enrollType,
      enrollRedirect,
      performSearch,
      searchBySubject,
      viewMoreTeachers,
      handlePostRequirement,
      formatTimeAgo,
      handleBecomeTutor,
      closeEnrollModal,
      logout,
    };
  }
};
</script>

<style scoped>
.scrollbar-hidden::-webkit-scrollbar {
  display: none;
}
.scrollbar-hidden {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>