<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
      <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
              <i class="fas fa-clipboard-list mr-2 text-purple-600"></i>My Requirements
            </h1>
            <p class="text-gray-600">View and manage your tutor requests</p>
          </div>
          <router-link to="/student/request-tutor" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition inline-flex items-center gap-2 whitespace-nowrap w-full md:w-auto justify-center">
            <i class="fas fa-plus"></i>Request a Tutor
          </router-link>
        </div>
      </div>

      <!-- Refund Confirmation Modal -->
      <div v-if="showRefundModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
          <div class="text-center mb-4">
            <i class="fas fa-coins text-5xl text-yellow-500 mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Refund Available</h2>
          </div>
          <p class="text-gray-700 mb-4">You will receive a refund of <strong class="text-lg text-green-600">{{ refundAmount }} coins</strong> since no teacher has unlocked your enquiry yet.</p>
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-blue-800"><i class="fas fa-info-circle mr-2"></i>This is the amount you paid when posting this enquiry.</p>
          </div>
          <div class="flex gap-3">
            <button @click="cancelRefund" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
              Cancel
            </button>
            <button @click="confirmRefund" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition font-medium">
              <i class="fas fa-check mr-2"></i>Confirm Refund
            </button>
          </div>
        </div>
      </div>

      <!-- Interested Teachers Modal -->
      <div v-if="showInterestedModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full p-6 max-h-96 overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
              <i class="fas fa-users mr-2 text-blue-600"></i>Interested Tutors
            </h2>
            <button @click="closeInterestedModal" class="text-gray-500 hover:text-gray-700">
              <i class="fas fa-times text-2xl"></i>
            </button>
          </div>
          
          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
            <p class="text-sm text-yellow-800">
              <i class="fas fa-info-circle mr-2"></i>
              <strong>Note:</strong> Approaching a tutor will cost <strong>10 coins</strong>. You'll be able to see their contact details after approaching.
            </p>
          </div>

          <div v-if="interestedTeachers.length === 0" class="text-center py-8">
            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
            <p class="text-gray-600">No tutors have expressed interest yet.</p>
          </div>

          <div v-else class="space-y-4">
            <p class="text-sm text-gray-600 mb-4">
              <strong>{{ interestedTeachers.length }}</strong> tutor{{ interestedTeachers.length > 1 ? 's' : '' }} want{{ interestedTeachers.length > 1 ? '' : 's' }} to work with you.
            </p>
            
            <div v-for="teacher in interestedTeachers" :key="teacher.id" class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
              <div class="flex items-start justify-between mb-3">
                <div class="flex items-start gap-3 flex-1">
                  <img v-if="teacher.photo" :src="teacher.photo" :alt="teacher.name" class="w-12 h-12 rounded-full object-cover">
                  <div v-else class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-user text-blue-600"></i>
                  </div>
                  <div class="flex-1">
                    <h3 class="font-bold text-gray-800">{{ teacher.name }}</h3>
                    
                    <!-- Show contact details if approached -->
                    <div v-if="teacher.email || teacher.phone" class="mt-2 space-y-1 bg-green-50 p-2 rounded">
                      <p v-if="teacher.email" class="text-sm text-gray-800 font-medium">
                        <i class="fas fa-envelope mr-1 text-blue-600"></i>{{ teacher.email }}
                      </p>
                      <p v-if="teacher.phone" class="text-sm text-gray-800 font-medium">
                        <i class="fas fa-phone mr-1 text-green-600"></i>{{ teacher.phone }}
                      </p>
                    </div>
                    
                    <!-- Show message if not approached yet -->
                    <div v-else class="mt-2 bg-gray-50 p-2 rounded">
                      <p class="text-xs text-gray-500 italic">
                        <i class="fas fa-lock mr-1"></i>Contact details will be shown after approaching
                      </p>
                    </div>
                    
                    <div class="flex items-center gap-3 mt-2">
                      <span v-if="teacher.rating" class="text-sm">
                        <i class="fas fa-star text-yellow-500"></i> {{ teacher.rating }}/5
                      </span>
                      <span v-if="teacher.hourly_rate" class="text-sm text-gray-600">₹{{ teacher.hourly_rate }}/hr</span>
                    </div>
                  </div>
                </div>
                <div>
                  <button 
                    v-if="!teacher.has_approached"
                    @click="selectTeacher(teacher.id)"
                    :disabled="approachLoading"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition disabled:bg-gray-400">
                    <i class="fas fa-check-circle mr-1"></i>{{ approachLoading ? 'Processing...' : 'Approach (10 coins)' }}
                  </button>
                  <div v-else class="px-4 py-2 bg-green-100 text-green-700 rounded-lg font-medium">
                    <i class="fas fa-check-circle mr-1"></i>Approached
                  </div>
                </div>
              </div>
              
              <p v-if="teacher.bio" class="text-sm text-gray-700 mb-3">{{ teacher.bio }}</p>
              
              <div v-if="teacher.interested_at" class="text-xs text-gray-500">
                <i class="fas fa-clock mr-1"></i>Interested on {{ formatDate(teacher.interested_at) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Requirements List -->
      <div class="space-y-4">
        <div v-for="req in requirements" :key="req.id" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
          <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4 mb-4">
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
              <div class="flex items-center gap-2 mt-2 text-sm">
                <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 font-semibold">
                  {{ req.current_leads || 0 }}/{{ req.max_leads || 0 }} tutors
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold"
                      :class="req.lead_status === 'full' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700'">
                  {{ req.lead_status_label || req.lead_status || 'Open' }}
                </span>
              </div>
            </div>
            <div class="flex flex-col items-start lg:items-end gap-2">
              
              <div class="flex flex-wrap gap-2">
                <button v-if="req.current_leads > 0" 
                        @click="openInterestedModal(req.id)" 
                        class="px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition whitespace-nowrap w-full sm:w-auto">
                  <i class="fas fa-eye mr-1"></i>View Tutors
                </button>
                <button v-if="req.status === 'active' && req.current_leads === 0" 
                        @click="openRefundModal(req.id)" 
                        class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition whitespace-nowrap w-full sm:w-auto">
                  <i class="fas fa-coins mr-1"></i>Get Refund
                </button>
                <button v-if="req.status === 'active' && req.current_leads === 0" @click="closeRequirement(req.id)" 
                        class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium transition w-full sm:w-auto">
                  <i class="fas fa-times-circle mr-1"></i>Close
                </button>
                <button @click="viewRequirement(req.id)" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition w-full sm:w-auto">
                  <i class="fas fa-eye mr-1"></i>Details
                </button>
                <button v-if="req.current_leads === 0" @click="editRequirement(req.id)" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition w-full sm:w-auto">
                  <i class="fas fa-edit mr-1"></i>Edit
                </button>
              </div>
            </div>
          </div>
          
          <p v-if="req.details" class="text-gray-700 mb-4">{{ req.details }}</p>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
            <div class="flex items-center text-gray-600">
              <i class="fas fa-rupee-sign mr-2 text-purple-600"></i>
              <span>{{ req.budget_display || (req.budget + ' ' + (req.budget_type_label || req.budget_type)) }}</span>
            </div>
            <div v-if="req.service_type" class="flex items-center text-gray-600">
              <i class="fas fa-briefcase mr-2 text-purple-600"></i>
              <span>{{ req.service_type_label || req.service_type }}</span>
            </div>
            <div v-if="req.availability" class="flex items-center text-gray-600">
              <i class="fas fa-clock mr-2 text-purple-600"></i>
              <span>{{ req.availability_label || req.availability }}</span>
            </div>
            <div v-if="req.meeting_options || req.meeting_options_labels" class="flex items-center text-gray-600">
              <i class="fas fa-handshake mr-2 text-purple-600"></i>
              <span>{{ req.meeting_options_labels ? req.meeting_options_labels.join(', ') : (Array.isArray(req.meeting_options) ? req.meeting_options.join(', ') : req.meeting_options) }}</span>
            </div>
            <div v-if="req.gender_preference" class="flex items-center text-gray-600">
              <i class="fas fa-user mr-2 text-purple-600"></i>
              <span>{{ req.gender_preference_label || req.gender_preference }}</span>
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

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="bg-white rounded-xl shadow-md p-4 mt-6">
          <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="text-sm text-gray-600">
              Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} requirements
            </div>
            <div class="flex items-center gap-2">
              <button 
                @click="changePage(pagination.current_page - 1)" 
                :disabled="pagination.current_page === 1"
                class="px-4 py-2 rounded-lg border transition"
                :class="pagination.current_page === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-50'">
                <i class="fas fa-chevron-left"></i>
              </button>
              
              <template v-for="page in visiblePages" :key="page">
                <button 
                  v-if="page !== '...'"
                  @click="changePage(page)"
                  class="px-4 py-2 rounded-lg transition"
                  :class="page === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border hover:bg-gray-50'">
                  {{ page }}
                </button>
                <span v-else class="px-2 text-gray-400">...</span>
              </template>
              
              <button 
                @click="changePage(pagination.current_page + 1)" 
                :disabled="pagination.current_page === pagination.last_page"
                class="px-4 py-2 rounded-lg border transition"
                :class="pagination.current_page === pagination.last_page ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-50'">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from '../bootstrap';

export default {
  name: 'RequirementsList',
  setup() {
    const router = useRouter();
    const requirements = ref([]);
    const loading = ref(true);
    const error = ref('');
    const pagination = ref(null);
    
    // Refund modal state
    const showRefundModal = ref(false);
    const refundAmount = ref(0);
    const refundRequirementId = ref(null);
    
    // Interested teachers modal state
    const showInterestedModal = ref(false);
    const interestedTeachers = ref([]);
    const selectedRequirement = ref(null);
    const approachLoading = ref(false);

    const fetchRequirements = async (page = 1) => {
      loading.value = true;
      try {
        const response = await axios.get('/api/student/requirements', {
          params: { page }
        });
        
        // API returns paginated data at root level
        const paginatedData = response.data;
        
        // Extract requirements array from data property
        requirements.value = paginatedData.data || [];
        
        // Set pagination data
        pagination.value = {
          current_page: paginatedData.current_page,
          last_page: paginatedData.last_page,
          per_page: paginatedData.per_page,
          total: paginatedData.total,
          from: paginatedData.from,
          to: paginatedData.to
        };
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

    const viewRequirement = (id) => {
      router.push(`/student/requirement-details/${id}`);
    };

    const openRefundModal = (id) => {
      const req = requirements.value.find(r => r.id === id);
      console.log('Opening refund modal for requirement:', req);
      
      if (req && req.post_fee > 0) {
        refundAmount.value = req.post_fee;
        refundRequirementId.value = id;
        showRefundModal.value = true;
        console.log('Refund modal opened with amount:', req.post_fee);
      } else {
        console.log('Cannot open refund modal - conditions not met:', {
          requirement_found: !!req,
          post_fee: req?.post_fee,
          current_leads: req?.current_leads
        });
        alert('This requirement is not eligible for a refund.');
      }
    };

    const cancelRefund = () => {
      showRefundModal.value = false;
      refundAmount.value = 0;
      refundRequirementId.value = null;
    };

    const confirmRefund = async () => {
      try {
        const response = await axios.post(`/api/student/requirements/${refundRequirementId.value}/close`);
        
        const refundedCoins = refundAmount.value;
        showRefundModal.value = false;
        refundAmount.value = 0;
        refundRequirementId.value = null;
        
        // Show success message with refund details
        alert(`✅ Refund Successful!\n\n${refundedCoins} coins have been refunded to your wallet.\n\nYour requirement has been closed and removed from the list.\n\nCurrent balance: ${response.data.current_balance || 'Updated'} coins`);
        
        // Refresh requirements list
        await fetchRequirements(pagination.value?.current_page || 1);
      } catch (err) {
        console.error('Error processing refund:', err);
        showRefundModal.value = false;
        
        if (err.response?.data?.message) {
          alert(`❌ Refund Failed\n\n${err.response.data.message}`);
        } else {
          alert('❌ Failed to process refund. Please try again.');
        }
      }
    };

    const openInterestedModal = async (id) => {
      try {
        const response = await axios.get(`/api/student/requirements/${id}/interested-teachers`);
        interestedTeachers.value = response.data.teachers || [];
        selectedRequirement.value = requirements.value.find(r => r.id === id);
        showInterestedModal.value = true;
      } catch (err) {
        console.error('Error loading interested teachers:', err);
        alert('Failed to load interested teachers');
      }
    };

    const closeInterestedModal = () => {
      showInterestedModal.value = false;
      interestedTeachers.value = [];
      selectedRequirement.value = null;
    };

    const selectTeacher = async (teacherId) => {
      if (!selectedRequirement.value) return;
      
      // Confirm before approaching
      if (!confirm('Are you sure you want to approach this tutor?\n\nThis will cost 10 coins and you will receive their contact details.')) {
        return;
      }
      
      approachLoading.value = true;
      try {
        const response = await axios.post(`/api/student/requirements/${selectedRequirement.value.id}/approach-teacher`, {
          teacher_id: teacherId
        });
        
        console.log('Approach response:', response.data);
        
        // Show success message with coin deduction info
        alert(`✅ Success!\n\n${response.data.coins_deducted} coins deducted\n${response.data.message}\n\nCurrent balance: ${response.data.current_balance} coins`);
        
        // Reload interested teachers from database to show updated contact details
        const teachersResponse = await axios.get(`/api/student/requirements/${selectedRequirement.value.id}/interested-teachers`);
        interestedTeachers.value = teachersResponse.data.teachers || [];
        
        // Update selected requirement status
        const req = requirements.value.find(r => r.id === selectedRequirement.value.id);
        if (req) {
          req.status = 'approached';
          selectedRequirement.value = { ...req };
        }
        
        // Refresh main requirements list to update status
        await fetchRequirements(pagination.value?.current_page || 1);
      } catch (err) {
        console.error('Error approaching teacher:', err);
        if (err.response?.status === 402) {
          alert(`❌ Insufficient Coins\n\n${err.response.data.message}\n\nPlease purchase more coins to continue.`);
        } else if (err.response?.status === 422) {
          alert(`❌ Error\n\n${err.response?.data?.message || 'You have already approached this tutor.'}`);
        } else {
          alert(`❌ Error\n\n${err.response?.data?.message || 'Failed to approach teacher'}`);
        }
      } finally {
        approachLoading.value = false;
      }
    };

    const closeRequirement = async (id) => {
      if (!confirm('Are you sure you want to close this requirement?\n\nThis action cannot be undone.')) {
        return;
      }
      
      try {
        const response = await axios.post(`/api/student/requirements/${id}/close`);
        
        // Show success message
        if (response.data.refund_amount && response.data.refund_amount > 0) {
          alert(`✅ Requirement Closed!\n\n${response.data.refund_amount} coins have been refunded to your wallet.\n\nYour requirement has been removed from the list.\n\nCurrent balance: ${response.data.current_balance || 'Updated'} coins`);
        } else {
          alert('✅ Requirement closed successfully!\n\nYour requirement has been removed from the list.');
        }
        
        // Refresh requirements list
        await fetchRequirements(pagination.value?.current_page || 1);
      } catch (err) {
        console.error('Error closing requirement:', err);
        if (err.response?.data?.message) {
          alert(`❌ Failed to Close\n\n${err.response.data.message}`);
        } else {
          alert('❌ Failed to close requirement. Please try again.');
        }
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

    const changePage = (page) => {
      if (page < 1 || page > pagination.value.last_page) return;
      fetchRequirements(page);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const visiblePages = computed(() => {
      if (!pagination.value) return [];
      
      const current = pagination.value.current_page;
      const last = pagination.value.last_page;
      const pages = [];
      
      if (last <= 7) {
        // Show all pages if 7 or fewer
        for (let i = 1; i <= last; i++) {
          pages.push(i);
        }
      } else {
        // Always show first page
        pages.push(1);
        
        if (current > 3) {
          pages.push('...');
        }
        
        // Show pages around current
        const start = Math.max(2, current - 1);
        const end = Math.min(last - 1, current + 1);
        
        for (let i = start; i <= end; i++) {
          pages.push(i);
        }
        
        if (current < last - 2) {
          pages.push('...');
        }
        
        // Always show last page
        pages.push(last);
      }
      
      return pages;
    });

    onMounted(() => {
      fetchRequirements(1);
    });

    return { 
      requirements, 
      loading, 
      error,
      pagination,
      showRefundModal,
      refundAmount,
      showInterestedModal,
      interestedTeachers,
      selectedRequirement,
      approachLoading,
      editRequirement,
      viewRequirement,
      closeRequirement,
      formatDate,
      changePage,
      visiblePages,
      openRefundModal,
      cancelRefund,
      confirmRefund,
      openInterestedModal,
      closeInterestedModal,
      selectTeacher
    };
  }
};
</script>
