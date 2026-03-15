<template>
  <div v-if="loading" class="flex items-center justify-center min-h-screen">
    <div class="text-xl text-gray-600">Loading...</div>
  </div>

  <div v-else class="max-w-5xl mx-auto px-4 py-8">
    <div class="mb-6">
      <button @click="goBack" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Requirements
      </button>
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

          <!-- Subscription Exhausted Banner -->
          <div v-if="viewsExhausted" class="bg-orange-50 border border-orange-300 rounded-lg p-4 mb-4">
            <div class="flex items-start gap-3">
              <i class="fas fa-exclamation-triangle text-orange-500 text-lg mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm font-semibold text-orange-800 mb-1">Subscription Views Exhausted</p>
                <p class="text-sm text-orange-700 mb-3">
                  You've used all <strong>{{ exhaustedData.views_used }}/{{ exhaustedData.views_allowed }}</strong> subscription views.
                  To approach a tutor, choose one of the options below:
                </p>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-if="exhaustedData.can_pay_with_coins"
                    @click="(pendingTeacherId = null, showInterestedModal = false, showExhaustedModal = true)"
                    class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded text-sm font-medium transition">
                    <i class="fas fa-coins mr-1"></i>Use Coins ({{ exhaustedData.coin_cost_alternative }})
                  </button>
                  <span v-else class="text-xs text-orange-600 self-center">
                    <i class="fas fa-coins mr-1"></i>Need {{ exhaustedData.coin_cost_alternative }} coins (have {{ exhaustedData.coins_available }})
                    — <router-link to="/student/wallet" class="underline font-medium">Buy Coins</router-link>
                  </span>
                  <button
                    @click="$router.push('/student/subscriptions')"
                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm font-medium transition">
                    <i class="fas fa-star mr-1"></i>Upgrade Plan
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Normal cost info -->
          <div v-else class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
            <p class="text-sm text-yellow-800">
              <i class="fas fa-info-circle mr-2"></i>
              <strong>Note:</strong>
              Approaching a tutor will cost
              <strong v-if="approachCostType === 'free'">Free</strong>
              <strong v-else>{{ approachCoinCost }} coins</strong>
              <span v-if="approachCostType === 'free'"> — included with your subscription</span>
              <span v-else> (based on your nationality)</span>.
              You'll be able to see their contact details after approaching.
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
                  <i class="fas fa-check-circle mr-1"></i>
                  {{ approachLoading ? 'Processing...' : (approachCostType === 'free' ? 'Approach (Free)' : 'Approach (' + approachCoinCost + ' coins)') }}
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

    <!-- Subscription Exhausted Modal -->
    <div v-if="showExhaustedModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-red-500 px-6 py-5 text-white">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center flex-shrink-0">
              <i class="fas fa-exclamation-triangle text-white text-lg"></i>
            </div>
            <div>
              <h2 class="text-lg font-bold">Subscription Views Exhausted</h2>
              <p class="text-orange-100 text-sm mt-0.5">
                Used <strong>{{ exhaustedData.views_used }}</strong> of <strong>{{ exhaustedData.views_allowed }}</strong> views in your current plan
              </p>
            </div>
          </div>
        </div>

        <div class="p-6">
          <p class="text-gray-600 text-sm mb-5 text-center">How would you like to proceed for this service?</p>

          <!-- Two Option Cards -->
          <div class="grid grid-cols-2 gap-3 mb-4">
            <!-- Coins Option -->
            <div
              v-if="exhaustedData.can_pay_with_coins"
              class="border-2 border-yellow-300 rounded-xl p-4 cursor-pointer hover:border-yellow-500 hover:bg-yellow-50 transition group"
              @click="openConfirmFromExhausted">
              <div class="text-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-yellow-200 transition">
                  <i class="fas fa-coins text-yellow-600 text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-800 mb-1">Pay with Coins</h3>
                <p class="text-2xl font-bold text-yellow-600 mb-1">{{ exhaustedData.coin_cost_alternative }}</p>
                <p class="text-xs text-gray-500">coins per approach</p>
                <div class="mt-3 bg-yellow-50 rounded-lg px-2 py-1.5">
                  <p class="text-xs text-gray-600">Your balance: <strong class="text-gray-800">{{ exhaustedData.coins_available }} coins</strong></p>
                  <p class="text-xs text-green-600 font-medium">After: {{ exhaustedData.coins_available - exhaustedData.coin_cost_alternative }} coins</p>
                </div>
              </div>
            </div>

            <!-- Insufficient coins placeholder -->
            <div v-else class="border-2 border-gray-200 rounded-xl p-4 opacity-60 cursor-not-allowed">
              <div class="text-center">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                  <i class="fas fa-coins text-gray-400 text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-500 mb-1">Pay with Coins</h3>
                <p class="text-2xl font-bold text-gray-400 mb-1">{{ exhaustedData.coin_cost_alternative }}</p>
                <p class="text-xs text-gray-400">coins required</p>
                <div class="mt-3 bg-red-50 rounded-lg px-2 py-1.5">
                  <p class="text-xs text-red-600">Only {{ exhaustedData.coins_available }} coins available</p>
                </div>
              </div>
            </div>

            <!-- Upgrade Plan Option -->
            <div
              class="border-2 border-blue-300 rounded-xl p-4 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition group"
              @click="goToSubscriptions">
              <div class="text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-200 transition">
                  <i class="fas fa-crown text-blue-600 text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-800 mb-1">Upgrade Plan</h3>
                <p class="text-sm font-semibold text-blue-600 mb-1">More Views</p>
                <p class="text-xs text-gray-500">unlimited or higher limit</p>
                <div class="mt-3 bg-blue-50 rounded-lg px-2 py-1.5">
                  <p class="text-xs text-blue-700 font-medium">Best value for active use</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Buy coins link if insufficient -->
          <div v-if="!exhaustedData.can_pay_with_coins" class="bg-orange-50 border border-orange-200 rounded-lg px-4 py-3 mb-4 flex items-center gap-2">
            <i class="fas fa-info-circle text-orange-500 text-sm"></i>
            <p class="text-xs text-orange-700">
              You need {{ exhaustedData.coin_cost_alternative - exhaustedData.coins_available }} more coins.
              <router-link to="/student/wallet" class="font-semibold text-orange-800 underline ml-1">Buy Coins →</router-link>
            </p>
          </div>

          <button
            @click="closeExhaustedModal"
            class="w-full py-2.5 border border-gray-200 text-gray-500 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Approach Modal (coins deduction confirmation) -->
    <div v-if="showConfirmModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-500 to-teal-500 px-6 py-5 text-white text-center">
          <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-handshake text-white text-xl"></i>
          </div>
          <h2 class="text-lg font-bold">Confirm Approach</h2>
          <p class="text-green-100 text-xs mt-1">Review details before proceeding</p>
        </div>

        <div class="p-6">
          <!-- Tutor Info -->
          <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
              <i class="fas fa-user text-blue-600"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-0.5">You are approaching</p>
              <p class="font-bold text-gray-800">{{ confirmData.teacherName }}</p>
            </div>
          </div>

          <!-- Cost Breakdown -->
          <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
            <p class="text-xs font-semibold text-yellow-800 uppercase tracking-wide mb-3">Payment Summary</p>
            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Service</span>
                <span class="font-medium text-gray-800">Approach Tutor</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Cost</span>
                <span class="font-bold text-yellow-700">{{ confirmData.coinCost }} coins</span>
              </div>
              <div class="border-t border-yellow-200 pt-2 mt-2 space-y-1">
                <div class="flex justify-between text-xs text-gray-500">
                  <span>Balance before</span>
                  <span>{{ confirmData.balanceBefore }} coins</span>
                </div>
                <div class="flex justify-between text-xs font-semibold">
                  <span class="text-gray-700">Balance after</span>
                  <span class="text-green-700">{{ confirmData.balanceBefore - confirmData.coinCost }} coins</span>
                </div>
              </div>
            </div>
          </div>

          <p class="text-xs text-gray-500 text-center mb-4">
            <i class="fas fa-lock mr-1"></i>
            Contact details will be shared after confirmation
          </p>

          <div class="flex gap-3">
            <button
              @click="cancelConfirm"
              class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition text-sm font-medium">
              Back
            </button>
            <button
              @click="confirmApproach"
              :disabled="approachLoading"
              class="flex-1 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl transition text-sm font-bold disabled:opacity-50 flex items-center justify-center gap-2">
              <i class="fas fa-spinner fa-spin" v-if="approachLoading"></i>
              <i class="fas fa-check-circle" v-else></i>
              {{ approachLoading ? 'Processing...' : 'Confirm' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Requirement Card -->
    <div class="bg-white rounded-xl shadow-lg p-8">
      <div class="mb-6 pb-6 border-b border-gray-200">
        <div class="flex items-start justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
              {{ subjectsDisplay }}
            </h1>
            <div class="flex flex-wrap gap-2 mb-2">
              <span v-for="subject in requirement.subjects || []" :key="subject.id"
                    class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                {{ subject.name }}
              </span>
            </div>
            <div class="flex items-center gap-4 text-sm text-gray-600">
              <span class="flex items-center gap-1">
                <i class="fas fa-map-marker-alt"></i>
                {{ requirement.city || requirement.location || 'Location not specified' }}
              </span>
              <span class="flex items-center gap-1">
                <i class="fas fa-clock"></i>
                Posted {{ formatDate(requirement.created_at) }}
              </span>
            </div>
          </div>
          <div class="flex flex-col items-end gap-2">
            <span class="px-3 py-1 rounded-full text-sm font-medium"
                  :class="statusBadgeClass">
              {{ requirement.status_label || requirement.status || 'N/A' }}
            </span>
            <span class="px-3 py-1 rounded-full text-xs font-semibold"
                  :class="requirement.lead_status === 'full' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700'">
              {{ requirement.lead_status_label || requirement.lead_status || 'Open' }}
            </span>
            <div v-if="requirement.lead_info" class="text-sm text-gray-600">
              {{ requirement.lead_info.available }}/{{ requirement.lead_info.max }} spots available
            </div>
          </div>
        </div>
      </div>

      <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-3">Requirement Details</h2>
        <p v-if="requirement.details" class="text-gray-700 leading-relaxed whitespace-pre-line">
          {{ requirement.details }}
        </p>
        <p v-else class="text-gray-500 italic">No additional details provided</p>
      </div>

      <!-- History Section -->
      <div v-if="history.length" class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-3">Requirement History</h2>
        <div class="space-y-4">
          <div v-for="(event, idx) in history" :key="idx" class="flex items-start gap-4">
            <div class="mt-1">
              <span class="inline-flex items-center justify-center w-8 h-8 rounded-full"
                    :class="event.type === 'approached' ? 'bg-green-100 text-green-700' : (event.type === 'unlock' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700')">
                <i :class="event.type === 'approached' ? 'fas fa-check' : (event.type === 'unlock' ? 'fas fa-unlock' : 'fas fa-plus')"></i>
              </span>
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between">
                <p class="font-semibold text-gray-800">{{ event.label }}</p>
                <span class="text-xs text-gray-500">{{ formatDate(event.date) }}</span>
              </div>
              <div v-if="event.tutor" class="mt-2 flex items-center gap-3">
                <img v-if="event.tutor.photo" :src="event.tutor.photo" :alt="event.tutor.name" class="w-10 h-10 rounded-full object-cover">
                <div v-else class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                  <i class="fas fa-user text-blue-600"></i>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-800">{{ event.tutor.name || 'Tutor' }}</p>
                  <p class="text-xs text-gray-500" v-if="event.tutor.subjects && event.tutor.subjects.length">
                    {{ event.tutor.subjects.join(', ') }}
                  </p>
                  <!-- Show contact details if available -->
                  <div v-if="event.type === 'approached' && (event.tutor.email || event.tutor.phone)" class="mt-2 space-y-1 bg-green-50 p-2 rounded">
                    <p v-if="event.tutor.email" class="text-xs text-gray-800 font-medium">
                      <i class="fas fa-envelope mr-1 text-blue-600"></i>{{ event.tutor.email }}
                    </p>
                    <p v-if="event.tutor.phone" class="text-xs text-gray-800 font-medium">
                      <i class="fas fa-phone mr-1 text-green-600"></i>{{ event.tutor.phone }}
                    </p>
                  </div>
                </div>
              </div>
              <div v-if="event.type === 'unlock' && event.unlock_price" class="mt-1 text-xs text-gray-600">
                Unlock Price: ₹{{ event.unlock_price }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
        <div class="flex items-center text-gray-600">
          <i class="fas fa-rupee-sign mr-2 text-purple-600"></i>
          <span>{{ requirement.budget_display || (requirement.budget + ' ' + (requirement.budget_type_label || requirement.budget_type)) }}</span>
        </div>
        <div v-if="requirement.service_type" class="flex items-center text-gray-600">
          <i class="fas fa-briefcase mr-2 text-purple-600"></i>
          <span>{{ requirement.service_type_label || requirement.service_type }}</span>
        </div>
        <div v-if="requirement.availability" class="flex items-center text-gray-600">
          <i class="fas fa-clock mr-2 text-purple-600"></i>
          <span>{{ requirement.availability_label || requirement.availability }}</span>
        </div>
        <div v-if="meetingOptionsDisplay" class="flex items-center text-gray-600">
          <i class="fas fa-handshake mr-2 text-purple-600"></i>
          <span>{{ meetingOptionsDisplay }}</span>
        </div>
        <div v-if="requirement.gender_preference" class="flex items-center text-gray-600">
          <i class="fas fa-user mr-2 text-purple-600"></i>
          <span>{{ requirement.gender_preference_label || requirement.gender_preference }}</span>
        </div>
        <div class="flex items-center text-gray-600">
          <i class="fas fa-calendar mr-2 text-purple-600"></i>
          <span>{{ formatDate(requirement.created_at) }}</span>
        </div>
      </div>

      <div v-if="requirement.languages && requirement.languages.length" class="mt-4 pt-4 border-t">
        <p class="text-sm text-gray-600 mb-2"><i class="fas fa-language mr-1"></i>Languages:</p>
        <div class="flex flex-wrap gap-2">
          <span v-for="lang in requirement.languages" :key="lang" class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
            {{ lang }}
          </span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap gap-3 pt-6 border-t border-gray-200 mt-6">
        <button v-if="canViewTeachers"
                @click="openInterestedModal(requirement.id)"
                class="px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition whitespace-nowrap">
          <i class="fas fa-eye mr-1"></i>View Tutors
        </button>
        <button v-if="canRefund"
                @click="openRefundModal(requirement.id)"
                class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition whitespace-nowrap">
          <i class="fas fa-coins mr-1"></i>Get Refund
        </button>
        <button v-if="canClose"
                @click="closeRequirement(requirement.id)"
                class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium transition">
          <i class="fas fa-times-circle mr-1"></i>Close
        </button>
        <button v-if="canEdit"
                @click="editRequirement(requirement.id)"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
          <i class="fas fa-edit mr-1"></i>Edit
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useUserStore } from '../store';
import axios from '../bootstrap';

export default {
  name: 'StudentRequirementDetail',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const userStore = useUserStore();
    const requirement = ref({});
    const loading = ref(true);
    const history = ref([]);

    // Refund modal state
    const showRefundModal = ref(false);
    const refundAmount = ref(0);
    const refundRequirementId = ref(null);

    // Interested teachers modal state
    const showInterestedModal = ref(false);
    const interestedTeachers = ref([]);
    const approachLoading = ref(false);
    const approachCoinCost = ref(0);
    const approachCostType = ref('coins'); // 'coins' or 'subscription'
    const approachCostLabel = ref(''); // Display label from API
    const viewsExhausted = ref(false); // Whether free views are exhausted

    // Subscription exhausted modal state
    const showExhaustedModal = ref(false);
    const exhaustedData = ref({ views_used: 0, views_allowed: 0, coin_cost_alternative: 0, coins_available: 0, can_pay_with_coins: false });
    const pendingTeacherId = ref(null);

    // Confirm approach modal
    const showConfirmModal = ref(false);
    const confirmData = ref({ teacherName: '', coinCost: 0, balanceBefore: 0, useCoinsForExhausted: false });

    const userHasSubscription = computed(() => {
      return userStore.user?.has_active_subscription || userStore.user?.subscription_active || false;
    });

    const approachCostDisplay = computed(() => {
      // Use the label from API if available
      if (approachCostLabel.value) {
        return approachCostLabel.value;
      }
      // Fallback to old logic
      if (userHasSubscription.value) {
        return 'Free';
      }
      return `${approachCoinCost.value} coins`;
    });

    const fetchRequirement = async () => {
      loading.value = true;
      try {
        const response = await axios.get(`/api/student/requirement-details/${route.params.id}`);
        requirement.value = response.data.requirement || response.data;
        history.value = response.data.history || [];
      } catch (err) {
        console.error('Error loading requirement:', err);
        router.push('/student/requirements');
      } finally {
        loading.value = false;
      }
    };

    const editRequirement = (id) => {
      router.push(`/student/requirements/${id}/edit`);
    };

    const openRefundModal = (id) => {
      if (requirement.value && requirement.value.current_leads === 0 && requirement.value.post_fee > 0) {
        refundAmount.value = requirement.value.post_fee;
        refundRequirementId.value = id;
        showRefundModal.value = true;
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
        
        showRefundModal.value = false;
        const refundedCoins = refundAmount.value;
        refundAmount.value = 0;
        refundRequirementId.value = null;
        
        // Show success message with refund details
        alert(`Refund successful! ${refundedCoins} coins refunded.`);
        
        // Navigate back to requirements list
        router.push('/student/requirements');
      } catch (err) {
        console.error('Error processing refund:', err);
        showRefundModal.value = false;
        
        if (err.response?.data?.message) {
          alert(err.response.data.message);
        } else {
          alert('Failed to process refund. Please try again.');
        }
      }
    };

    const openInterestedModal = async (id) => {
      try {
        const response = await axios.get(`/api/student/requirements/${id}/interested-teachers`);
        interestedTeachers.value = response.data.teachers || [];
        approachCoinCost.value = response.data.approach_coin_cost || 49;

        // Store the new fields from API
        approachCostType.value = response.data.approach_cost_type || 'coins';
        approachCostLabel.value = response.data.approach_cost_label || '';
        viewsExhausted.value = response.data.views_exhausted || false;

        // Populate exhaustedData from initial load so the modal is ready
        if (viewsExhausted.value) {
          exhaustedData.value = {
            views_used: response.data.views_used,
            views_allowed: response.data.views_allowed,
            coin_cost_alternative: response.data.coin_cost_alternative || response.data.approach_coin_cost || 49,
            coins_available: response.data.coins_available,
            can_pay_with_coins: response.data.can_pay_with_coins || false,
          };
        }

        showInterestedModal.value = true;
      } catch (err) {
        console.error('Error loading interested teachers:', err);
        alert('Failed to load interested teachers');
      }
    };

    const closeInterestedModal = () => {
      showInterestedModal.value = false;
      interestedTeachers.value = [];
    };

    const selectTeacher = async (teacherId) => {
      // If subscription views already known to be exhausted, show choice modal immediately
      if (viewsExhausted.value) {
        pendingTeacherId.value = teacherId;
        showInterestedModal.value = false;
        showExhaustedModal.value = true;
        return;
      }

      // For coin-based approach, show confirm modal instead of browser confirm()
      if (approachCostType.value !== 'free') {
        const teacher = interestedTeachers.value.find(t => t.id === teacherId);
        pendingTeacherId.value = teacherId;
        confirmData.value = {
          teacherName: teacher?.name || 'Tutor',
          coinCost: approachCoinCost.value,
          balanceBefore: exhaustedData.value.coins_available || 0,
          useCoinsForExhausted: false,
        };
        // Fetch current coin balance if we don't have it
        if (!confirmData.value.balanceBefore) {
          try {
            const userResp = await axios.get('/api/user');
            confirmData.value.balanceBefore = userResp.data.coins || 0;
          } catch {}
        }
        showInterestedModal.value = false;
        showConfirmModal.value = true;
        return;
      }

      // Free (subscription) approach — proceed directly
      approachLoading.value = true;
      try {
        const response = await axios.post(`/api/student/requirements/${requirement.value.id}/approach-teacher`, {
          teacher_id: teacherId
        });
        
        console.log('Approach response:', response.data);
        
        // Show success message based on cost type
        let successMessage = 'Successfully approached! ';
        
        if (approachCostType.value === 'subscription' && userHasSubscription.value) {
          successMessage += 'Contact details sent for free with your subscription.';
        } else if (approachCostType.value === 'coins' || viewsExhausted.value) {
          successMessage += `${response.data.coins_deducted} coins deducted.`;
        } else {
          successMessage += 'Contact details sent.';
        }
        
        alert(successMessage);
        
        // Reload interested teachers from database to show updated contact details
        const teachersResponse = await axios.get(`/api/student/requirements/${requirement.value.id}/interested-teachers`);
        interestedTeachers.value = teachersResponse.data.teachers || [];
        
        // Update requirement status
        requirement.value.status = 'approached';
        
        // Refresh requirement details to update status
        await fetchRequirement();
      } catch (err) {
        console.error('Error approaching teacher:', err);
        if (err.response?.status === 403 && err.response.data?.views_exhausted) {
          // Subscription exhausted - show choice modal
          exhaustedData.value = err.response.data;
          pendingTeacherId.value = teacherId;
          showExhaustedModal.value = true;
        } else if (err.response?.status === 402) {
          alert(err.response.data.message);
        } else if (err.response?.status === 422) {
          alert(err.response?.data?.message || 'You have already approached this tutor.');
        } else {
          alert(err.response?.data?.message || 'Failed to approach teacher');
        }
      } finally {
        approachLoading.value = false;
      }
    };

    const closeExhaustedModal = () => {
      showExhaustedModal.value = false;
      pendingTeacherId.value = null;
    };

    const goToSubscriptions = () => {
      closeExhaustedModal();
      router.push('/student/subscriptions');
    };

    const proceedWithCoins = async () => {
      if (!pendingTeacherId.value) return;
      showExhaustedModal.value = false;
      approachLoading.value = true;
      try {
        const response = await axios.post(`/api/student/requirements/${requirement.value.id}/approach-teacher`, {
          teacher_id: pendingTeacherId.value,
          use_coins: true,
        });
        alert(`Successfully approached! ${response.data.coins_deducted} coins deducted.`);
        pendingTeacherId.value = null;
        const teachersResponse = await axios.get(`/api/student/requirements/${requirement.value.id}/interested-teachers`);
        interestedTeachers.value = teachersResponse.data.teachers || [];
        requirement.value.status = 'approached';
        await fetchRequirement();
      } catch (err) {
        console.error('Error proceeding with coins:', err);
        alert(err.response?.data?.message || 'Failed to approach teacher');
      } finally {
        approachLoading.value = false;
      }
    };

    const openConfirmFromExhausted = () => {
      const teacher = interestedTeachers.value.find(t => t.id === pendingTeacherId.value);
      confirmData.value = {
        teacherName: teacher?.name || 'Tutor',
        coinCost: exhaustedData.value.coin_cost_alternative,
        balanceBefore: exhaustedData.value.coins_available,
        useCoinsForExhausted: true,
      };
      showExhaustedModal.value = false;
      showConfirmModal.value = true;
    };

    const cancelConfirm = () => {
      showConfirmModal.value = false;
      if (confirmData.value.useCoinsForExhausted) {
        showExhaustedModal.value = true;
      } else {
        showInterestedModal.value = true;
      }
    };

    const confirmApproach = async () => {
      if (!pendingTeacherId.value) return;
      approachLoading.value = true;
      showConfirmModal.value = false;
      try {
        const payload = { teacher_id: pendingTeacherId.value };
        if (confirmData.value.useCoinsForExhausted) {
          payload.use_coins = true;
        }
        const response = await axios.post(`/api/student/requirements/${requirement.value.id}/approach-teacher`, payload);
        const coinsMsg = response.data.coins_deducted ? ` ${response.data.coins_deducted} coins deducted.` : '';
        alert(`Successfully approached!${coinsMsg}`);
        pendingTeacherId.value = null;
        const teachersResponse = await axios.get(`/api/student/requirements/${requirement.value.id}/interested-teachers`);
        interestedTeachers.value = teachersResponse.data.teachers || [];
        requirement.value.status = 'approached';
        await fetchRequirement();
      } catch (err) {
        console.error('Error confirming approach:', err);
        if (err.response?.status === 403 && err.response.data?.views_exhausted) {
          exhaustedData.value = err.response.data;
          showExhaustedModal.value = true;
        } else {
          alert(err.response?.data?.message || 'Failed to approach teacher');
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
          alert(`Requirement closed! ${response.data.refund_amount} coins refunded.`);
        } else {
          alert('Requirement closed successfully!');
        }
        
        // Navigate back to requirements list
        router.push('/student/requirements');
      } catch (err) {
        console.error('Error closing requirement:', err);
        if (err.response?.data?.message) {
          alert(err.response.data.message);
        } else {
          alert('Failed to close requirement. Please try again.');
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

    const goBack = () => {
      router.push('/student/requirements');
    };

    const subjectsDisplay = computed(() => {
      if (requirement.value.subjects && requirement.value.subjects.length) {
        return requirement.value.subjects.map(s => s.name).join(', ');
      }
      return requirement.value.subject_name || requirement.value.other_subject || 'Subject Required';
    });

    const meetingOptionsDisplay = computed(() => {
      if (requirement.value.meeting_options_labels && requirement.value.meeting_options_labels.length) {
        return requirement.value.meeting_options_labels.join(', ');
      }
      if (Array.isArray(requirement.value.meeting_options)) {
        return requirement.value.meeting_options.join(', ');
      }
      return requirement.value.meeting_options || '';
    });

    const statusBadgeClass = computed(() => {
      if (requirement.value.status === 'active') return 'bg-green-100 text-green-700';
      if (requirement.value.status === 'approached') return 'bg-blue-100 text-blue-700';
      return 'bg-gray-100 text-gray-700';
    });

    const canViewTeachers = computed(() => requirement.value.status === 'active' && (requirement.value.current_leads || 0) > 0);
    const canRefund = computed(() => requirement.value.status === 'active' && (requirement.value.current_leads || 0) === 0 && (requirement.value.post_fee || 0) > 0);
    const canClose = computed(() => requirement.value.status === 'active');
    const canEdit = computed(() => {
      // Hide edit button if requirement is approached or has approached tutors in history
      if (requirement.value.status === 'approached') return false;
      const hasApproachedTutors = history.value.some(event => event.type === 'approached');
      return !hasApproachedTutors;
    });

    onMounted(() => {
      fetchRequirement();
    });

    return {
      requirement,
      loading,
      history,
      showRefundModal,
      refundAmount,
      showInterestedModal,
      interestedTeachers,
      approachLoading,
      approachCoinCost,
      approachCostDisplay,
      approachCostType,
      approachCostLabel,
      viewsExhausted,
      userHasSubscription,
      subjectsDisplay,
      meetingOptionsDisplay,
      statusBadgeClass,
      canViewTeachers,
      canRefund,
      canClose,
      canEdit,
      editRequirement,
      openRefundModal,
      cancelRefund,
      confirmRefund,
      openInterestedModal,
      closeInterestedModal,
      selectTeacher,
      closeRequirement,
      formatDate,
      goBack,
      showExhaustedModal,
      exhaustedData,
      pendingTeacherId,
      closeExhaustedModal,
      goToSubscriptions,
      proceedWithCoins,
      showConfirmModal,
      confirmData,
      openConfirmFromExhausted,
      cancelConfirm,
      confirmApproach,
    };
  }
};
</script>
