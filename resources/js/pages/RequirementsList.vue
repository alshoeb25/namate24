<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
      <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
          <i class="fas fa-clipboard-list mr-2 text-purple-600"></i>My Requirements
        </h1>
        <p class="text-gray-600">View and manage your tutor requests</p>
      </div>

      <!-- Requirements List -->
      <div class="space-y-4">
        <div v-for="req in requirements" :key="req.id" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
              <h3 class="text-xl font-semibold text-gray-800">{{ req.student_name || 'Student' }}</h3>
              <div v-if="req.subjects && req.subjects.length" class="flex flex-wrap gap-2 mt-2">
                <span v-for="subject in req.subjects" :key="subject.id" 
                      class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                  {{ subject.name }}
                </span>
              </div>
              <p class="text-sm text-gray-600 mt-2">
                <i class="fas fa-map-marker-alt mr-1"></i>{{ req.city || req.location }}
                <span v-if="req.area" class="ml-2">• {{ req.area }}</span>
                <span v-if="req.level" class="ml-2">• {{ req.level }}</span>
              </p>
            </div>
            <div class="flex items-center gap-2">
              <span class="px-3 py-1 rounded-full text-sm font-medium" 
                    :class="req.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'">
                {{ req.status }}
              </span>
              <button v-if="req.status === 'active'" @click="closeRequirement(req.id)" 
                      class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium transition">
                <i class="fas fa-times-circle mr-1"></i>Close
              </button>
              <button @click="editRequirement(req.id)" 
                      class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                <i class="fas fa-edit mr-1"></i>Edit
              </button>
            </div>
          </div>
          
          <p v-if="req.details" class="text-gray-700 mb-4">{{ req.details }}</p>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
            <div class="flex items-center text-gray-600">
              <i class="fas fa-rupee-sign mr-2 text-purple-600"></i>
              <span>{{ req.budget }} {{ req.budget_type }}</span>
            </div>
            <div v-if="req.service_type" class="flex items-center text-gray-600">
              <i class="fas fa-briefcase mr-2 text-purple-600"></i>
              <span>{{ req.service_type }}</span>
            </div>
            <div v-if="req.availability" class="flex items-center text-gray-600">
              <i class="fas fa-clock mr-2 text-purple-600"></i>
              <span>{{ req.availability }}</span>
            </div>
            <div v-if="req.meeting_options" class="flex items-center text-gray-600">
              <i class="fas fa-handshake mr-2 text-purple-600"></i>
              <span>{{ Array.isArray(req.meeting_options) ? req.meeting_options.join(', ') : req.meeting_options }}</span>
            </div>
            <div v-if="req.gender_preference" class="flex items-center text-gray-600">
              <i class="fas fa-user mr-2 text-purple-600"></i>
              <span>{{ req.gender_preference }}</span>
            </div>
            <div class="flex items-center text-gray-600">
              <i class="fas fa-calendar mr-2 text-purple-600"></i>
              <span>{{ formatDate(req.created_at) }}</span>
            </div>
          </div>
          
          <div v-if="req.languages && req.languages.length" class="mt-4 pt-4 border-t">
            <p class="text-sm text-gray-600 mb-2"><i class="fas fa-language mr-1"></i>Languages:</p>
            <div class="flex flex-wrap gap-2">
              <span v-for="lang in req.languages" :key="lang" 
                    class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                {{ lang }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="requirements.length === 0" class="bg-white rounded-xl shadow-md p-12 text-center">
          <i class="fas fa-clipboard-list text-gray-300 text-6xl mb-4"></i>
          <h3 class="text-xl font-semibold text-gray-700 mb-2">No Requirements Yet</h3>
          <p class="text-gray-600 mb-6">Start by posting your first tutor request</p>
          <router-link to="/student/request-tutor" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg inline-block transition">
            <i class="fas fa-plus mr-2"></i>Request a Tutor
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from '../bootstrap';

export default {
  name: 'RequirementsList',
  setup() {
    const router = useRouter();
    const requirements = ref([]);
    const loading = ref(true);
    const error = ref('');

    const fetchRequirements = async () => {
      loading.value = true;
      try {
        const response = await axios.get('/api/student/requirements');
        requirements.value = response.data.requirements || [];
      } catch (err) {
        console.error('Error loading requirements:', err);
        error.value = 'Failed to load requirements';
      } finally {
        loading.value = false;
      }
    };

    const editRequirement = (id) => {
      router.push(`/student/requirements/${id}/edit`);
    };

    const closeRequirement = async (id) => {
      if (!confirm('Are you sure you want to close this requirement?')) {
        return;
      }
      
      try {
        await axios.post(`/api/student/requirements/${id}/close`);
        // Refresh the list
        fetchRequirements();
      } catch (err) {
        console.error('Error closing requirement:', err);
        alert('Failed to close requirement');
      }
    };

    const formatDate = (date) => {
      if (!date) return '';
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    };

    onMounted(() => {
      fetchRequirements();
    });

    return { 
      requirements, 
      loading, 
      error, 
      editRequirement,
      closeRequirement,
      formatDate
    };
  }
};
</script>
