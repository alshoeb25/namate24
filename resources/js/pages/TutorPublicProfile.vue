<template>
  <div v-if="loading" class="flex items-center justify-center min-h-screen">
    <div class="text-xl text-gray-600">Loading...</div>
  </div>

  <div v-else class="max-w-7xl mx-auto px-4 py-8">
    <div class="text-center mb-8">
      <h1 class="text-4xl font-normal text-gray-700 mb-2">
        <span>{{ profile?.user?.name || profile?.name || '--' }}</span>
        <span class="text-gray-500 font-light ml-2">Teaching</span>
      </h1>
      <div class="flex items-center justify-center text-gray-500 text-lg">
        <i class="fas fa-star mr-2"></i>
        <span>{{ profile?.rating_avg ? `${profile.rating_avg}/5 (${profile.rating_count || 0} reviews)` : 'No reviews yet' }}</span>
      </div>
    </div>

    <main class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <section class="lg:col-span-2 space-y-6">

        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-gray-700 mb-4">Teaching Overview</h2>
          <p class="text-gray-600 mb-6 leading-relaxed whitespace-pre-line">
            {{ profile?.about || profile?.headline || 'No overview added.' }}
          </p>

          <div v-if="profile?.teaching_methodology" class="space-y-4">
            <h3 class="font-semibold text-gray-700">Teaching Methodology</h3>
            <p class="text-gray-600 pl-4 whitespace-pre-line">{{ profile.teaching_methodology }}</p>
          </div>

          <div v-if="profile?.speciality || profile?.strength" class="space-y-4 mt-4">
            <div v-if="profile?.speciality">
              <h4 class="font-medium text-gray-700">Speciality</h4>
              <p class="text-gray-600 pl-4">{{ profile.speciality }}</p>
            </div>
            <div v-if="profile?.strength">
              <h4 class="font-medium text-gray-700 mt-4">Strength</h4>
              <p class="text-gray-600 pl-4">{{ profile.strength }}</p>
            </div>
          </div>
        </div>

        <!-- Introduction Video -->
        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-purple-600 mb-4 flex items-center">
            <i class="fas fa-video mr-3"></i>
            Introduction Video
          </h2>
          
          <div v-if="approvedVideoUrl">
            <div class="flex items-center justify-between gap-4 flex-wrap">
              <div>
                <p v-if="profile?.video_title" class="text-sm text-gray-600 mb-1">{{ profile.video_title }}</p>
                <p class="text-xs text-green-700 font-semibold">✓ Approved</p>
              </div>
              <a :href="approvedVideoUrl" target="_blank" rel="noopener noreferrer"
                 class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium">
                <i class="fas fa-external-link-alt mr-2"></i>
                Open Video
              </a>
            </div>
            <p v-if="profile?.youtube_intro_url" class="text-sm text-gray-500 mt-3 break-all">
              <i class="fab fa-youtube mr-1"></i>
              {{ profile.youtube_intro_url }}
            </p>
          </div>
          
          <div v-else class="text-gray-600">
            <p>No video found.</p>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-teal-600 mb-4 flex items-center">
            <i class="fas fa-graduation-cap mr-3"></i>
            Subjects
          </h2>
          <div v-if="!profile?.subjects || profile.subjects.length === 0" class="text-gray-600">
            <p>No subjects added.</p>
          </div>
          <div v-else class="space-y-3 text-gray-600">
            <p v-for="subject in profile.subjects" :key="subject.id" class="hover:text-teal-600 cursor-pointer transition-colors">
              {{ subject.name }}
              <span v-if="subject.pivot?.from_level_id || subject.pivot?.to_level_id" class="ml-1">
                ({{ getLevelLabel(subject) }})
              </span>
            </p>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-blue-600 mb-4 flex items-center">
            <i class="fas fa-briefcase mr-3"></i>
            Experience
          </h2>
          <div v-if="!profile?.experiences || profile.experiences.length === 0" class="text-gray-600">
            <p>No experience mentioned.</p>
          </div>
          <div v-else class="space-y-4">
            <div v-for="(exp, index) in profile.experiences" :key="index" class="border-l-2 border-blue-500 pl-4">
              <p class="font-medium text-gray-700">{{ exp.company || exp.organization || exp.institute }}</p>
              <p class="text-sm text-gray-600">{{ exp.position || exp.role || exp.designation }}</p>
              <p class="text-sm text-gray-500">
                {{ exp.from_year }} - {{ exp.to_year || 'Present' }}
                <span v-if="exp.current" class="ml-2 text-green-600">(Current)</span>
              </p>
              <p v-if="exp.description" class="text-sm mt-1 text-gray-600">{{ exp.description }}</p>
            </div>

            <div v-if="profile?.experience_years || profile?.experience_total_years" class="bg-blue-50 p-3 rounded-lg mt-4">
              <div class="flex flex-wrap gap-4 text-sm text-gray-700">
                <div v-if="profile?.experience_total_years">
                  <span class="font-semibold">Total:</span> {{ profile.experience_total_years }} years
                </div>
                <div v-if="profile?.experience_teaching_years">
                  <span class="font-semibold">Teaching:</span> {{ profile.experience_teaching_years }} years
                </div>
                <div v-if="profile?.experience_online_years">
                  <span class="font-semibold">Online:</span> {{ profile.experience_online_years }} years
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-teal-600 mb-4 flex items-center">
            <i class="fas fa-user-graduate mr-3"></i>
            Education
          </h2>
          <div v-if="!profile?.educations || profile.educations.length === 0" class="text-gray-600">
            <p>No education added.</p>
          </div>
          <div v-else class="space-y-4 text-gray-600">
            <div v-for="(edu, index) in profile.educations" :key="index">
              <p>
                <strong>{{ edu.degree || edu.degree_name }}</strong>
                <span v-if="edu.degree_type"> ({{ edu.degree_type }})</span>
              </p>
              <p class="text-sm">
                ({{ edu.from_year }}—{{ edu.to_year || 'Present' }})
                <span v-if="edu.current" class="text-green-600">(Pursuing)</span>
                from {{ edu.institute || edu.institute_name }}
              </p>
              <p v-if="edu.specialization" class="text-sm mt-1">
                <span class="font-medium">Specialization:</span> {{ edu.specialization }}
              </p>
              <p v-if="edu.description" class="text-sm mt-1">{{ edu.description }}</p>
            </div>
          </div>
        </div>

        <div v-if="profile?.courses && profile.courses.length > 0" class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-purple-600 mb-4 flex items-center">
            <i class="fas a-certificate mr-3"></i>
            Courses & Certifications
          </h2>
          <div class="space-y-3 text-gray-600">
            <div v-for="(course, index) in profile.courses" :key="index" class="bg-gray-50 p-3 rounded">
              <p class="font-medium">{{ course.name || course.title }}</p>
              <p v-if="course.provider" class="text-sm">{{ course.provider }}</p>
              <p v-if="course.year" class="text-sm text-gray-500">Completed: {{ course.year }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-blue-600 mb-4 flex items-center">
            <i class="fas fa-wallet mr-3"></i>
            Fee details
          </h2>
          <div v-if="!profile?.price_per_hour && !profile?.min_fee && !profile?.max_fee" class="text-gray-600">
            <p>Fee not set.</p>
          </div>
          <div v-else class="text-gray-600">
            <p v-if="profile?.price_per_hour">
              ₹{{ profile.price_per_hour }}/hour
            </p>
            <p v-else-if="profile?.min_fee || profile?.max_fee">
              ₹{{ profile.min_fee || 0 }}—{{ profile.max_fee || 0 }}/hour
              <span v-if="profile?.charge_type" class="text-sm"> ({{ profile.charge_type }})</span>
            </p>
            <p v-if="profile?.fee_notes" class="text-sm mt-2">
              <span class="font-medium">Note:</span> {{ profile.fee_notes }}
            </p>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-teal-600 mb-4 flex items-center">
            <i class="fas fa-thumbs-up mr-3"></i>
            Reviews
          </h2>
          <div v-if="(profile?.rating_count && profile.rating_count > 0) || (profile?.reviews && profile.reviews.length > 0)" class="space-y-4">
            <div class="bg-yellow-50 p-4 rounded-lg">
              <div class="flex items-center gap-4">
                <div class="text-4xl font-bold text-yellow-600">{{ profile.rating_avg }}</div>
                <div>
                  <div class="flex items-center gap-1 mb-1">
                    <span v-for="n in 5" :key="n" class="text-yellow-400">
                      {{ n <= Math.round(profile.rating_avg) ? '★' : '☆' }}
                    </span>
                  </div>
                  <p class="text-sm text-gray-600">Based on {{ profile.rating_count }} reviews</p>
                </div>
              </div>
            </div>

            <div v-if="profile?.reviews && profile.reviews.length > 0" class="space-y-3">
              <div v-for="(review, index) in profile.reviews" :key="index" class="border rounded-lg p-4 bg-gray-50">
                <div class="flex items-start justify-between mb-2">
                  <div>
                    <p class="font-medium">{{ review.student_name || 'Anonymous' }}</p>
                    <div class="flex items-center gap-1">
                      <span v-for="n in 5" :key="n" class="text-yellow-400 text-sm">
                        {{ n <= review.rating ? '★' : '☆' }}
                      </span>
                    </div>
                  </div>
                  <p class="text-sm text-gray-500">{{ formatDate(review.created_at) }}</p>
                </div>
                <p class="text-gray-600 text-sm">{{ review.comment }}</p>
              </div>
            </div>
          </div>
          <p v-else class="text-gray-600">
            No reviews yet. Please log in to leave a review.
          </p>
        </div>

       
      </section>

      <aside class="lg:col-span-1">
        <div class="bg-white rounded-lg p-6 mb-6 flex flex-col items-center">
          <div class="relative w-56 h-56 mb-4">
            <div class="w-full h-full bg-gray-500 rounded-full flex items-end justify-center overflow-hidden">
              <img v-if="profile?.photo_url || profile?.user?.avatar_url" :src="profile.photo_url || profile.user.avatar_url" :alt="profile?.user?.name || 'Tutor'" class="w-full h-full object-cover">
              <i v-else class="fas fa-user text-gray-400 text-9xl mb-4"></i>
            </div>
          </div>
          <div class="text-center">
            <p class="text-lg font-semibold text-gray-800">{{ profile?.user?.name || '--' }}</p>
            <p class="text-sm text-gray-500">{{ profile?.headline || 'Tutor' }}</p>
          </div>
        </div>

        <div class="bg-gray-100 rounded-lg p-6">
          <div class="grid grid-cols-2 gap-3 mb-6">
            <button @click="requireLogin('message')" class="bg-green-500 hover:bg-green-600 text-white py-2.5 px-4 rounded text-sm font-medium flex items-center justify-center">
              <i class="far fa-envelope mr-2"></i>Message
            </button>
            <button @click="requireLogin('phone')" class="bg-blue-500 hover:bg-blue-600 text-white py-2.5 px-4 rounded text-sm font-medium flex items-center justify-center">
              <i class="fas fa-phone mr-2"></i>Phone
            </button>
            <button @click="requireLogin('pay')" class="bg-purple-500 hover:bg-purple-600 text-white py-2.5 px-4 rounded text-sm font-medium flex items-center justify-center">
              <i class="far fa-credit-card mr-2"></i>Pay
            </button>
            <button @click="requireLogin('review')" class="bg-orange-500 hover:bg-orange-600 text-white py-2.5 px-4 rounded text-sm font-medium flex items-center justify-center">
              <i class="far fa-star mr-2"></i>Review
            </button>
          </div>

          <div class="space-y-4 text-sm">
            <div class="flex items-start">
              <i class="fas fa-map-marker-alt text-gray-600 w-5 mt-1 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3">{{ getLocation() }}</span>
            </div>

            <div class="flex items-center">
              <i class="fas fa-car text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Can travel:</span> {{ getTravelInfo() }}</span>
            </div>

            <div class="flex items-center">
              <i class="fas fa-chalkboard-teacher text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Total Teaching exp:</span> {{ getTotalExperience() }}</span>
            </div>

            <div class="flex items-center">
              <i class="fas fa-wifi text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Teaches online:</span> {{ profile?.online_available ? 'Yes' : 'No' }}</span>
            </div>

            <div v-if="profile?.experience_online_years" class="flex items-center">
              <i class="fas fa-wifi text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Online Teaching exp:</span> {{ profile.experience_online_years }} yrs.</span>
            </div>

            <div class="flex items-center">
              <i class="fas fa-home text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Teaches at student's home:</span> {{ isHomeTuitionAvailable() }}</span>
            </div>

            <div class="flex items-center">
              <i class="fas fa-book text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Homework Help:</span> {{ profile?.helps_homework ? 'Yes' : 'No' }}</span>
            </div>

            <div class="flex items-center">
              <i class="fas fa-venus-mars text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Gender:</span> {{ profile?.gender || 'Not specified' }}</span>
            </div>

            <div class="flex items-center">
              <i class="fas fa-briefcase text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Works as:</span> {{ getWorksAs() }}</span>
            </div>

            <div v-if="profile?.languages && profile.languages.length > 0" class="flex items-center">
              <i class="fas fa-language text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Languages:</span> {{ profile.languages.join(', ') }}</span>
            </div>

            <div v-if="profile?.has_digital_pen" class="flex items-center">
              <i class="fas fa-pen text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3">Has Digital Pen</span>
            </div>
          </div>
        </div>
      </aside>
    </main>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useUserStore } from '../store';

export default {
  name: 'TutorPublicProfile',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const userStore = useUserStore();
    const user = computed(() => userStore.user);
    const isLoggedIn = computed(() => !!user.value);
    const profile = ref(null);
    const loading = ref(true);
    const approvedVideoUrl = computed(() => {
      const p = profile.value;
      if (!p) return null;

      // normalize status
      const status = (p.video_approval_status || '').trim().toLowerCase();
      if (status !== 'approved') return null;

      // Prefer YouTube link if present
      if (p.youtube_intro_url) return p.youtube_intro_url;

      // Try explicit URL fields if the API provides them
      if (p.video_url) return p.video_url;
      if (p.introductory_video_url) return p.introductory_video_url;

      // Build storage URL for uploaded file
      if (p.introductory_video) {
        if (p.introductory_video.startsWith('http')) return p.introductory_video;
        const base = window.location.origin.replace(/\/$/, '');
        const path = p.introductory_video.replace(/^\/?storage\//, '');
        return `${base}/storage/${path}`;
      }

      return null;
    });

    async function loadProfile() {
      loading.value = true;
      try {
        const res = await axios.get(`/api/public/tutors/${route.params.id}`);
        profile.value = res.data;
      } catch (error) {
        console.error('Error loading tutor profile:', error);
      } finally {
        loading.value = false;
      }
    }

    function formatDate(dateString) {
      if (!dateString) return null;
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
    }

    function getTotalExperience() {
      if (profile.value?.experience_total_years) {
        return `${profile.value.experience_total_years} years`;
      }

      if (!profile.value?.experiences || profile.value.experiences.length === 0) {
        return 'Not specified';
      }

      let totalYears = 0;
      profile.value.experiences.forEach(exp => {
        const fromYear = parseInt(exp.from_year);
        const toYear = exp.to_year ? parseInt(exp.to_year) : new Date().getFullYear();
        totalYears += (toYear - fromYear);
      });

      return totalYears > 0 ? `${totalYears} years` : 'Not specified';
    }

    function getLocation() {
      if (profile.value?.city) {
        let location = profile.value.city;
        if (profile.value.state) location += `, ${profile.value.state}`;
        if (profile.value.country) location += `, ${profile.value.country}`;
        return location;
      }
      if (profile.value?.address) return profile.value.address;
      return 'Not specified';
    }

    function getTravelInfo() {
      if (profile.value?.travel_willing) {
        return profile.value.travel_distance_km ? `Yes (${profile.value.travel_distance_km} km)` : 'Yes';
      }
      return 'No';
    }

    function isHomeTuitionAvailable() {
      if (profile.value?.teaching_mode && Array.isArray(profile.value.teaching_mode)) {
        return profile.value.teaching_mode.includes('home') ? 'Yes' : 'No';
      }
      return 'Not specified';
    }

    function getWorksAs() {
      if (profile.value?.current_role) return profile.value.current_role;
      if (profile.value?.employed_full_time) return 'Full-time Employed';
      return 'Individual teacher';
    }

    function getLevelLabel(subject) {
      if (subject.pivot?.from_level_id && subject.pivot?.to_level_id) {
        return `Levels ${subject.pivot.from_level_id}-${subject.pivot.to_level_id}`;
      }
      if (subject.pivot?.from_level_id) {
        return `Level ${subject.pivot.from_level_id}+`;
      }
      return '';
    }

    function requireLogin(action) {
      if (!isLoggedIn.value) {
        return router.push({ name: 'login', query: { redirect: route.fullPath } });
      }
      alert(`${action} feature coming soon`);
    }

    onMounted(async () => {
      if (!userStore.user && userStore.token) {
        await userStore.fetchUser();
      }
      await loadProfile();
    });

    return {
      user,
      profile,
      loading,
      approvedVideoUrl,
      formatDate,
      getTotalExperience,
      getLocation,
      getTravelInfo,
      isHomeTuitionAvailable,
      getWorksAs,
      getLevelLabel,
      requireLogin
    };
  }
};
</script>
