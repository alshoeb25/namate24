<template>
  <div class="min-h-screen bg-gray-50">

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">My Tutors</h1>
        <p class="text-gray-600 mt-2">Review and manage your hired tutors</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="space-y-4">
        <div v-for="n in 3" :key="n" class="bg-white rounded-lg shadow-md p-6 animate-pulse">
          <div class="flex gap-4">
            <div class="w-20 h-20 bg-gray-200 rounded-lg"></div>
            <div class="flex-1">
              <div class="h-4 bg-gray-200 rounded w-1/3 mb-2"></div>
              <div class="h-3 bg-gray-200 rounded w-1/2 mb-2"></div>
              <div class="h-3 bg-gray-200 rounded w-2/3"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="hiredTutors.length === 0" class="bg-white rounded-lg shadow-md p-12 text-center">
        <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
          <i class="fas fa-user-tie text-gray-400 text-3xl"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">No tutors hired yet</h3>
        <p class="text-gray-600 mb-6">Start your learning journey by hiring a tutor</p>
        <router-link to="/search"
                     class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          <i class="fas fa-search mr-2"></i>Find Tutors
        </router-link>
      </div>

      <!-- Hired Tutors Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="item in hiredTutors" :key="item.id"
             class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all overflow-hidden">
          
          <!-- Tutor Image -->
          <div class="relative h-48 bg-gray-200 overflow-hidden">
            <img v-if="item.tutor.user?.avatar_url || item.tutor.photo_url"
                 :src="item.tutor.user?.avatar_url || item.tutor.photo_url"
                 class="w-full h-full object-cover">
            <div v-else class="w-full h-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
              <i class="fas fa-user text-white text-4xl"></i>
            </div>

            <!-- Status Badge -->
            <span :class="['absolute top-3 right-3 px-3 py-1 text-xs font-semibold rounded-full',
                           item.status === 'completed' ? 'bg-green-100 text-green-800' :
                           item.status === 'confirmed' ? 'bg-blue-100 text-blue-800' :
                           'bg-yellow-100 text-yellow-800']">
              {{ item.status }}
            </span>
          </div>

          <!-- Content -->
          <div class="p-4">
            <h3 class="font-bold text-lg text-gray-900">{{ item.tutor.user?.name }}</h3>
            
            <!-- Rating -->
            <div class="flex items-center gap-2 mt-2">
              <div class="flex items-center text-yellow-500">
                <i class="fas fa-star"></i>
                <span class="ml-1 text-sm font-semibold">{{ item.tutor.rating_avg || 'N/A' }}</span>
              </div>
              <span class="text-gray-500 text-sm">({{ item.tutor.rating_count || 0 }} reviews)</span>
            </div>

            <!-- Subjects -->
            <div v-if="item.tutor.subjects?.length" class="mt-3 flex flex-wrap gap-2">
              <span v-for="subject in item.tutor.subjects.slice(0, 2)" :key="subject.id"
                    class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded">
                {{ subject.name }}
              </span>
              <span v-if="item.tutor.subjects.length > 2" class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">
                +{{ item.tutor.subjects.length - 2 }}
              </span>
            </div>

            <!-- Source Badge (for requirements) -->
            <div v-if="item.source === 'requirement' && item.subjects_requested" class="mt-3">
              <span class="text-xs text-gray-600">
                <i class="fas fa-clipboard-list mr-1"></i>Hired for: {{ item.subjects_requested }}
              </span>
            </div>

            <!-- Booking Info -->
            <div class="mt-4 pt-4 border-t border-gray-200 space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600">Session Date</span>
                <span class="font-semibold">{{ formatDate(item.start_at) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Price</span>
                <span class="font-semibold text-blue-600">₹{{ item.session_price }}</span>
              </div>
            </div>

            <!-- Review Section -->
            <div class="mt-4 pt-4 border-t border-gray-200">
              <div v-if="item.review">
                <!-- Existing Review -->
                <div class="space-y-2">
                  <div class="flex items-center gap-2">
                    <div class="flex text-yellow-500">
                      <i v-for="star in item.review.rating" :key="`filled-${star}`" class="fas fa-star text-sm"></i>
                      <i v-for="star in (5 - item.review.rating)" :key="`empty-${star}`" class="far fa-star text-sm text-gray-300"></i>
                    </div>
                    <span class="text-xs text-gray-500">{{ formatDate(item.review.created_at) }}</span>
                  </div>
                  <p class="text-sm text-gray-700 italic">{{ item.review.comment }}</p>
                </div>
              </div>
              <div v-else-if="item.status === 'completed' || item.source === 'requirement'">
                <!-- Review Form -->
                <button @click="openReviewModal(item)"
                        class="w-full py-2 px-3 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition-colors text-sm font-medium">
                  <i class="fas fa-pen mr-1"></i>Write a Review
                </button>
              </div>
              <div v-else class="text-center text-gray-500 text-xs py-2">
                Complete session to write review
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-4 flex gap-2">
              <router-link :to="`/tutor/${item.tutor.user_id}`"
                           class="flex-1 py-2 px-3 bg-gray-100 text-gray-900 rounded hover:bg-gray-200 transition-colors text-sm font-medium text-center">
                <i class="fas fa-eye mr-1"></i>Profile
              </router-link>
              <button @click="contactTutor(item)"
                      class="flex-1 py-2 px-3 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors text-sm font-medium">
                <i class="fas fa-message mr-1"></i>Contact
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Review Modal -->
    <div v-if="showReviewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Review {{ selectedTutor?.tutor?.user?.name }}</h3>

        <!-- Star Rating -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
          <div class="flex gap-2">
            <button v-for="star in 5" :key="star"
                    @click="reviewData.rating = star"
                    class="text-3xl transition-colors"
                    :class="star <= reviewData.rating ? 'text-yellow-400' : 'text-gray-300'">
              <i class="fas fa-star"></i>
            </button>
          </div>
        </div>

        <!-- Review Text -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Your Review</label>
          <textarea v-model="reviewData.comment"
                    placeholder="Share your experience with this tutor..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    rows="4"></textarea>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3">
          <button @click="closeReviewModal"
                  class="flex-1 py-2 px-4 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200 transition-colors font-medium">
            Cancel
          </button>
          <button @click="submitReview"
                  :disabled="submittingReview"
                  class="flex-1 py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50">
            <span v-if="submittingReview"><i class="fas fa-spinner fa-spin mr-1"></i>Submitting...</span>
            <span v-else><i class="fas fa-paper-plane mr-1"></i>Submit Review</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import HeaderRoot from '../components/header/HeaderRoot.vue';
import { useUserStore } from '../store';

export default {
  name: 'HiredTutors',
  components: {
    HeaderRoot,
  },
  setup() {
    const userStore = useUserStore();
    const hiredTutors = ref([]);
    const loading = ref(false);
    const showReviewModal = ref(false);
    const selectedTutor = ref(null);
    const submittingReview = ref(false);
    const reviewData = ref({
      rating: 5,
      comment: ''
    });

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    };

    const fetchHiredTutors = async () => {
      loading.value = true;
      try {
        const response = await axios.get('/api/student/hired-tutors');
        hiredTutors.value = response.data.data || [];
      } catch (error) {
        console.error('Failed to fetch hired tutors:', error);
        hiredTutors.value = [];
      } finally {
        loading.value = false;
      }
    };

    const openReviewModal = (item) => {
      selectedTutor.value = item;
      reviewData.value = {
        rating: 5,
        comment: ''
      };
      showReviewModal.value = true;
    };

    const closeReviewModal = () => {
      showReviewModal.value = false;
      selectedTutor.value = null;
    };

    const submitReview = async () => {
      if (!selectedTutor.value || !reviewData.value.rating) return;

      submittingReview.value = true;
      try {
        const reviewPayload = {
          student_id: userStore.user.id,
          tutor_id: selectedTutor.value.tutor_id,
          rating: reviewData.value.rating,
          comment: reviewData.value.comment
        };

        // Add booking_id or related_requirement_id based on source
        if (selectedTutor.value.source === 'requirement') {
          reviewPayload.related_requirement_id = selectedTutor.value.requirement_id;
        } else {
          reviewPayload.booking_id = selectedTutor.value.id;
        }

        await axios.post(`/api/tutors/${selectedTutor.value.tutor_id}/reviews`, reviewPayload);

        // Update the tutor with review
        const tutorIndex = hiredTutors.value.findIndex(b => b.id === selectedTutor.value.id);
        if (tutorIndex >= 0) {
          hiredTutors.value[tutorIndex].review = {
            rating: reviewData.value.rating,
            comment: reviewData.value.comment,
            created_at: new Date()
          };
        }

        closeReviewModal();
        alert('Review submitted successfully!');
      } catch (error) {
        console.error('Failed to submit review:', error);
        alert('Failed to submit review. Please try again.');
      } finally {
        submittingReview.value = false;
      }
    };

    const contactTutor = (item) => {
      // Navigate to conversation or messaging
      alert('Messaging feature coming soon!');
    };

    onMounted(async () => {
      await userStore.fetchUser();
      if (userStore.user?.id) {
        await fetchHiredTutors();
      }
    });

    return {
      hiredTutors,
      loading,
      showReviewModal,
      selectedTutor,
      reviewData,
      submittingReview,
      formatDate,
      openReviewModal,
      closeReviewModal,
      submitReview,
      contactTutor,
    };
  }
};
</script>

<style scoped>
/* Custom scrollbar for better UX */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
