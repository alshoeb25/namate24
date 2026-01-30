<template>
  <main class="min-h-[60vh] flex items-center justify-center px-4 py-16">
    <div class="max-w-xl w-full bg-white rounded-2xl shadow-lg border border-yellow-100 p-8">
      <div class="flex items-start gap-3 text-yellow-700">
        <i class="fas fa-exclamation-triangle text-xl mt-1"></i>
        <div>
          <h1 class="text-2xl font-semibold text-yellow-700">Your profile is not verified</h1>
          <p class="text-sm text-gray-600 mt-2">
            You can’t access student leads or requirement details until your tutor profile is verified.
          </p>
          <p class="text-sm text-gray-600 mt-2">
            Please complete your profile and submit required documents. Our team will review it shortly.
          </p>
        </div>
      </div>

      <div class="mt-6 flex flex-wrap gap-3">
        <router-link to="/tutor/profile" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-yellow-600 text-white hover:bg-yellow-700">
          <i class="fas fa-user-check"></i>
          Go to Profile
        </router-link>
        <router-link to="/tutor/documents" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
          <i class="fas fa-file-upload"></i>
          Upload Documents
        </router-link>
      </div>
    </div>
  </main>
</template>

<script>
export default {
  name: 'TutorNotApproved',
};
</script>
