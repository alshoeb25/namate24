<template>
  <div class="bg-gradient-to-br from-pink-50 to-purple-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
      <!-- Header -->
      <div class="text-center mb-8 sm:mb-12">
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-pink-600 mb-3 sm:mb-4 px-2">Contact Us</h1>
        <p class="text-base sm:text-lg text-gray-600 max-w-3xl mx-auto px-2">
          Get in touch with the right people at Namate24. We're here to help!
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 lg:gap-12">
        <!-- Left Column - Contact Info -->
        <div class="space-y-8">
          <!-- Introduction -->
          <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8">
            <p class="text-gray-700 mb-6">
              Excellence shouldn't be confused with quality, speed, or any one particular thing.
              It means delivering the outcome one is responsible for. Excellence shouldn't be confused
              with quality, speed, or any one particular thing. It means delivering the outcome
              one is responsible for.
            </p>

            <!-- Contact Details -->
            <div class="space-y-6">
              <!-- Email -->
              <div class="flex items-start space-x-4 transition-transform hover:-translate-y-1 duration-300">
                <div class="bg-pink-100 p-3 rounded-xl">
                  <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-800 mb-1 text-sm sm:text-base">Email Address</h3>
                  <a href="mailto:training@namate24academy.com"
                     class="text-pink-600 hover:text-pink-800 text-sm sm:text-base md:text-lg font-medium transition duration-300 break-all">
                    training@namate24academy.com
                  </a>
                </div>
              </div>

              <!-- Phone -->
              <div class="flex items-start space-x-4 transition-transform hover:-translate-y-1 duration-300">
                <div class="bg-pink-100 p-3 rounded-xl">
                  <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-800 mb-1 text-sm sm:text-base">Phone Number</h3>
                  <a href="tel:+916364224826"
                     class="text-pink-600 hover:text-pink-800 text-sm sm:text-base md:text-lg font-medium transition duration-300">
                    +91 63642 24826
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Contact Form Title -->
          <div class="bg-gradient-to-r from-pink-500 to-purple-600 rounded-2xl shadow-xl p-4 sm:p-6 md:p-8 text-white">
            <h2 class="text-xl sm:text-2xl font-bold mb-3 sm:mb-4">Get In Touch</h2>
            <p class="text-pink-100">
              Have a question or just want to say hi? We'd love to hear from you.
            </p>
          </div>
        </div>

        <!-- Right Column - Contact Form -->
        <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 md:p-8">
          <form @submit.prevent="submitForm" class="space-y-4 sm:space-y-6">
            <!-- User Type Dropdown - FIRST -->
            <div>
              <!-- Dynamic Text Based on Selection -->
              <div v-if="dynamicMessage" class="mt-4 p-4 bg-pink-50 rounded-lg border border-pink-200">
                <p class="text-gray-700">{{ dynamicMessage }}</p>
              </div>
              <label for="userType" class="block text-gray-700 font-medium mb-2">Select Your Role *</label>
              <select
                v-model="formData.user_type"
                id="userType"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition duration-300"
                :class="{ 'border-red-500': errors.user_type }"
              >
                <option value="">Select your role</option>
                <option value="tutor">Tutor</option>
                <option value="student">Student</option>
                <option value="organisation">Organisation</option>
              </select>
              <p v-if="errors.user_type" class="text-red-500 text-sm mt-1">{{ errors.user_type }}</p>

              
            </div>

            <!-- For Tutor/Student -->
            <template v-if="formData.user_type === 'tutor' || formData.user_type === 'student'">
              <!-- First Name Field -->
              <div>
                <label for="firstName" class="block text-gray-700 font-medium mb-2">First Name *</label>
                <input
                  v-model="formData.first_name"
                  type="text"
                  id="firstName"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition duration-300"
                  :class="{ 'border-red-500': errors.first_name }"
                  placeholder="Enter your first name"
                />
                <p v-if="errors.first_name" class="text-red-500 text-sm mt-1">{{ errors.first_name }}</p>
              </div>

              <!-- Last Name Field -->
              <div>
                <label for="lastName" class="block text-gray-700 font-medium mb-2">Last Name *</label>
                <input
                  v-model="formData.last_name"
                  type="text"
                  id="lastName"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition duration-300"
                  :class="{ 'border-red-500': errors.last_name }"
                  placeholder="Enter your last name"
                />
                <p v-if="errors.last_name" class="text-red-500 text-sm mt-1">{{ errors.last_name }}</p>
              </div>

              <!-- Email Field -->
              <div>
                <label for="email" class="block text-gray-700 font-medium mb-2">Email *</label>
                <input
                  v-model="formData.email"
                  type="email"
                  id="email"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition duration-300"
                  :class="{ 'border-red-500': errors.email }"
                  placeholder="Enter your email address"
                />
                <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
              </div>

              <!-- Phone Field -->
              <div>
                <label for="mobile" class="block text-gray-700 font-medium mb-2">Phone *</label>
                <input
                  v-model="formData.mobile"
                  type="tel"
                  id="mobile"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition duration-300"
                  :class="{ 'border-red-500': errors.mobile }"
                  placeholder="Enter your phone number"
                />
                <p v-if="errors.mobile" class="text-red-500 text-sm mt-1">{{ errors.mobile }}</p>
              </div>
            </template>

            <!-- For Organization -->
            <template v-if="formData.user_type === 'organisation'">
              <!-- Organization Name -->
              <div>
                <label for="organizationName" class="block text-gray-700 font-medium mb-2">Organization Name *</label>
                <input
                  v-model="formData.organization_name"
                  type="text"
                  id="organizationName"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition duration-300"
                  :class="{ 'border-red-500': errors.organization_name }"
                  placeholder="Enter your organization name"
                />
                <p v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</p>
              </div>

              <!-- Contact Person -->
              <div>
                <label for="contactPerson" class="block text-gray-700 font-medium mb-2">Contact Person *</label>
                <input
                  v-model="formData.contact_person"
                  type="text"
                  id="contactPerson"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition duration-300"
                  :class="{ 'border-red-500': errors.contact_person }"
                  placeholder="Enter contact person name"
                />
                <p v-if="errors.contact_person" class="text-red-500 text-sm mt-1">{{ errors.contact_person }}</p>
              </div>

              <!-- Contact Email -->
              <div>
                <label for="contactEmail" class="block text-gray-700 font-medium mb-2">Contact Email *</label>
                <input
                  v-model="formData.email"
                  type="email"
                  id="contactEmail"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition duration-300"
                  :class="{ 'border-red-500': errors.email }"
                  placeholder="Enter contact email"
                />
                <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
              </div>
            </template>

            <!-- Message Field (Optional) -->
            <div>
              <label for="message" class="block text-gray-700 font-medium mb-2">Message</label>
              <textarea
                v-model="formData.message"
                id="message"
                rows="5"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition duration-300"
                :class="{ 'border-red-500': errors.message }"
                placeholder="Please type your message here..."
              ></textarea>
              <p v-if="errors.message" class="text-red-500 text-sm mt-1">{{ errors.message }}</p>
            </div>

            <!-- Success Message -->
            <div v-if="successMessage" class="p-4 bg-green-50 border border-green-200 rounded-lg">
              <p class="text-green-700">{{ successMessage }}</p>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-red-700">{{ errorMessage }}</p>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="isSubmitting"
              class="w-full bg-gradient-to-r from-pink-500 to-purple-600 text-white font-bold py-4 px-6 rounded-lg hover:from-pink-600 hover:to-purple-700 transition duration-300 transform hover:-translate-y-1 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="isSubmitting">Sending...</span>
              <span v-else>Send Your Message</span>
            </button>
          </form>
        </div>
      </div>

      <!-- Footer Note -->
      <div class="mt-8 sm:mt-12 text-center px-2">
        <p class="text-sm sm:text-base text-gray-600">
          We typically respond within 24 hours during business days.
        </p>
        <p class="text-gray-500 text-xs sm:text-sm mt-2">
          © 2023 Namate24. All rights reserved.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from '../bootstrap';

const formData = ref({
  user_type: '',
  first_name: '',
  last_name: '',
  organization_name: '',
  contact_person: '',
  email: '',
  mobile: '',
  message: '',
});

const errors = ref({});
const isSubmitting = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const userTypeMessages = {
  tutor: "Welcome tutors! We're excited to help you connect with students and grow your tutoring business. Please share any specific requirements or questions about our platform.",
  student: "Hello students! We're here to help you find the perfect tutor for your learning journey. Let us know what subjects or skills you want to learn.",
  organisation: "Greetings to our organizational partners! We offer custom solutions for educational institutions. Please share details about your requirements."
};

const dynamicMessage = computed(() => {
  return userTypeMessages[formData.value.user_type] || '';
});

const submitForm = async () => {
  // Reset messages and errors
  errors.value = {};
  successMessage.value = '';
  errorMessage.value = '';

  // Basic validation
  if (!formData.value.user_type) {
    errorMessage.value = 'Please select a role.';
    return;
  }

  // Check required fields based on user type
  if (formData.value.user_type === 'tutor' || formData.value.user_type === 'student') {
    if (!formData.value.first_name || !formData.value.last_name || !formData.value.email || !formData.value.mobile) {
      errorMessage.value = 'Please fill all required fields.';
      return;
    }
  } else if (formData.value.user_type === 'organisation') {
    if (!formData.value.organization_name || !formData.value.contact_person || !formData.value.email) {
      errorMessage.value = 'Please fill all required fields.';
      return;
    }
  }

  // Email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(formData.value.email)) {
    errors.value.email = 'Please enter a valid email address.';
    errorMessage.value = 'Please check your email address.';
    return;
  }

  // Mobile validation (only for tutor/student)
  if (formData.value.user_type !== 'organisation' && formData.value.mobile) {
    const mobileRegex = /^[0-9\-\+\s\(\)]{7,20}$/;
    if (!mobileRegex.test(formData.value.mobile)) {
      errors.value.mobile = 'Please enter a valid phone number.';
      errorMessage.value = 'Please check your phone number.';
      return;
    }
  }

  isSubmitting.value = true;

  try {
    const payload = { user_type: formData.value.user_type };

    if (formData.value.user_type === 'tutor' || formData.value.user_type === 'student') {
      payload.first_name = formData.value.first_name;
      payload.last_name = formData.value.last_name;
      payload.email = formData.value.email;
      payload.mobile = formData.value.mobile;
    } else if (formData.value.user_type === 'organisation') {
      payload.organization_name = formData.value.organization_name;
      payload.contact_person = formData.value.contact_person;
      payload.email = formData.value.email;
    }

    if (formData.value.message) {
      payload.message = formData.value.message;
    }

    const response = await axios.post('/api/contact-submissions', payload);
    
    successMessage.value = 'Thank you for your message! We will get back to you soon.';
    
    // Reset form
    formData.value = {
      user_type: '',
      first_name: '',
      last_name: '',
      organization_name: '',
      contact_person: '',
      email: '',
      mobile: '',
      message: '',
    };
  } catch (error) {
    console.error('Contact form error:', error);
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
      errorMessage.value = 'Please check the highlighted fields.';
    } else if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'An error occurred. Please try again later.';
    }
  } finally {
    isSubmitting.value = false;
  }
};
</script>
