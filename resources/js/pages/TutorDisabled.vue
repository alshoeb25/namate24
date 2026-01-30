<template>
  <main class="min-h-[60vh] flex items-center justify-center px-4 py-16">
    <div class="max-w-xl w-full bg-white rounded-2xl shadow-lg border border-red-100 p-8">
      <div class="flex items-start gap-3 text-red-600">
        <i class="fas fa-ban text-xl mt-1"></i>
        <div>
          <h1 class="text-2xl font-semibold text-red-700">Your tutor profile is disabled</h1>
          <p class="text-sm text-red-600 mt-2" v-if="disabledReason">Reason: {{ disabledReason }}</p>
          <p class="text-sm text-gray-600 mt-2">Please contact support to enable your profile.</p>
        </div>
      </div>

      <div class="mt-6 flex flex-wrap gap-3">
        <a :href="mailtoLink" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
          <i class="fas fa-envelope"></i>
          Contact Support
        </a>
        <router-link to="/" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
          <i class="fas fa-home"></i>
          Go Home
        </router-link>
      </div>
    </div>
  </main>
</template>

<script>
import { computed } from 'vue';
import { useUserStore } from '../store';

export default {
  name: 'TutorDisabled',
  setup() {
    const userStore = useUserStore();
    const disabledReason = computed(() => userStore.user?.tutor?.disabled_reason || '');
    const mailtoLink = computed(() => {
      const email = import.meta?.env?.VITE_SUPPORT_EMAIL || 'support@namate24.com';
      const subject = encodeURIComponent('Tutor profile disabled');
      return `mailto:${email}?subject=${subject}`;
    });

    return { disabledReason, mailtoLink };
  },
};
</script>
