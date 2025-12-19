<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
      <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
          <i class="fas fa-star mr-2 text-yellow-500"></i>My Reviews
        </h1>
        <p class="text-gray-600">Reviews you've given to tutors</p>
      </div>

      <div class="bg-white rounded-xl shadow-md p-12 text-center">
        <i class="fas fa-star text-gray-300 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Reviews Yet</h3>
        <p class="text-gray-600">Book a tutor to leave your first review</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StudentReviews'
};
</script>
