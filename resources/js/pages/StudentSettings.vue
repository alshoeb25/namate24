<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
      <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
          <i class="fas fa-cog mr-2 text-gray-600"></i>Settings
        </h1>
        <p class="text-gray-600">Manage your account preferences</p>
      </div>

      <!-- Settings Sections -->
      <div class="space-y-4">
        <!-- Notifications -->
        <div class="bg-white rounded-xl shadow-md p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-bell mr-2"></i>Notifications
          </h3>
          <div class="space-y-3">
            <label class="flex items-center justify-between">
              <span class="text-gray-700">Email Notifications</span>
              <input type="checkbox" class="toggle" checked>
            </label>
            <label class="flex items-center justify-between">
              <span class="text-gray-700">SMS Notifications</span>
              <input type="checkbox" class="toggle" checked>
            </label>
            <label class="flex items-center justify-between">
              <span class="text-gray-700">Push Notifications</span>
              <input type="checkbox" class="toggle">
            </label>
          </div>
        </div>

        <!-- Privacy -->
        <div class="bg-white rounded-xl shadow-md p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-lock mr-2"></i>Privacy
          </h3>
          <div class="space-y-3">
            <label class="flex items-center justify-between">
              <span class="text-gray-700">Show profile to tutors</span>
              <input type="checkbox" class="toggle" checked>
            </label>
            <label class="flex items-center justify-between">
              <span class="text-gray-700">Allow tutor recommendations</span>
              <input type="checkbox" class="toggle" checked>
            </label>
          </div>
        </div>

        <!-- Account Actions -->
        <div class="bg-white rounded-xl shadow-md p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-user-cog mr-2"></i>Account
          </h3>
          <button class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition">
            <i class="fas fa-trash mr-2"></i>Delete Account
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StudentSettings'
};
</script>
