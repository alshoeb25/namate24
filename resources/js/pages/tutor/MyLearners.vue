<template>
  <div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-6xl mx-auto px-4">
      <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
              <i class="fas fa-user-graduate mr-2 text-indigo-600"></i>My Learners
            </h1>
            <p class="text-gray-600">Students who hired you via requirements</p>
          </div>
        </div>
      </div>

      <div v-if="loading" class="text-center py-12 text-gray-600">Loading learners...</div>
      <div v-else-if="learners.length === 0" class="bg-white rounded-xl shadow-md p-12 text-center">
        <i class="fas fa-user-graduate text-gray-300 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No learners yet</h3>
        <p class="text-gray-600">Once a student hires you, they will appear here.</p>
      </div>

      <div v-else class="space-y-4">
        <div v-for="learner in learners" :key="learner.requirement_id" class="bg-white rounded-xl shadow-md p-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
              <h3 class="text-xl font-semibold text-gray-800">
                {{ learner.student_name }}
              </h3>
              <p class="text-sm text-gray-600 mt-1">
                <i class="fas fa-book mr-1"></i>
                {{ learner.subjects?.length ? learner.subjects.join(', ') : (learner.subject_name || 'Subject') }}
              </p>
              <p class="text-sm text-gray-600 mt-1">
                <i class="fas fa-map-marker-alt mr-1"></i>
                {{ learner.city || learner.location || 'Location not specified' }}
                <span v-if="learner.area" class="ml-1">• {{ learner.area }}</span>
              </p>
              <p class="text-xs text-gray-500 mt-2">
                Hired on {{ formatDate(learner.hired_at) }}
              </p>
            </div>
            <div class="flex flex-col gap-2 text-sm">
              <div v-if="learner.student_email" class="text-gray-700">
                <i class="fas fa-envelope mr-1"></i>{{ learner.student_email }}
              </div>
              <div v-if="learner.student_phone" class="text-gray-700">
                <i class="fas fa-phone mr-1"></i>{{ learner.student_phone }}
              </div>
              <div class="text-xs text-gray-500">Requirement #{{ learner.requirement_id }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from '../../bootstrap';

export default {
  name: 'MyLearners',
  setup() {
    const learners = ref([]);
    const loading = ref(true);

    const fetchLearners = async () => {
      loading.value = true;
      try {
        const response = await axios.get('/api/tutor/learners');
        learners.value = response.data.data || [];
      } catch (error) {
        console.error('Failed to load learners:', error);
        learners.value = [];
      } finally {
        loading.value = false;
      }
    };

    const formatDate = (date) => {
      if (!date) return 'N/A';
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    };

    onMounted(() => {
      fetchLearners();
    });

    return {
      learners,
      loading,
      formatDate,
    };
  }
};
</script>
