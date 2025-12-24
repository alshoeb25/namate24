<template>
  <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition">
    <div class="flex justify-between items-start gap-4">
      <div class="flex-1">
        <div class="flex flex-wrap items-center gap-2 mb-2">
          <span v-for="subject in localEnquiry.subjects || []" :key="subject.id"
                class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-semibold">
            {{ subject.name }}
          </span>
          <span v-if="!localEnquiry.subjects || !localEnquiry.subjects.length"
                class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">
            General Enquiry
          </span>
        </div>
        <h3 class="text-xl font-semibold text-gray-900">{{ localEnquiry.student_name || 'Student enquiry' }}</h3>
        <p class="text-sm text-gray-600 mt-1 flex items-center gap-2">
          <i class="fas fa-map-marker-alt text-red-500"></i>
          <span>{{ localEnquiry.city }}<span v-if="localEnquiry.area">, {{ localEnquiry.area }}</span></span>
          <span v-if="localEnquiry.level" class="text-gray-500">• {{ localEnquiry.level }}</span>
          <span v-if="localEnquiry.service_type" class="text-gray-500">• {{ localEnquiry.service_type }}</span>
        </p>
      </div>
      <div class="text-right">
        <span :class="['px-3 py-1 rounded-full text-xs font-semibold uppercase',
                       isFull ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700']">
          {{ isFull ? 'Lead Full' : 'Open' }}
        </span>
        <div class="mt-2 text-sm text-gray-500">{{ counterLabel }} tutors</div>
        <div class="mt-2 inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold">
          <i class="fas fa-coins"></i>
          <span>{{ localEnquiry.unlock_price || 0 }} coins to unlock</span>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 text-sm text-gray-700">
      <div class="flex items-center gap-2">
        <i class="fas fa-rupee-sign text-blue-600"></i>
        <span>{{ localEnquiry.budget }} {{ localEnquiry.budget_type }}</span>
      </div>
      <div class="flex items-center gap-2" v-if="localEnquiry.meeting_options">
        <i class="fas fa-handshake text-blue-600"></i>
        <span>{{ Array.isArray(localEnquiry.meeting_options) ? localEnquiry.meeting_options.join(', ') : localEnquiry.meeting_options }}</span>
      </div>
      <div class="flex items-center gap-2" v-if="localEnquiry.availability">
        <i class="fas fa-clock text-blue-600"></i>
        <span>{{ localEnquiry.availability }}</span>
      </div>
    </div>

    <div class="mt-4">
      <div class="flex items-center justify-between mb-2 text-sm font-medium text-gray-700">
        <span>Lead cap</span>
        <span>{{ counterLabel }}</span>
      </div>
      <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
        <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500" :style="{ width: progressWidth }"></div>
      </div>
    </div>

    <div class="mt-5 p-4 rounded-lg border" :class="hasUnlocked ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50'">
      <div class="flex items-center justify-between gap-2">
        <div class="text-sm font-semibold" :class="hasUnlocked ? 'text-green-700' : 'text-gray-800'">
          {{ hasUnlocked ? 'Contact unlocked' : 'Unlock to view contact' }}
        </div>
        <div class="text-xs text-gray-500">{{ hasUnlocked ? 'No extra charge' : unlockLabel }}</div>
      </div>

      <div v-if="hasUnlocked" class="mt-3 space-y-1 text-sm text-gray-800">
        <div class="flex items-center gap-2">
          <i class="fas fa-phone text-green-600"></i>
          <span>{{ localEnquiry.phone || 'Not shared' }}</span>
        </div>
        <div class="flex items-center gap-2" v-if="localEnquiry.alternate_phone">
          <i class="fas fa-phone text-green-600"></i>
          <span>{{ localEnquiry.alternate_phone }}</span>
        </div>
      </div>
      <div v-else class="mt-3">
        <button @click="unlock" :disabled="!canUnlock || unlocking" 
                class="px-4 py-2 rounded-lg text-white font-semibold shadow-sm"
                :class="(!canUnlock || unlocking) ? 'bg-gray-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'">
          <span v-if="unlocking">Unlocking...</span>
          <span v-else>{{ unlockLabel }}</span>
        </button>
        <p v-if="isFull" class="mt-2 text-xs text-red-600">Lead closed. Maximum tutors reached.</p>
      </div>

      <p v-if="errorMsg" class="mt-3 text-xs text-red-600">{{ errorMsg }}</p>
      <p v-if="successMsg" class="mt-3 text-xs text-green-700">{{ successMsg }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import axios from '../bootstrap';
import { useUserStore } from '../store';

const props = defineProps({
  enquiry: {
    type: Object,
    required: true,
  },
});

const userStore = useUserStore();
const localEnquiry = ref({ ...props.enquiry });
const unlocking = ref(false);
const errorMsg = ref('');
const successMsg = ref('');

watch(() => props.enquiry, (val) => {
  localEnquiry.value = { ...val };
}, { deep: true });

const isFull = computed(() => {
  return (localEnquiry.value.lead_status === 'full') || ((localEnquiry.value.current_leads || 0) >= (localEnquiry.value.max_leads || 0));
});

const hasUnlocked = computed(() => Boolean(localEnquiry.value.has_unlocked));
const canUnlock = computed(() => !hasUnlocked.value && !isFull.value);

const counterLabel = computed(() => `${localEnquiry.value.current_leads || 0}/${localEnquiry.value.max_leads || 0}`);
const progressWidth = computed(() => {
  const max = localEnquiry.value.max_leads || 1;
  const current = localEnquiry.value.current_leads || 0;
  return `${Math.min(100, (current / max) * 100)}%`;
});

const unlockLabel = computed(() => `Unlock Contact (${localEnquiry.value.unlock_price || 0} coins)`);

const unlock = async () => {
  if (!userStore.token) {
    errorMsg.value = 'Please log in as a tutor to unlock enquiries.';
    return;
  }

  unlocking.value = true;
  errorMsg.value = '';
  successMsg.value = '';
  try {
    const response = await axios.post(`/api/enquiries/${localEnquiry.value.id}/unlock`);
    localEnquiry.value = response.data.enquiry;
    successMsg.value = response.data.message;
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Unable to unlock this enquiry.';
  } finally {
    unlocking.value = false;
  }
};
</script>
