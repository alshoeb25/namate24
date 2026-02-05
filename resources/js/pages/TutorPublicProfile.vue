<template>
  <div v-if="loading" class="flex items-center justify-center min-h-screen">
    <div class="text-xl text-gray-600">Loading...</div>
  </div>

  <div v-else class="max-w-7xl mx-auto px-4 py-8">
    <div class="text-center mb-8">
      <h1 class="text-4xl font-normal text-gray-700 mb-2">
        <span>{{ profile?.user?.name || profile?.name || '--' }}</span>
        <span class="text-gray-500 font-light ml-2">Teaching</span>
      </h1>
      <div class="flex items-center justify-center text-gray-500 text-lg">
        <i class="fas fa-star mr-2 text-pink-600"></i>
        <span>{{ ratingSummaryText }}</span>
      </div>
    </div>

    <main class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <section class="lg:col-span-2 space-y-6">

        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-gray-700 mb-4">Teaching Overview</h2>
          <p class="text-gray-600 mb-6 leading-relaxed whitespace-pre-line">
            {{ profile?.about || profile?.headline || 'No overview added.' }}
          </p>

          <div v-if="profile?.teaching_methodology" class="space-y-4">
            <h3 class="font-semibold text-gray-700">Teaching Methodology</h3>
            <p class="text-gray-600 pl-4 whitespace-pre-line">{{ profile.teaching_methodology }}</p>
          </div>

          <div v-if="profile?.speciality || profile?.strength" class="space-y-4 mt-4">
            <div v-if="profile?.speciality">
              <h4 class="font-medium text-gray-700">Speciality</h4>
              <p class="text-gray-600 pl-4">{{ profile.speciality }}</p>
            </div>
            <div v-if="profile?.strength">
              <h4 class="font-medium text-gray-700 mt-4">Strength</h4>
              <p class="text-gray-600 pl-4">{{ profile.strength }}</p>
            </div>
          </div>
        </div>

        <!-- Introduction Video -->
        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-purple-600 mb-4 flex items-center">
            <i class="fas fa-video mr-3"></i>
            Introduction Video
          </h2>
          
          <div v-if="approvedVideoUrl">
            <div class="flex items-center justify-between gap-4 flex-wrap">
              <div>
                <p v-if="profile?.video_title" class="text-sm text-gray-600 mb-1">{{ profile.video_title }}</p>
                <p class="text-xs text-green-700 font-semibold">✓ Approved</p>
              </div>
              <a :href="approvedVideoUrl" target="_blank" rel="noopener noreferrer"
                 class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium">
                <i class="fas fa-external-link-alt mr-2"></i>
                Open Video
              </a>
            </div>
            <p v-if="profile?.youtube_intro_url" class="text-sm text-gray-500 mt-3 break-all">
              <i class="fab fa-youtube mr-1"></i>
              {{ profile.youtube_intro_url }}
            </p>
          </div>
          
          <div v-else class="text-gray-600">
            <p>No video found.</p>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-teal-600 mb-4 flex items-center">
            <i class="fas fa-graduation-cap mr-3"></i>
            Subjects
          </h2>
          <div v-if="!profile?.subjects || profile.subjects.length === 0" class="text-gray-600">
            <p>No subjects added.</p>
          </div>
          <div v-else class="space-y-3 text-gray-600">
            <p v-for="subject in profile.subjects" :key="subject.id" class="hover:text-teal-600 cursor-pointer transition-colors">
              {{ subject.name }}
              <span v-if="subject.pivot?.from_level_id || subject.pivot?.to_level_id" class="ml-1">
                ({{ getLevelLabel(subject) }})
              </span>
            </p>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-blue-600 mb-4 flex items-center">
            <i class="fas fa-briefcase mr-3"></i>
            Experience
          </h2>
          <div v-if="!profile?.experiences || profile.experiences.length === 0" class="text-gray-600">
            <p>No experience mentioned.</p>
          </div>
          <div v-else class="space-y-4">
            <div v-for="(exp, index) in profile.experiences" :key="index" class="border-l-2 border-blue-500 pl-4">
              <p class="font-medium text-gray-700">{{ exp.company || exp.organization || exp.institute }}</p>
              <p class="text-sm text-gray-600">{{ exp.position || exp.role || exp.designation }}</p>
              <p class="text-sm text-gray-500">
                {{ exp.from_year }} - {{ exp.to_year || 'Present' }}
                <span v-if="exp.current" class="ml-2 text-green-600">(Current)</span>
              </p>
              <p v-if="exp.description" class="text-sm mt-1 text-gray-600">{{ exp.description }}</p>
            </div>

            <div v-if="profile?.experience_years || profile?.experience_total_years" class="bg-blue-50 p-3 rounded-lg mt-4">
              <div class="flex flex-wrap gap-4 text-sm text-gray-700">
                <div v-if="profile?.experience_total_years">
                  <span class="font-semibold">Total:</span> {{ profile.experience_total_years }} years
                </div>
                <div v-if="profile?.experience_teaching_years">
                  <span class="font-semibold">Teaching:</span> {{ profile.experience_teaching_years }} years
                </div>
                <div v-if="profile?.experience_online_years">
                  <span class="font-semibold">Online:</span> {{ profile.experience_online_years }} years
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-teal-600 mb-4 flex items-center">
            <i class="fas fa-user-graduate mr-3"></i>
            Education
          </h2>
          <div v-if="!profile?.educations || profile.educations.length === 0" class="text-gray-600">
            <p>No education added.</p>
          </div>
          <div v-else class="space-y-4 text-gray-600">
            <div v-for="(edu, index) in profile.educations" :key="index">
              <p>
                <strong>{{ edu.degree || edu.degree_name }}</strong>
                <span v-if="edu.degree_type"> ({{ edu.degree_type }})</span>
              </p>
              <p class="text-sm">
                ({{ edu.from_year }}—{{ edu.to_year || 'Present' }})
                <span v-if="edu.current" class="text-green-600">(Pursuing)</span>
                from {{ edu.institute || edu.institute_name }}
              </p>
              <p v-if="edu.specialization" class="text-sm mt-1">
                <span class="font-medium">Specialization:</span> {{ edu.specialization }}
              </p>
              <p v-if="edu.description" class="text-sm mt-1">{{ edu.description }}</p>
            </div>
          </div>
        </div>

        <div v-if="profile?.courses && profile.courses.length > 0" class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-purple-600 mb-4 flex items-center">
            <i class="fas a-certificate mr-3"></i>
            Courses & Certifications
          </h2>
          <div class="space-y-3 text-gray-600">
            <div v-for="(course, index) in profile.courses" :key="index" class="bg-gray-50 p-3 rounded">
              <p class="font-medium">{{ course.name || course.title }}</p>
              <p v-if="course.provider" class="text-sm">{{ course.provider }}</p>
              <p v-if="course.year" class="text-sm text-gray-500">Completed: {{ course.year }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-blue-600 mb-4 flex items-center">
            <i class="fas fa-wallet mr-3"></i>
            Fee details
          </h2>
          <div v-if="!profile?.price_per_hour && !profile?.min_fee && !profile?.max_fee" class="text-gray-600">
            <p>Fee not set.</p>
          </div>
          <div v-else class="text-gray-600">
            <p v-if="profile?.price_per_hour">
              ₹{{ profile.price_per_hour }}/hour
            </p>
            <p v-else-if="profile?.min_fee || profile?.max_fee">
              ₹{{ profile.min_fee || 0 }}—{{ profile.max_fee || 0 }}/hour
              <span v-if="profile?.charge_type" class="text-sm"> ({{ profile.charge_type }})</span>
            </p>
            <p v-if="profile?.fee_notes" class="text-sm mt-2">
              <span class="font-medium">Note:</span> {{ profile.fee_notes }}
            </p>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6">
          <h2 class="text-xl font-semibold text-teal-600 mb-4 flex items-center">
            <i class="fas fa-thumbs-up mr-3"></i>
            Reviews
          </h2>
          <div v-if="(ratingSummary?.total_reviews && ratingSummary.total_reviews > 0) || (approvedReviews && approvedReviews.length > 0)" class="space-y-4">
            <div class="bg-yellow-50 p-4 rounded-lg">
              <div class="flex items-center gap-4">
                <div class="text-4xl font-bold text-pink-600">{{ ratingSummary?.average ?? 'N/A' }}</div>
                <div>
                  <div class="flex items-center gap-1 mb-1">
                    <span v-for="n in 5" :key="n" :class="n <= Math.round(ratingSummary?.average || 0) ? 'text-pink-600' : 'text-gray-300'">
                      {{ n <= Math.round(ratingSummary?.average || 0) ? '★' : '☆' }}
                    </span>
                  </div>
                  <p class="text-sm text-gray-600">Based on {{ ratingSummary?.total_reviews || 0 }} reviews</p>
                </div>
              </div>
            </div>

            <div v-if="approvedReviews && approvedReviews.length > 0" class="space-y-3">
              <div v-for="(review, index) in approvedReviews" :key="index" class="border rounded-lg p-4 bg-gray-50">
                <div class="flex items-start justify-between mb-2">
                  <div>
                    <p class="font-medium">{{ review.name || 'Anonymous' }}</p>
                    <div class="flex items-center gap-1">
                      <span v-for="n in 5" :key="n" :class="n <= review.rating ? 'text-pink-600' : 'text-gray-300'" class="text-sm">
                        {{ n <= review.rating ? '★' : '☆' }}
                      </span>
                    </div>
                  </div>
                  <p class="text-sm text-gray-500">{{ review.date ? formatDate(review.date) : formatDate(review.created_at) }}</p>
                </div>
                <p class="text-gray-600 text-sm">{{ review.comment }}</p>
              </div>
            </div>
          </div>
          <p v-else class="text-gray-600">
            No reviews yet. Please log in to leave a review.
          </p>
        </div>

       
      </section>

      <aside class="lg:col-span-1">
        <div class="bg-white rounded-lg p-6 mb-6 flex flex-col items-center">
          <div class="relative w-56 h-56 mb-4">
            <div class="w-full h-full bg-gray-500 rounded-full flex items-end justify-center overflow-hidden">
              <img v-if="profile?.photo_url || profile?.user?.avatar_url" :src="profile.photo_url || profile.user.avatar_url" :alt="profile?.user?.name || 'Tutor'" class="w-full h-full object-cover">
              <i v-else class="fas fa-user text-gray-400 text-9xl mb-4"></i>
            </div>
          </div>
          <div class="text-center">
            <p class="text-lg font-semibold text-gray-800">{{ profile?.user?.name || '--' }}</p>
            <p class="text-sm text-gray-500">{{ profile?.headline || 'Tutor' }}</p>
          </div>
        </div>

        <div class="bg-gray-100 rounded-lg p-6">
          <!-- ACTION BUTTONS - Only show for logged in students viewing other tutors -->
          <div v-if="isLoggedInStudent && !isOwnProfile" class="grid grid-cols-2 gap-3 mb-6">
            <button 
              @click="openContactModal"
              :disabled="hasContactAccess"
              :class="hasContactAccess ? 'bg-gray-400' : 'bg-blue-500 hover:bg-blue-600'"
              class="text-white py-2.5 px-4 rounded text-sm font-medium flex items-center justify-center transition">
              <i class="fas fa-phone mr-2"></i>
              {{ hasContactAccess ? 'Contact Unlocked' : 'Contact' }}
            </button>
            <button 
              @click="openReviewModal"
              :disabled="!canReview"
              :class="canReview ? 'bg-orange-500 hover:bg-orange-600' : 'bg-gray-400'"
              class="text-white py-2.5 px-4 rounded text-sm font-medium flex items-center justify-center transition">
              <i class="far fa-star mr-2"></i>Review
            </button>
          </div>
          
          <!-- Message for tutors viewing their own profile -->
          <div v-if="isOwnProfile" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-blue-800 text-sm text-center">
              <i class="fas fa-info-circle mr-2"></i>
              This is your profile. Use the "Edit Profile" button to make changes.
            </p>
          </div>
          
          <!-- Login prompt for guests -->
          <div v-if="!isLoggedIn" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-yellow-800 text-sm text-center mb-3">
              <i class="fas fa-lock mr-2"></i>
              Please log in to contact this tutor or leave a review
            </p>
            <router-link :to="{ name: 'login', query: { redirect: $route.fullPath } }"
                         class="block w-full bg-blue-600 text-white text-center py-2 rounded hover:bg-blue-700 transition">
              Log In
            </router-link>
          </div>

          <div class="space-y-4 text-sm">
            <!-- Contact Information - Only show if access granted -->
            <div v-if="hasContactAccess" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
              <h3 class="font-semibold text-green-800 mb-3 flex items-center">
                <i class="fas fa-unlock mr-2"></i>Contact Details
              </h3>
              <div class="space-y-2 text-gray-700">
                <div v-if="profile?.user?.phone" class="flex items-center">
                  <i class="fas fa-phone text-green-600 w-5 flex-shrink-0"></i>
                  <span class="ml-3 font-medium">{{ profile.user.phone }}</span>
                </div>
                <div v-if="profile?.user?.email" class="flex items-center">
                  <i class="fas fa-envelope text-green-600 w-5 flex-shrink-0"></i>
                  <span class="ml-3">{{ profile.user.email }}</span>
                </div>
                <div v-if="profile?.whatsapp" class="flex items-center">
                  <i class="fab fa-whatsapp text-green-600 w-5 flex-shrink-0"></i>
                  <span class="ml-3">{{ profile.whatsapp }}</span>
                </div>
              </div>
            </div>

            <div class="flex items-start">
              <i class="fas fa-map-marker-alt text-gray-600 w-5 mt-1 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3">{{ getLocation() }}</span>
            </div>

            <div class="flex items-center">
              <i class="fas fa-car text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Can travel:</span> {{ getTravelInfo() }}</span>
            </div>

            <div class="flex items-center">
              <i class="fas fa-chalkboard-teacher text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Total Teaching exp:</span> {{ getTotalExperience() }}</span>
            </div>

            <div class="flex items-center">
              <i class="fas fa-wifi text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Teaches online:</span> {{ profile?.online_available ? 'Yes' : 'No' }}</span>
            </div>

            <div v-if="profile?.experience_online_years" class="flex items-center">
              <i class="fas fa-wifi text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Online Teaching exp:</span> {{ profile.experience_online_years }} yrs.</span>
            </div>

            <div class="flex items-center">
              <i class="fas fa-home text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Teaches at student's home:</span> {{ isHomeTuitionAvailable() }}</span>
            </div>

            <div class="flex items-center">
              <i class="fas fa-book text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Homework Help:</span> {{ profile?.helps_homework ? 'Yes' : 'No' }}</span>
            </div>

            <div class="flex items-center">
              <i class="fas fa-venus-mars text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Gender:</span> {{ profile?.gender || 'Not specified' }}</span>
            </div>

            <div class="flex items-center">
              <i class="fas fa-briefcase text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Works as:</span> {{ getWorksAs() }}</span>
            </div>

            <div v-if="profile?.languages && profile.languages.length > 0" class="flex items-center">
              <i class="fas fa-language text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3"><span class="font-semibold">Languages:</span> {{ profile.languages.join(', ') }}</span>
            </div>

            <div v-if="profile?.has_digital_pen" class="flex items-center">
              <i class="fas fa-pen text-gray-600 w-5 flex-shrink-0"></i>
              <span class="text-gray-700 ml-3">Has Digital Pen</span>
            </div>
          </div>
        </div>
      </aside>
    </main>

    <!-- Contact Terms & Conditions Modal -->
    <div v-if="showContactModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Unlock Contact Details</h2>
            <button @click="closeContactModal" class="text-gray-500 hover:text-gray-700">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex items-center">
              <i class="fas fa-coins text-yellow-600 text-2xl mr-3"></i>
              <div>
                <p class="font-semibold text-yellow-800">{{ contactUnlockCoins }} Coins Required</p>
                <p class="text-sm text-yellow-700">Your current balance: {{ userCoins }} coins</p>
              </div>
            </div>
          </div>

          <div class="mb-6">
            <label class="flex items-start gap-3 text-sm text-gray-700">
              <input
                type="checkbox"
                v-model="acceptedPolicies"
                class="mt-1"
              />
              <span>
                I have read and agree to the
                <a href="/terms-and-conditions" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">Terms and Conditions</a>
                and the
                <a href="/safety-documents" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">Safety Documents</a>.
              </span>
            </label>
          </div>

          <div v-if="userCoins < contactUnlockCoins" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <p class="text-red-800 font-medium">
              <i class="fas fa-exclamation-triangle mr-2"></i>
              Insufficient coins. Please purchase more coins to continue.
            </p>
            <router-link to="/student/wallet" 
                         class="inline-block mt-3 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm">
              Purchase Coins
            </router-link>
          </div>

          <div class="flex gap-3">
            <button 
              @click="closeContactModal" 
              class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-3 rounded-lg font-medium transition">
              Reject
            </button>
            <button 
              @click="acceptAndUnlock"
              :disabled="userCoins < contactUnlockCoins || unlocking || !acceptedPolicies"
              :class="userCoins < contactUnlockCoins || unlocking || !acceptedPolicies ? 'bg-gray-400' : 'bg-green-600 hover:bg-green-700'"
              class="flex-1 text-white py-3 rounded-lg font-medium transition">
              <span v-if="unlocking">
                <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
              </span>
              <span v-else>Accept & Unlock ({{ contactUnlockCoins }} coins)</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Review Modal -->
    <div v-if="showReviewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-lg w-full">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-800">
              {{ isEditingReview ? 'Edit Review' : 'Write a Review' }}
            </h2>
            <button @click="closeReviewModal" class="text-gray-500 hover:text-gray-700">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <!-- Review Status Badge -->
          <div v-if="existingReview" class="mb-4">
            <div :class="existingReview.status === 'approved' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-yellow-50 border-yellow-200 text-yellow-800'" 
                 class="border rounded-lg p-3 text-sm">
              <div class="flex items-center">
                <i :class="existingReview.status === 'approved' ? 'fa-check-circle text-green-600' : 'fa-clock text-yellow-600'" 
                   class="fas mr-2"></i>
                <div>
                  <span class="font-semibold">Status: {{ existingReview.status.toUpperCase() }}</span>
                  <p v-if="existingReview.status === 'pending'" class="mt-1">
                    Your review is pending approval. You can edit it until it's approved.
                  </p>
                  <p v-else-if="existingReview.status === 'approved'" class="mt-1">
                    Your review is visible on the tutor's profile. Contact support to make changes.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
            <div class="flex gap-2">
              <button 
                v-for="star in 5" 
                :key="star"
                @click="reviewRating = star"
                :disabled="existingReview?.status === 'approved'"
                class="text-3xl transition-colors"
                :class="[
                  star <= reviewRating ? 'text-yellow-400' : 'text-gray-300',
                  existingReview?.status === 'approved' ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'
                ]">
                ★
              </button>
            </div>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Your Review</label>
            <textarea 
              v-model="reviewComment"
              :disabled="existingReview?.status === 'approved'"
              rows="4"
              placeholder="Share your experience with this tutor..."
              :class="existingReview?.status === 'approved' ? 'bg-gray-100 cursor-not-allowed' : ''"
              class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </textarea>
          </div>

          <div class="flex gap-3">
            <button 
              @click="closeReviewModal" 
              class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-3 rounded-lg font-medium transition">
              {{ existingReview?.status === 'approved' ? 'Close' : 'Cancel' }}
            </button>
            <button 
              v-if="existingReview?.status !== 'approved'"
              @click="submitReview"
              :disabled="!reviewRating || !reviewComment.trim() || submittingReview"
              :class="!reviewRating || !reviewComment.trim() || submittingReview ? 'bg-gray-400' : 'bg-blue-600 hover:bg-blue-700'"
              class="flex-1 text-white py-3 rounded-lg font-medium transition">
              <span v-if="submittingReview">
                <i class="fas fa-spinner fa-spin mr-2"></i>{{ isEditingReview ? 'Updating...' : 'Submitting...' }}
              </span>
              <span v-else>{{ isEditingReview ? 'Update Review' : 'Submit Review' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useUserStore } from '../store';

export default {
  name: 'TutorPublicProfile',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const userStore = useUserStore();
    const user = computed(() => userStore.user);
    const isLoggedIn = computed(() => !!user.value);
    const profile = ref(null);
    const loading = ref(true);    
    // Contact modal
    const showContactModal = ref(false);
    const unlocking = ref(false);
    const hasContactAccess = ref(false);
    const userCoins = ref(0);
    const contactUnlockCoins = ref(0);
    const acceptedPolicies = ref(false);
    
    // Review modal
    const showReviewModal = ref(false);
    const reviewRating = ref(0);
    const reviewComment = ref('');
    const submittingReview = ref(false);
    const canReview = ref(false);
    const existingReview = ref(null);
    const isEditingReview = ref(false);

    const isLoggedInStudent = computed(() => {
      return user.value && user.value.role === 'student';
    });

    const isOwnProfile = computed(() => {
      if (!user.value || !profile.value) return false;
      // Check if the logged-in user is viewing their own profile
      return user.value.id === profile.value.user_id;
    });    
    const approvedReviews = computed(() => {
      if (!profile.value?.reviews) return [];
      return profile.value.reviews;
    });

    const ratingSummary = computed(() => profile.value?.rating_summary || null);

    const ratingSummaryText = computed(() => {
      if (ratingSummary.value?.average) {
        return `${ratingSummary.value.average}/5 (${ratingSummary.value.total_reviews || 0} reviews)`;
      }
      if (profile.value?.rating_avg) {
        return `${profile.value.rating_avg}/5 (${profile.value.rating_count || 0} reviews)`;
      }
      return 'No reviews yet';
    });

    const approvedVideoUrl = computed(() => {
      const p = profile.value;
      if (!p) return null;

      // normalize status
      const status = (p.video_approval_status || '').trim().toLowerCase();
      if (status !== 'approved') return null;

      // Prefer YouTube link if present
      if (p.youtube_intro_url) return p.youtube_intro_url;

      // Try explicit URL fields if the API provides them
      if (p.video_url) return p.video_url;
      if (p.introductory_video_url) return p.introductory_video_url;

      // Build storage URL for uploaded file
      if (p.introductory_video) {
        if (p.introductory_video.startsWith('http')) return p.introductory_video;
        const base = window.location.origin.replace(/\/$/, '');
        const path = p.introductory_video.replace(/^\/?storage\//, '');
        return `${base}/storage/${path}`;
      }

      return null;
    });

    async function loadProfile() {
      loading.value = true;
      try {
        const res = await axios.get(`/api/public/tutors/${route.params.id}`);
        profile.value = res.data;
        // Check if student has already unlocked this tutor's contact
        if (isLoggedInStudent.value && profile.value?.user_id) {
          await checkContactAccess();
          await loadUserCoins();
        }
      } catch (error) {
        console.error('Error loading tutor profile:', error);
      } finally {
        loading.value = false;
      }
    }

    async function checkContactAccess() {
      try {
        const tutorId = profile.value?.id;
        if (!tutorId) return;
        
        const response = await axios.get(`/api/student/contacted-tutors/check/${tutorId}`);
        hasContactAccess.value = response.data.has_access || false;
        canReview.value = response.data.can_review || false;
      } catch (error) {
        console.error('Error checking contact access:', error);
      }
    }

    async function loadUserCoins() {
      try {
        const response = await axios.get('/api/student/coins/balance');
        userCoins.value = response.data.balance || 0;
      } catch (error) {
        console.error('Error loading coins:', error);
        userCoins.value = 0;
      }
    }

    async function loadContactUnlockCoins() {
      try {
        const response = await axios.get('/api/settings/contact-unlock-coins');
        if (response.data?.contact_unlock_coins !== undefined) {
          contactUnlockCoins.value = Number(response.data.contact_unlock_coins);
        }
      } catch (error) {
        console.error('Error loading contact unlock coins:', error);
      }
    }

    async function openContactModal() {
      if (!isLoggedInStudent.value) {
        alert('Please login as a student to contact tutors');
        router.push({ name: 'login', query: { redirect: route.fullPath } });
        return;
      }
      
      if (isOwnProfile.value) {
        alert('You cannot contact your own profile. This is your tutor profile.');
        return;
      }
      
      if (!contactUnlockCoins.value) {
        await loadContactUnlockCoins();
      }
      acceptedPolicies.value = false;
      showContactModal.value = true;
    }

    function closeContactModal() {
      showContactModal.value = false;
      acceptedPolicies.value = false;
    }

    async function acceptAndUnlock() {
      if (!acceptedPolicies.value) {
        alert('Please accept the Terms and Conditions and Safety Documents.');
        return;
      }
      if (userCoins.value < contactUnlockCoins.value) {
        alert('Insufficient coins. Please purchase more coins.');
        return;
      }

      unlocking.value = true;
      try {
        const tutorId = profile.value?.id;
        const studentId = user.value?.student?.id;
        
        if (!studentId) {
          alert('Student profile not found. Please create a student profile first.');
          return;
        }
        
        const response = await axios.post('/api/student/unlock-tutor-contact', {
          tutor_id: tutorId,
          student_id: studentId
        });

        if (response.data.success) {
          hasContactAccess.value = true;
          canReview.value = true;
          userCoins.value = response.data.remaining_balance || (userCoins.value - contactUnlockCoins.value);
          
          // Reload profile to get contact details
          await loadProfile();
          
          closeContactModal();
          alert('Contact details unlocked successfully! This tutor is now added to your "My Tutors" section.');
        }
      } catch (error) {
        console.error('Error unlocking contact:', error);
        if (error.response?.data?.message) {
          alert(error.response.data.message);
        } else {
          alert('Failed to unlock contact. Please try again.');
        }
      } finally {
        unlocking.value = false;
      }
    }

    function openReviewModal() {
      if (!isLoggedInStudent.value) {
        alert('Please login as a student to write reviews');
        router.push({ name: 'login', query: { redirect: route.fullPath } });
        return;
      }
      
      if (isOwnProfile.value) {
        alert('You cannot review your own profile. This is your tutor profile.');
        return;
      }
      
      if (!canReview.value) {
        alert('You can only review tutors whose contact you have unlocked.');
        return;
      }
      
      // Load existing review if available
      loadExistingReview();
      
      showReviewModal.value = true;
    }

    async function loadExistingReview() {
      try {
        const tutorId = profile.value?.id;
        const studentId = user.value?.student?.id;
        
        if (!tutorId || !studentId) return;
        
        const response = await axios.get(`/api/student/review/${tutorId}?student_id=${studentId}`);
        if (response.data.success && response.data.review) {
          existingReview.value = response.data.review;
          reviewRating.value = existingReview.value.rating;
          reviewComment.value = existingReview.value.comment;
          isEditingReview.value = true;
        }
      } catch (error) {
        // No existing review, that's fine
        existingReview.value = null;
        isEditingReview.value = false;
      }
    }

    function closeReviewModal() {
      showReviewModal.value = false;
      if (!isEditingReview.value) {
        reviewRating.value = 0;
        reviewComment.value = '';
      }
      isEditingReview.value = false;
    }

    async function submitReview() {
      if (!reviewRating.value || !reviewComment.value.trim()) {
        alert('Please provide both rating and comment');
        return;
      }

      submittingReview.value = true;
      try {
        const tutorId = profile.value?.id;
        const studentId = user.value?.student?.id;
        
        if (!studentId) {
          alert('Student profile not found.');
          return;
        }
        
        let response;
        if (isEditingReview.value && existingReview.value) {
          // Update existing review
          response = await axios.put(`/api/student/review/${existingReview.value.id}`, {
            rating: reviewRating.value,
            comment: reviewComment.value.trim()
          });
        } else {
          // Submit new review
          response = await axios.post('/api/student/submit-review', {
            tutor_id: tutorId,
            student_id: studentId,
            rating: reviewRating.value,
            comment: reviewComment.value.trim()
          });
        }

        if (response.data.success) {
          existingReview.value = response.data.review;
          
          // Show status modal
          const status = existingReview.value.status;
          const statusMessage = status === 'approved' 
            ? 'Your review is now visible on the tutor\'s profile.' 
            : 'Your review has been submitted and is pending approval. You can edit it until it\'s approved.';
          
          alert(`Review ${isEditingReview.value ? 'updated' : 'submitted'} successfully!\n\nStatus: ${status.toUpperCase()}\n${statusMessage}`);
          
          closeReviewModal();
          // Reload profile to show new review if approved
          if (status === 'approved') {
            await loadProfile();
          }
        }
      } catch (error) {
        console.error('Error submitting review:', error);
        if (error.response?.data?.message) {
          alert(error.response.data.message);
        } else {
          alert('Failed to submit review. Please try again.');
        }
      } finally {
        submittingReview.value = false;
      }
    }

    function formatDate(dateString) {
      if (!dateString) return null;
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
    }

    function getTotalExperience() {
      if (profile.value?.experience_total_years) {
        return `${profile.value.experience_total_years} years`;
      }

      if (!profile.value?.experiences || profile.value.experiences.length === 0) {
        return 'Not specified';
      }

      let totalYears = 0;
      profile.value.experiences.forEach(exp => {
        const fromYear = parseInt(exp.from_year);
        const toYear = exp.to_year ? parseInt(exp.to_year) : new Date().getFullYear();
        totalYears += (toYear - fromYear);
      });

      return totalYears > 0 ? `${totalYears} years` : 'Not specified';
    }

    function getLocation() {
      if (profile.value?.city) {
        let location = profile.value.city;
        if (profile.value.state) location += `, ${profile.value.state}`;
        if (profile.value.country) location += `, ${profile.value.country}`;
        return location;
      }
      if (profile.value?.address) return profile.value.address;
      return 'Not specified';
    }

    function getTravelInfo() {
      if (profile.value?.travel_willing) {
        return profile.value.travel_distance_km ? `Yes (${profile.value.travel_distance_km} km)` : 'Yes';
      }
      return 'No';
    }

    function isHomeTuitionAvailable() {
      if (profile.value?.teaching_mode && Array.isArray(profile.value.teaching_mode)) {
        return profile.value.teaching_mode.includes('home') ? 'Yes' : 'No';
      }
      return 'Not specified';
    }

    function getWorksAs() {
      if (profile.value?.current_role) return profile.value.current_role;
      if (profile.value?.employed_full_time) return 'Full-time Employed';
      return 'Individual teacher';
    }

    function getLevelLabel(subject) {
      if (subject.pivot?.from_level_id && subject.pivot?.to_level_id) {
        return `Levels ${subject.pivot.from_level_id}-${subject.pivot.to_level_id}`;
      }
      if (subject.pivot?.from_level_id) {
        return `Level ${subject.pivot.from_level_id}+`;
      }
      return '';
    }

    function requireLogin(action) {
      if (!isLoggedIn.value) {
        return router.push({ name: 'login', query: { redirect: route.fullPath } });
      }
      alert(`${action} feature coming soon`);
    }

    onMounted(async () => {
      if (!userStore.user && userStore.token) {
        await userStore.fetchUser();
      }
      await loadContactUnlockCoins();
      await loadProfile();
    });

    return {
      user,
      profile,
      loading,
      isLoggedIn,
      isLoggedInStudent,
      isOwnProfile,
      approvedReviews,
      ratingSummary,
      ratingSummaryText,
      approvedVideoUrl,
      showContactModal,
      unlocking,
      hasContactAccess,
      userCoins,
      contactUnlockCoins,
      acceptedPolicies,
      showReviewModal,
      reviewRating,
      reviewComment,
      submittingReview,
      canReview,
      existingReview,
      isEditingReview,
      openContactModal,
      closeContactModal,
      acceptAndUnlock,
      openReviewModal,
      closeReviewModal,
      submitReview,
      loadExistingReview,
      formatDate,
      getTotalExperience,
      getLocation,
      getTravelInfo,
      isHomeTuitionAvailable,
      getWorksAs,
      getLevelLabel,
      requireLogin
    };
  }
};
</script>
