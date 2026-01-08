<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
      <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
          <i class="fas fa-star mr-2 text-yellow-500"></i>My Reviews
        </h1>
        <p class="text-gray-600">Reviews you've given to tutors</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="space-y-4">
        <div v-for="n in 3" :key="n" class="bg-white rounded-xl shadow-md p-6 animate-pulse">
          <div class="flex gap-4">
            <div class="w-16 h-16 bg-gray-200 rounded-full"></div>
            <div class="flex-1">
              <div class="h-4 bg-gray-200 rounded w-1/3 mb-2"></div>
              <div class="h-3 bg-gray-200 rounded w-1/2 mb-2"></div>
              <div class="h-3 bg-gray-200 rounded w-full"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="reviews.length === 0" class="bg-white rounded-xl shadow-md p-12 text-center">
        <i class="fas fa-star text-gray-300 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Reviews Yet</h3>
        <p class="text-gray-600 mb-6">Book a tutor to leave your first review</p>
        <router-link to="/search" 
                     class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          <i class="fas fa-search mr-2"></i>Find Tutors
        </router-link>
      </div>

      <!-- Reviews List -->
      <div v-else class="space-y-4">
        <div v-for="review in reviews" :key="review.id"
             class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow overflow-hidden">
          <div class="p-6">
            <div class="flex items-start gap-4">
              <!-- Tutor Avatar -->
              <router-link :to="`/tutor/${review.tutor?.user_id}`">
                <img v-if="review.tutor?.user?.avatar_url || review.tutor?.photo_url"
                     :src="review.tutor.user?.avatar_url || review.tutor.photo_url"
                     class="w-16 h-16 rounded-full object-cover">
                <div v-else 
                     class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                  <i class="fas fa-user text-white text-xl"></i>
                </div>
              </router-link>

              <div class="flex-1">
                <!-- Tutor Name -->
                <router-link :to="`/tutor/${review.tutor?.user_id}`">
                  <h3 class="text-lg font-bold text-gray-900 hover:text-blue-600 transition-colors">
                    {{ review.tutor?.user?.name || 'Tutor' }}
                  </h3>
                </router-link>

                <!-- Display Mode -->
                <template v-if="editingId !== review.id">
                  <!-- Rating Stars -->
                  <div class="flex items-center gap-1 mt-1 mb-3">
                    <i v-for="star in review.rating" 
                       :key="`filled-${star}`" 
                       class="fas fa-star text-yellow-400 text-sm"></i>
                    <i v-for="star in (5 - review.rating)" 
                       :key="`empty-${star}`" 
                       class="far fa-star text-gray-300 text-sm"></i>
                    <span class="ml-2 text-sm text-gray-500">
                      {{ review.rating }}/5
                    </span>
                  </div>

                  <!-- Review Comment -->
                  <p class="text-gray-700 leading-relaxed mb-3">{{ review.comment }}</p>

                  <!-- Review Date & Status -->
                  <div class="flex items-center gap-4 text-sm">
                    <span class="text-gray-500">
                      <i class="far fa-calendar mr-1"></i>
                      {{ formatDate(review.created_at) }}
                    </span>
                    <span v-if="review.moderation_status" 
                          :class="[
                            'px-2 py-1 rounded text-xs font-medium',
                            review.moderation_status === 'approved' ? 'bg-green-100 text-green-800' :
                            review.moderation_status === 'rejected' ? 'bg-red-100 text-red-800' :
                            'bg-yellow-100 text-yellow-800'
                          ]">
                      {{ review.moderation_status === 'approved' ? 'Published' :
                         review.moderation_status === 'rejected' ? 'Not Published' :
                         'Under Review' }}
                    </span>
                    <span v-if="review.booking_id" class="text-gray-500">
                      <i class="fas fa-calendar-check mr-1"></i>
                      From Booking
                    </span>
                    <span v-else-if="review.related_requirement_id" class="text-gray-500">
                      <i class="fas fa-clipboard-list mr-1"></i>
                      From Requirement
                    </span>
                  </div>

                  <!-- Edit Button -->
                  <div class="mt-4">
                    <button @click="startEdit(review)" 
                            class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700">
                      <i class="fas fa-edit mr-2"></i>Edit Review
                    </button>
                  </div>
                </template>

                <!-- Edit Mode -->
                <template v-else>
                  <div class="mt-2 grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                    <div>
                      <label class="block text-sm text-gray-600 mb-1">Rating</label>
                      <select v-model.number="editRating" class="w-28 px-3 py-2 border rounded-lg">
                        <option v-for="n in 5" :key="n" :value="n">{{ n }} / 5</option>
                      </select>
                    </div>
                    <div class="md:col-span-3">
                      <label class="block text-sm text-gray-600 mb-1">Comment</label>
                      <textarea v-model="editComment" rows="3" class="w-full px-3 py-2 border rounded-lg"></textarea>
                    </div>
                  </div>
                  <div class="mt-4 flex gap-2">
                    <button @click="saveEdit(review.id)" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                      <i class="fas fa-save mr-2"></i>Save
                    </button>
                    <button @click="cancelEdit()" 
                            class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                      Cancel
                    </button>
                  </div>
                  <p class="text-xs text-gray-500 mt-2">Editing resubmits your review for moderation.</p>
                </template>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" 
             class="flex justify-center items-center gap-2 mt-6">
          <button @click="loadPage(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-chevron-left"></i>
          </button>
          <span class="text-gray-600">
            Page {{ pagination.current_page }} of {{ pagination.last_page }}
          </span>
          <button @click="loadPage(pagination.current_page + 1)"
                  :disabled="pagination.current_page === pagination.last_page"
                  class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-chevron-right"></i>
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
  name: 'StudentReviews',
  setup() {
    const userStore = useUserStore();
    const reviews = ref([]);
    const loading = ref(false);
    const editingId = ref(null);
    const editRating = ref(5);
    const editComment = ref('');
    const pagination = ref({
      current_page: 1,
      last_page: 1,
      per_page: 20,
      total: 0
    });

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    };

    const loadReviews = async (page = 1) => {
      loading.value = true;
      try {
        const response = await axios.get('/api/student/reviews', {
          params: { per_page: 20, page }
        });
        
        reviews.value = response.data.data || [];
        pagination.value = {
          current_page: response.data.current_page || 1,
          last_page: response.data.last_page || 1,
          per_page: response.data.per_page || 20,
          total: response.data.total || 0
        };
      } catch (error) {
        console.error('Failed to fetch reviews:', error);
        reviews.value = [];
      } finally {
        loading.value = false;
      }
    };

    const startEdit = (review) => {
      editingId.value = review.id;
      editRating.value = review.rating;
      editComment.value = review.comment || '';
    };

    const cancelEdit = () => {
      editingId.value = null;
      editRating.value = 5;
      editComment.value = '';
    };

    const saveEdit = async (id) => {
      try {
        await axios.patch(`/api/student/reviews/${id}`, {
          rating: editRating.value,
          comment: editComment.value
        });
        await loadReviews(pagination.value.current_page);
        cancelEdit();
      } catch (error) {
        console.error('Failed to update review:', error);
      }
    };

    const loadPage = (page) => {
      if (page >= 1 && page <= pagination.value.last_page) {
        loadReviews(page);
      }
    };

    onMounted(async () => {
      await userStore.fetchUser();
      if (userStore.user?.id) {
        await loadReviews();
      }
    });

    return {
      reviews,
      loading,
      pagination,
      formatDate,
      loadPage,
      editingId,
      editRating,
      editComment,
      startEdit,
      cancelEdit,
      saveEdit
    };
  }
};
</script>
