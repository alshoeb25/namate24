<template>
  <div v-if="show" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50" @click.self="closeModal">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden animate-fadeIn">
      <!-- Header -->
      <div class="bg-gradient-to-r p-6 text-white"
           :class="type === 'teacher' ? 'from-pink-500 to-purple-600' : 'from-blue-500 to-cyan-600'">
        <div class="flex items-center justify-between">
          <h2 class="text-2xl font-bold">
            <i class="mr-2" :class="type === 'teacher' ? 'fas fa-chalkboard-teacher' : 'fas fa-user-graduate'"></i>
            Enroll as {{ type === 'teacher' ? 'Teacher' : 'Student' }}
          </h2>
          <button @click="closeModal" class="text-white hover:text-gray-200">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="p-6">
        <div v-if="!enrolling && !success">
          <p class="text-gray-700 mb-6">
            {{ type === 'teacher' 
              ? 'Create your teaching profile and start connecting with students looking for tutors.'
              : 'Create your student profile and start finding the perfect tutors for your learning needs.' 
            }}
          </p>

          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-blue-900 mb-2">
              <i class="fas fa-info-circle mr-2"></i>What you'll get:
            </h3>
            <ul class="space-y-2 text-sm text-blue-800">
              <li v-if="type === 'teacher'">
                <i class="fas fa-check text-green-600 mr-2"></i>Access to tutor dashboard
              </li>
              <li v-if="type === 'teacher'">
                <i class="fas fa-check text-green-600 mr-2"></i>Create and manage your teaching profile
              </li>
              <li v-if="type === 'teacher'">
                <i class="fas fa-check text-green-600 mr-2"></i>Respond to student requirements
              </li>
              <li v-if="type === 'student'">
                <i class="fas fa-check text-green-600 mr-2"></i>Access to student dashboard
              </li>
              <li v-if="type === 'student'">
                <i class="fas fa-check text-green-600 mr-2"></i>Search and find tutors
              </li>
              <li v-if="type === 'student'">
                <i class="fas fa-check text-green-600 mr-2"></i>Post tutor requirements
              </li>
              <li>
                <i class="fas fa-check text-green-600 mr-2"></i>Connect with {{ type === 'teacher' ? 'students' : 'teachers' }}
              </li>
            </ul>
          </div>

          <p class="text-sm text-gray-600 mb-6">
            <i class="fas fa-shield-alt text-green-600 mr-2"></i>
            You'll keep your existing account and can switch between both roles anytime.
          </p>

          <button @click="confirmEnroll" 
                  class="w-full py-3 rounded-lg font-semibold text-white transition transform hover:scale-105"
                  :class="type === 'teacher' 
                    ? 'bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700'
                    : 'bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700'">
            <i class="fas fa-user-plus mr-2"></i>
            Enroll as {{ type === 'teacher' ? 'Teacher' : 'Student' }}
          </button>
        </div>

        <!-- Enrolling State -->
        <div v-if="enrolling" class="text-center py-8">
          <div class="animate-spin rounded-full h-16 w-16 border-b-2 mx-auto mb-4"
               :class="type === 'teacher' ? 'border-pink-600' : 'border-blue-600'"></div>
          <p class="text-gray-700 font-medium">Processing your enrollment...</p>
        </div>

        <!-- Success State -->
        <div v-if="success" class="text-center py-8">
          <div class="text-6xl mb-4">
            <i class="fas fa-check-circle" :class="type === 'teacher' ? 'text-pink-600' : 'text-blue-600'"></i>
          </div>
          <h3 class="text-2xl font-bold text-gray-800 mb-2">Enrollment Successful!</h3>
          <p class="text-gray-600 mb-6">
            You're now enrolled as a {{ type === 'teacher' ? 'teacher' : 'student' }}. 
            Redirecting to your dashboard...
          </p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
          <p class="text-red-700">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ error }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '../store';

export default {
  name: 'EnrollmentModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    type: {
      type: String,
      required: true,
      validator: (value) => ['teacher', 'student'].includes(value)
    }
  },
  emits: ['close'],
  setup(props, { emit }) {
    const router = useRouter();
    const userStore = useUserStore();
    const enrolling = ref(false);
    const success = ref(false);
    const error = ref('');

    const closeModal = () => {
      if (!enrolling.value) {
        emit('close');
      }
    };

    const confirmEnroll = async () => {
      enrolling.value = true;
      error.value = '';

      try {
        let response;
        if (props.type === 'teacher') {
          response = await userStore.enrollAsTeacher();
        } else {
          response = await userStore.enrollAsStudent();
        }

        success.value = true;
        
        // Redirect after 2 seconds
        setTimeout(() => {
          const destination = props.type === 'teacher' ? '/tutor/profile' : '/student/dashboard';
          router.push(destination);
          emit('close');
        }, 2000);
      } catch (err) {
        error.value = err.response?.data?.message || 'Enrollment failed. Please try again.';
        enrolling.value = false;
      }
    };

    return {
      enrolling,
      success,
      error,
      closeModal,
      confirmEnroll
    };
  }
};
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.2s ease-out;
}
</style>
