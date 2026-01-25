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
      <section class="max-w-7xl mx-auto px-4 mt-10">
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
              Here are the latest admission updates
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
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '../store';
import HeroSearch from '../components/HeroSearch.vue';
import axios from 'axios';

export default {
  name: 'Home',
  components: {
    HeroSearch,
  },
  setup() {
    const router = useRouter();
    const userStore = useUserStore();

    const profileMenuOpen = ref(false);
    const mobileMenuOpen = ref(false);
    const searchSubject = ref('');
    const searchLocation = ref('');

    const categories = ref([]);

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

    const isAuthenticated = computed(() => userStore.token !== null);
    const user = computed(() => userStore.user);

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

    onMounted(async () => {
      await userStore.fetchUser();
      await fetchSubjects();
      await fetchFeaturedTeachers();
    });

    return {
      profileMenuOpen,
      mobileMenuOpen,
      searchSubject,
      searchLocation,
      categories,
      featuredTeachers,
      isAuthenticated,
      user,
      performSearch,
      searchBySubject,
      viewMoreTeachers,
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