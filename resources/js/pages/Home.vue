<template>
  <div class="w-full">

    <!-- MAIN CONTENT -->
    <main class="w-full">
      <!-- Hero Section -->
      <section class="w-full">
        <div class="text-black rounded-b-3xl p-6 shadow-lg w-full bg-cover bg-center 
                   bg-[url('https://image2url.com/images/1765221100057-fff8f4b5-27df-48e0-8d70-7393bebec0ff.png')] 
                   md:bg-[url('https://image2url.com/images/1765432244131-32eb4e62-559a-40c6-87c3-d046b0b27ae1.png')]">
          <div class="desktop-bg max-w-3xl mx-auto">
            <h2 class="text-2xl font-bold text-white">
              Empowering Teachers & Students Worldwide
            </h2>
            <p class="text-sm mt-1 opacity-90 text-white">
              Expert guidance for every subject, anywhere.
            </p>

            <div class="mt-4 bg-white p-4 rounded-xl flex flex-col gap-3">
              <!-- Subject Input -->
              <div class="flex row gap-2">
                <div class="flex items-center bg-gray-100 rounded-lg px-3 py-2 flex-1">
                  <img src="https://img.icons8.com/?size=100&id=McUzNetNtaJK&format=png" alt="search icon"
                    class="h-5 w-5 object-contain opacity-70" />
                  <input v-model="searchSubject" type="text" placeholder="Search Subject"
                    class="ml-2 bg-transparent outline-none w-full text-gray-700">
                </div>
              </div>

              <!-- Location + Arrow -->
              <div class="flex row gap-2">
                <div class="flex items-center bg-gray-100 rounded-lg px-3 py-2 flex-1">
                  <img src="https://img.icons8.com/?size=100&id=p5n5ZAUprZsA&format=png" alt="location icon"
                    class="h-5 w-5 opacity-70" />
                  <input v-model="searchLocation" type="text" placeholder="Zip Code or City"
                    class="ml-2 bg-transparent outline-none w-full text-gray-700">
                </div>
                <button @click="performSearch"
                  class="bg-blue-600 hover:bg-blue-700 transition text-white px-2 rounded-lg 
                         flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M14 5l7 7-7 7M21 12H3" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Trending Categories -->
      <section class="max-w-7xl mx-auto px-4 mt-8">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Trending Courses</h2>
          <router-link to="/search" class="text-blue-600 text-sm font-medium hover:underline">See All</router-link>
        </div>

        <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hidden">
          <div v-for="(category, idx) in categories" :key="idx" class="flex flex-col items-center">
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center" :style="{ backgroundColor: category.bgColor }">
              <img :src="category.icon" class="w-8 h-8" />
            </div>
            <span class="mt-2 text-sm text-gray-700">{{ category.name }}</span>
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
          <button class="mt-4 md:mt-0 bg-white text-gray-900 font-medium 
                         px-5 py-2.5 rounded-lg shadow hover:bg-gray-100 transition">
            Apply for Franchise
          </button>
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
              <button class="bg-white text-black text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-200 transition w-full sm:w-auto text-center">
                Click to know more
              </button>
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

export default {
  name: 'Home',
  setup() {
    const router = useRouter();
    const userStore = useUserStore();

    const profileMenuOpen = ref(false);
    const mobileMenuOpen = ref(false);
    const searchSubject = ref('');
    const searchLocation = ref('');

    const categories = [
      { name: 'Math', bgColor: '#FEF3C7', icon: 'https://img.icons8.com/ios-filled/50/FD7E14/calculator.png' },
      { name: 'Science', bgColor: '#DBEAFE', icon: 'https://img.icons8.com/ios-filled/50/1E90FF/test-tube.png' },
      { name: 'English', bgColor: '#E9D5FF', icon: 'https://img.icons8.com/ios-filled/50/9C27B0/letter-e.png' },
      { name: 'Coding', bgColor: '#DCFCE7', icon: 'https://img.icons8.com/ios-filled/50/4CAF50/code.png' },
      { name: 'Music', bgColor: '#FCE7F3', icon: 'https://img.icons8.com/ios-filled/50/E91E63/musical-notes.png' },
    ];

    const featuredTeachers = ref([
      {
        id: 1,
        name: 'Sarah Jain',
        photo: 'https://image2url.com/images/1765181971596-87182fef-e224-4cd5-988f-9e288f3dad7b.png',
        mode: 'Online, Offline',
        subjects: ['Algebra', 'Calculus'],
        rating: 4.9
      },
      {
        id: 2,
        name: 'Rahul Trivedi',
        photo: 'https://image2url.com/images/1765182082393-64829582-e598-47cb-b30d-6ab41cc836db.png',
        mode: 'Online',
        subjects: ['Physics', 'Chemistry'],
        rating: 5.0
      },
      {
        id: 3,
        name: 'Prathima Roy',
        photo: 'https://image2url.com/images/1765182131342-c42f7847-c01b-4bc3-b8a3-70a182163597.png',
        mode: 'Online',
        subjects: ['English Lit', 'ESL'],
        rating: 4.8
      },
    ]);

    const isAuthenticated = computed(() => userStore.token !== null);
    const user = computed(() => userStore.user);

    const performSearch = () => {
      router.push({
        name: 'search',
        query: {
          subject: searchSubject.value,
          location: searchLocation.value,
        }
      });
    };

    const viewMoreTeachers = () => {
      router.push({ name: 'search' });
    };

    const logout = () => {
      userStore.logout();
      profileMenuOpen.value = false;
    };

    onMounted(async () => {
      await userStore.fetchUser();
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