<template>
  <div v-if="loading" class="flex items-center justify-center min-h-screen">
    <div class="text-xl text-gray-600">Loading...</div>
  </div>
  
  <div v-else class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
      <h1 class="text-4xl font-normal text-gray-700 mb-2">
        <span>{{ profile?.name || user?.name || '--' }}</span>
        <span class="text-gray-500 font-light ml-2">Teaching</span>
      </h1>
      <div class="flex items-center justify-center text-gray-500 text-lg">
        <i class="fas fa-star mr-2"></i>
        <span>{{ profile?.rating_avg ? `${profile.rating_avg}/5 (${profile.rating_count} reviews)` : 'No reviews yet' }}</span>
      </div>
    </div>

    <!-- Main Content -->
    <main class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- ================= LEFT CONTENT ================= -->
      <section class="lg:col-span-2 space-y-6">

        <!-- Teaching Overview Section -->
        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-gray-700 mb-4">Teaching Overview</h2>
          <p class="text-gray-600 mb-6 leading-relaxed whitespace-pre-line">
            {{ profile?.about || profile?.headline || 'No overview added.' }}
          </p>

          <!-- TEACHING METHODOLOGY -->
          <div v-if="profile?.teaching_methodology" class="space-y-4">
            <h3 class="font-semibold text-gray-700">Teaching Methodology</h3>
            <p class="text-gray-600 pl-4 whitespace-pre-line">{{ profile.teaching_methodology }}</p>
          </div>

          <!-- SPECIALITY & STRENGTH -->
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

        <!-- SUBJECTS -->
        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-teal-600 mb-4 flex items-center">
            <i class="fas fa-graduation-cap mr-3"></i>
            Subjects
          </h2>
          <div v-if="!profile?.subjects || profile.subjects.length === 0" class="text-gray-600">
            <p>No subjects added.</p>
          </div>
          <div v-else class="space-y-3 text-gray-600">
            <p v-for="subject in profile?.subjects" :key="subject.id" 
               class="hover:text-teal-600 cursor-pointer transition-colors">
              {{ subject.name }}
              <span v-if="subject.pivot?.from_level_id || subject.pivot?.to_level_id" class="ml-1">
                ({{ getLevelLabel(subject) }})
              </span>
            </p>
          </div>
        </div>

        <!-- EXPERIENCE -->
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
            
            <!-- EXPERIENCE SUMMARY -->
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

        <!-- EDUCATION -->
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

        <!-- COURSES -->
        <div v-if="profile?.courses && profile.courses.length > 0" class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-purple-600 mb-4 flex items-center">
            <i class="fas fa-certificate mr-3"></i>
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

        <!-- FEES -->
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

        <!-- REVIEWS -->
        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-teal-600 mb-4 flex items-center">
            <i class="fas fa-thumbs-up mr-3"></i>
            Reviews
          </h2>
          <div v-if="profile?.rating_count && profile.rating_count > 0" class="space-y-4">
            <!-- Rating Summary -->
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

            <!-- Reviews List -->
            <div v-if="profile?.reviews && profile.reviews.length > 0" class="space-y-3">
              <div v-for="(review, index) in profile.reviews" :key="index" 
                   class="border rounded-lg p-4 bg-gray-50">
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
            No reviews yet. Be the first one to 
            <a href="#" class="text-blue-500 hover:text-blue-700 underline">review this tutor</a>.
          </p>
        </div>

        <!-- Introduction Video (approved only) after reviews -->
        <div v-if="approvedVideoUrl" class="bg-white rounded-lg p-6 border-l-4 border-purple-500 shadow">
          <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
              <h2 class="text-xl font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-video text-purple-600"></i>
                Introduction Video
              </h2>
              <p v-if="profile?.video_title" class="text-sm text-gray-600 mt-1">{{ profile.video_title }}</p>
              <p class="text-xs text-green-700 font-semibold mt-2">Approved</p>
            </div>
            <a :href="approvedVideoUrl" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium">
              <i class="fas fa-external-link-alt mr-2"></i>
              Open Video in New Tab
            </a>
          </div>
          <p v-if="profile?.youtube_intro_url" class="text-sm text-gray-600 mt-3 break-all">
            Source: YouTube • {{ profile.youtube_intro_url }}
          </p>
        </div>

      </section>

      <!-- ================= RIGHT SIDEBAR ================= -->
      <aside class="lg:col-span-1">
        <!-- Profile Photo Upload -->
        <div class="bg-white rounded-lg p-6 mb-6 flex flex-col items-center">
          <div class="relative w-56 h-56 mb-4">
            <div class="w-full h-full bg-gray-500 rounded-full flex items-end justify-center overflow-hidden">
              <img v-if="profile?.photo_url || user?.profile_photo" 
                   :src="profile?.photo_url || user?.profile_photo"
                   :alt="profile?.name || user?.name"
                   class="w-full h-full object-cover">
              <i v-else class="fas fa-user text-gray-400 text-9xl mb-4"></i>
            </div>
            <!-- Upload buttons on sides -->
            <router-link to="/tutor/profile/photo"
                         class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-2 bg-red-500 hover:bg-red-600 text-white px-2 py-8 rounded text-xs font-medium">
            </router-link>
            <router-link to="/tutor/profile/photo"
                         class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-2 bg-red-500 hover:bg-red-600 text-white px-2 py-8 rounded text-xs font-medium">
            </router-link>
          </div>
          <router-link to="/tutor/profile/photo" 
                       class="text-white bg-gray-600 px-6 py-2 rounded text-sm hover:bg-gray-700">
            Upload your photo
          </router-link>
        </div>

        <!-- Action Buttons and Details Card -->
        <div class="bg-gray-100 rounded-lg p-6">
          <!-- ACTION BUTTONS -->
          <div class="grid grid-cols-2 gap-3 mb-6">
            <button class="bg-green-500 hover:bg-green-600 text-white py-2.5 px-4 rounded text-sm font-medium flex items-center justify-center" disabled>
              <i class="far fa-envelope mr-2"></i>Message
            </button>
            <button class="bg-blue-500 hover:bg-blue-600 text-white py-2.5 px-4 rounded text-sm font-medium flex items-center justify-center">
              <i class="fas fa-phone mr-2"></i>Phone
            </button>
            <button class="bg-purple-500 hover:bg-purple-600 text-white py-2.5 px-4 rounded text-sm font-medium flex items-center justify-center" disabled>
              <i class="far fa-credit-card mr-2"></i>Pay
            </button>
            <button class="bg-orange-500 hover:bg-orange-600 text-white py-2.5 px-4 rounded text-sm font-medium flex items-center justify-center" disabled>
              <i class="far fa-star mr-2"></i>Review
            </button>
          </div>

          <!-- Teacher Details -->
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
              <i class="fas fa-power-off text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Last login:</span> {{ getLastLoginDisplay() }}</span>
            </div>

            <div class="flex items-center">
              <i class="fas fa-user-plus text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Registered:</span> {{ getRegisteredDate() }}</span>
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
              <span class="text-gray-700 ml-3"><span class="font-semibold">Gender:</span> {{ profile?.gender || user?.gender || 'Not specified' }}</span>
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

          <!-- EDIT PROFILE BUTTON -->
          <router-link to="/tutor/dashboard" 
                       class="block w-full bg-blue-600 text-white text-center py-3 rounded-lg hover:bg-blue-700 transition font-semibold mt-6">
            Edit Profile
          </router-link>
        </div>
      </aside>

    </main>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useUserStore } from '../store';
import axios from 'axios';

export default {
  name: 'TutorProfile',
  setup() {
    const userStore = useUserStore();
    const user = computed(() => userStore.user);
    const profile = ref(null);
    const loading = ref(true);
    const approvedVideoUrl = computed(() => {
      const p = profile.value;
      if (!p) return null;
      const status = (p.video_approval_status || '').toLowerCase();
      if (status !== 'approved') return null;

      if (p.youtube_intro_url) return p.youtube_intro_url;
      if (p.video_url) return p.video_url;
      if (p.introductory_video_url) return p.introductory_video_url;

      if (p.introductory_video) {
        if (p.introductory_video.startsWith('http')) return p.introductory_video;
        const base = window.location.origin.replace(/\/$/, '');
        const path = p.introductory_video.replace(/^\/storage\//, '');
        return `${base}/storage/${path}`;
      }

      return null;
    });

    async function loadProfile() {
      loading.value = true;
      try {
        // Fetch authenticated tutor's profile
        const res = await axios.get('/api/tutor/profile/view');
        profile.value = res.data;
      } catch (error) {
        console.error('Error loading profile:', error);
        
        // If no profile data, try to get user data
        if (!userStore.user) {
          await userStore.fetchUser();
        }
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
      
      // Calculate total years from experiences array
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
        return profile.value.travel_distance_km 
          ? `Yes (${profile.value.travel_distance_km} km)` 
          : 'Yes';
      }
      return 'No';
    }

    function isHomeTuitionAvailable() {
      if (profile.value?.teaching_mode && Array.isArray(profile.value.teaching_mode)) {
        return profile.value.teaching_mode.includes('home') ? 'Yes' : 'No';
      }
      return 'Not specified';
    }

    function getFeeDisplay() {
      if (profile.value?.price_per_hour) {
        return `₹${profile.value.price_per_hour}/hr`;
      }
      if (profile.value?.min_fee && profile.value?.max_fee) {
        return `₹${profile.value.min_fee} - ₹${profile.value.max_fee}`;
      }
      return 'Not set';
    }

    function getLastLoginDisplay() {
      if (!user.value?.last_login_at) return 'Recently';
      const now = new Date();
      const lastLogin = new Date(user.value.last_login_at);
      const diffInSeconds = Math.floor((now - lastLogin) / 1000);
      
      if (diffInSeconds < 60) return `${diffInSeconds} secs ago`;
      if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} mins ago`;
      if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
      return formatDate(user.value.last_login_at);
    }

    function getRegisteredDate() {
      if (!user.value?.created_at) return 'Recently';
      const date = new Date(user.value.created_at);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }

    function getWorksAs() {
      if (profile.value?.current_role) return profile.value.current_role;
      if (profile.value?.employed_full_time) return 'Full-time Employed';
      return 'Individual teacher';
    }

    function getLevelLabel(subject) {
      // This would need to be enhanced with actual level names from API
      if (subject.pivot?.from_level_id && subject.pivot?.to_level_id) {
        return `Levels ${subject.pivot.from_level_id}-${subject.pivot.to_level_id}`;
      }
      if (subject.pivot?.from_level_id) {
        return `Level ${subject.pivot.from_level_id}+`;
      }
      return '';
    }

    onMounted(async () => {
      if (!userStore.user) {
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
      getFeeDisplay,
      getLastLoginDisplay,
      getRegisteredDate,
      getWorksAs,
      getLevelLabel
    };
  }
};
</script>

<style scoped>
  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  }
</style>