<template>
  <div class="min-h-screen bg-gray-50">

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Contacted Tutors</h1>
        <p class="text-gray-600 mt-2">View tutors you've contacted and manage your reviews</p>
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
      <div v-else-if="contactedTutors.length === 0" class="bg-white rounded-lg shadow-md p-12 text-center">
        <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
          <i class="fas fa-user-tie text-gray-400 text-3xl"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">No tutors contacted yet</h3>
        <p class="text-gray-600 mb-6">Start by unlocking tutor contact details</p>
        <router-link to="/search"
                     class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          <i class="fas fa-search mr-2"></i>Find Tutors
        </router-link>
      </div>

      <!-- Contacted Tutors Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="tutor in contactedTutors" :key="tutor.id"
             class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all overflow-hidden">
          
          <!-- Tutor Image -->
          <div class="relative h-48 bg-gray-200 overflow-hidden">
            <img v-if="tutor.photo_url"
                 :src="tutor.photo_url"
                 class="w-full h-full object-cover">
            <div v-else class="w-full h-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
              <i class="fas fa-user text-white text-4xl"></i>
            </div>

            <!-- Review Status Badge -->
            <span v-if="tutor.has_reviewed"
                  :class="['absolute top-3 right-3 px-3 py-1 text-xs font-semibold rounded-full',
                           tutor.review?.status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800']">
              Review {{ tutor.review?.status }}
            </span>
          </div>

          <!-- Content -->
          <div class="p-4">
            <h3 class="font-bold text-lg text-gray-900">{{ tutor.name }}</h3>
            <p v-if="tutor.headline" class="text-sm text-gray-600 mt-1">{{ tutor.headline }}</p>
            
            <!-- Rating -->
            <div class="flex items-center gap-2 mt-2">
              <div class="flex items-center text-yellow-500">
                <i class="fas fa-star"></i>
                <span class="ml-1 text-sm font-semibold">{{ tutor.rating_avg || 'N/A' }}</span>
              </div>
              <span class="text-gray-500 text-sm">({{ tutor.rating_count || 0 }} reviews)</span>
            </div>

            <!-- Price -->
            <div v-if="tutor.price_per_hour" class="mt-2">
              <span class="text-sm font-semibold text-blue-600">₹{{ tutor.price_per_hour }}/hour</span>
            </div>

            <!-- Contacted Date -->
            <div class="mt-3 text-xs text-gray-500">
              <i class="fas fa-clock mr-1"></i>Contacted on {{ formatDate(tutor.contacted_at) }}
            </div>

            <!-- Contact Details (Always visible since unlocked) -->
            <div class="mt-4 pt-4 border-t border-gray-200 space-y-2">
              <div class="flex items-start gap-2 text-sm">
                <i class="fas fa-envelope text-blue-600 mt-0.5"></i>
                <a :href="`mailto:${tutor.email}`" class="text-blue-600 hover:underline break-all">{{ tutor.email }}</a>
              </div>
              <div v-if="tutor.phone" class="flex items-center gap-2 text-sm">
                <i class="fas fa-phone text-blue-600"></i>
                <a :href="`tel:${tutor.phone}`" class="text-blue-600 hover:underline">{{ tutor.phone }}</a>
              </div>
            </div>

            <!-- Review Section -->
            <div class="mt-4 pt-4 border-t border-gray-200">
              <div v-if="tutor.has_reviewed">
                <!-- Existing Review -->
                <div class="space-y-2">
                  <div class="flex items-center justify-between">
                    <div class="flex text-yellow-500">
                      <i v-for="star in tutor.review.rating" :key="`filled-${star}`" class="fas fa-star text-sm"></i>
                      <i v-for="star in (5 - tutor.review.rating)" :key="`empty-${star}`" class="far fa-star text-sm text-gray-300"></i>
                    </div>
                    <span class="text-xs text-gray-500">{{ formatDate(tutor.review.created_at) }}</span>
                  </div>
                  <p class="text-sm text-gray-700 italic">{{ tutor.review.comment }}</p>
                  
                  <!-- Edit button for pending reviews -->
                  <button v-if="tutor.review.status === 'pending'"
                          @click="editReview(tutor)"
                          class="w-full mt-2 py-1.5 px-3 bg-yellow-50 text-yellow-700 rounded hover:bg-yellow-100 transition-colors text-xs font-medium">
                    <i class="fas fa-edit mr-1"></i>Edit Review (Pending Approval)
                  </button>
                </div>
              </div>
              <div v-else>
                <!-- Review Form Button -->
                <button @click="openReviewModal(tutor)"
                        class="w-full py-2 px-3 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition-colors text-sm font-medium">
                  <i class="fas fa-pen mr-1"></i>Write a Review
                </button>
              </div>
            </div>

            <!-- Action Button -->
            <div class="mt-4">
              <router-link :to="`/tutor/${tutor.user_id}`"
                           class="block w-full py-2 px-3 bg-gray-100 text-gray-900 rounded hover:bg-gray-200 transition-colors text-sm font-medium text-center">
                <i class="fas fa-eye mr-1"></i>View Profile
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Review Modal -->
    <div v-if="showReviewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-xl font-bold text-gray-900 mb-4">
          {{ isEditingReview ? 'Edit Review for' : 'Review' }} {{ selectedTutor?.name }}
        </h3>

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
                    rows="4"
                    maxlength="1000"></textarea>
          <div class="text-xs text-gray-500 mt-1 text-right">{{ reviewData.comment?.length || 0 }}/1000</div>
        </div>

        <!-- Review Status Info (for editing) -->
        <div v-if="isEditingReview" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
          <p class="text-sm text-yellow-800">
            <i class="fas fa-info-circle mr-1"></i>Your review is pending approval. You can edit it until it's approved.
          </p>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3">
          <button @click="closeReviewModal"
                  class="flex-1 py-2 px-4 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200 transition-colors font-medium">
            Cancel
          </button>
          <button @click="submitReview"
                  :disabled="submittingReview || !reviewData.rating"
                  class="flex-1 py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50">
            <span v-if="submittingReview"><i class="fas fa-spinner fa-spin mr-1"></i>Submitting...</span>
            <span v-else><i class="fas fa-paper-plane mr-1"></i>{{ isEditingReview ? 'Update' : 'Submit' }} Review</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useUserStore } from '../store';

export default {
  name: 'ContactedTutors',
  setup() {
    const userStore = useUserStore();
    const contactedTutors = ref([]);
    const loading = ref(false);
    const showReviewModal = ref(false);
    const selectedTutor = ref(null);
    const submittingReview = ref(false);
    const isEditingReview = ref(false);
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

    const fetchContactedTutors = async () => {
      loading.value = true;
      try {
        const response = await axios.get('/api/student/contacted-tutors');
        contactedTutors.value = response.data.tutors || [];
      } catch (error) {
        console.error('Failed to fetch contacted tutors:', error);
        contactedTutors.value = [];
      } finally {
        loading.value = false;
      }
    };

    const openReviewModal = (tutor) => {
      selectedTutor.value = tutor;
      isEditingReview.value = false;
      reviewData.value = {
        rating: 5,
        comment: ''
      };
      showReviewModal.value = true;
    };

    const editReview = (tutor) => {
      selectedTutor.value = tutor;
      isEditingReview.value = true;
      reviewData.value = {
        rating: tutor.review.rating,
        comment: tutor.review.comment
      };
      showReviewModal.value = true;
    };

    const closeReviewModal = () => {
      showReviewModal.value = false;
      selectedTutor.value = null;
      isEditingReview.value = false;
    };

    const submitReview = async () => {
      if (!selectedTutor.value || !reviewData.value.rating) return;

      submittingReview.value = true;
      try {
        const studentId = userStore.user?.student?.id;
        if (!studentId) {
          alert('Student profile not found. Please complete your student profile first.');
          submittingReview.value = false;
          return;
        }

        if (isEditingReview.value) {
          // Update existing review
          await axios.put(`/api/student/review/${selectedTutor.value.review.id}`, {
            rating: reviewData.value.rating,
            comment: reviewData.value.comment
          });

          // Update the tutor with new review data
          const tutorIndex = contactedTutors.value.findIndex(t => t.id === selectedTutor.value.id);
          if (tutorIndex >= 0) {
            contactedTutors.value[tutorIndex].review.rating = reviewData.value.rating;
            contactedTutors.value[tutorIndex].review.comment = reviewData.value.comment;
          }

          alert('Review updated successfully! It will be visible once approved.');
        } else {
          // Submit new review
          const response = await axios.post('/api/student/submit-review', {
            tutor_id: selectedTutor.value.id,
            student_id: studentId,
            rating: reviewData.value.rating,
            comment: reviewData.value.comment
          });

          // Update the tutor with review
          const tutorIndex = contactedTutors.value.findIndex(t => t.id === selectedTutor.value.id);
          if (tutorIndex >= 0) {
            contactedTutors.value[tutorIndex].has_reviewed = true;
            contactedTutors.value[tutorIndex].review = response.data.review;
          }

          alert('Review submitted successfully! It will be visible once approved.');
        }

        closeReviewModal();
      } catch (error) {
        console.error('Failed to submit review:', error);
        const errorMessage = error.response?.data?.message || 'Failed to submit review. Please try again.';
        alert(errorMessage);
      } finally {
        submittingReview.value = false;
      }
    };

    onMounted(() => {
      fetchContactedTutors();
    });

    return {
      contactedTutors,
      loading,
      showReviewModal,
      selectedTutor,
      submittingReview,
      isEditingReview,
      reviewData,
      formatDate,
      fetchContactedTutors,
      openReviewModal,
      editReview,
      closeReviewModal,
      submitReview
    };
  }
};
</script>
