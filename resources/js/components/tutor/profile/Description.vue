<template>
  <main class="max-w-4xl mx-auto mt-16 px-4 pb-20">
    
    <!-- Page Title -->
    <h1 class="text-3xl font-semibold text-gray-800 mb-4">
      Profile description
    </h1>

    <!-- Instructions -->
    <div class="text-gray-700 space-y-2 mb-8">
      <p>This is the most important section for you.</p>

      <p class="font-medium">
        80% of students will decide if they want to hire you just based on what you write here.
      </p>

      <p>Make sure it's good, relevant, in detail, and without mistakes.</p>
      <p>Do not copy-paste your resume here.</p>
      <p class="text-red-600 font-medium">Do not share any contact details.</p>
    </div>

    <!-- Success Message -->
    <div v-if="message" class="p-4 bg-green-100 text-green-700 rounded-lg mb-6">
      {{ message }}
    </div>

    <!-- Form -->
    <form @submit.prevent="updateDescription" class="space-y-6">

      <!-- Textarea -->
      <div>
        <div class="flex justify-between items-center mb-2">
          <label class="block text-gray-700 font-medium">
            Your Profile Description
          </label>
          <!-- Profile Power Indicator -->
          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600">Profile Power:</span>
            <div class="flex items-center gap-1">
              <div class="w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                <div 
                  :class="[
                    'h-full transition-all duration-300',
                    profilePower < 30 ? 'bg-red-500' :
                    profilePower < 60 ? 'bg-yellow-500' :
                    profilePower < 80 ? 'bg-blue-500' : 'bg-green-500'
                  ]"
                  :style="{ width: profilePower + '%' }"
                ></div>
              </div>
              <span 
                :class="[
                  'text-sm font-semibold',
                  profilePower < 30 ? 'text-red-500' :
                  profilePower < 60 ? 'text-yellow-500' :
                  profilePower < 80 ? 'text-blue-500' : 'text-green-500'
                ]"
              >
                {{ profilePower }}%
              </span>
            </div>
          </div>
        </div>
        <textarea 
          v-model="form.description"
          rows="12" 
          placeholder="Write your profile description here..." 
          class="w-full border border-gray-300 rounded-md p-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
        ></textarea>
        <p class="text-xs text-gray-500 mt-1">{{ form.description?.length || 0 }} characters</p>
      </div>

      <!-- Checkbox -->
      <div class="flex items-start gap-3">
        <input 
          v-model="form.confirmed_no_contact"
          type="checkbox" 
          id="confirm"
          class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
        />
        <label for="confirm" class="text-gray-700 text-sm">
          I have not shared any contact details (Email, Phone, Skype, Website etc)
        </label>
      </div>

      <!-- Error Message -->
      <div v-if="error" ref="errorMessage" class="p-4 bg-red-100 text-red-700 rounded-lg">
        {{ error }}
      </div>

      <!-- Submit Button -->
      <div>
        <button 
          type="submit" 
          class="px-8 py-2 bg-blue-600 text-white font-medium rounded-sm hover:bg-blue-700 transition"
          :disabled="loading"
        >
          {{ loading ? 'Saving...' : 'Submit' }}
        </button>
      </div>

    </form>

  </main>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import axios from 'axios'

const form = ref({
  description: '',
  confirmed_no_contact: false
})

const loading = ref(false)
const message = ref('')
const error = ref('')
const errorMessage = ref(null)

const contactRegex =
  /(\b\d{10}\b)|(@)|(\bwww\b)|(http)|(https)|(skype)|(whatsapp)|(gmail)|(yahoo)/i

const profilePower = computed(() => {
  const length = form.value.description?.length || 0
  if (length === 0) return 0
  if (length < 100) return Math.min(20, length / 5)
  if (length < 300) return 20 + ((length - 100) / 200) * 30
  if (length < 500) return 50 + ((length - 300) / 200) * 30
  if (length < 800) return 80 + ((length - 500) / 300) * 15
  return Math.min(100, 95 + ((length - 800) / 200) * 5)
})

const fetchDescription = async () => {
  try {
    loading.value = true
    const { data } = await axios.get('/api/tutor/profile/description')
    form.value.description = data.description || ''
    form.value.confirmed_no_contact = data.confirmed_no_contact || false
  } catch {
    error.value = 'Failed to load description'
    scrollToError()
  } finally {
    loading.value = false
  }
}

const updateDescription = async () => {
  error.value = ''
  message.value = ''

  if (!form.value.description.trim()) {
    error.value = 'Please write your profile description.'
    scrollToError()
    return
  }

  if (contactRegex.test(form.value.description)) {
    error.value =
      'Please remove contact details (phone, email, website, WhatsApp, etc).'
    scrollToError()
    return
  }

  if (!form.value.confirmed_no_contact) {
    error.value = 'Please confirm that no contact details are shared.'
    scrollToError()
    return
  }

  try {
    loading.value = true
    const { data } = await axios.post(
      '/api/tutor/profile/description',
      form.value
    )
    message.value = data.message
    setTimeout(() => (message.value = ''), 3000)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update description'
    scrollToError()
  } finally {
    loading.value = false
  }
}

const scrollToError = () => {
  nextTick(() => {
    errorMessage.value?.scrollIntoView({ behavior: 'smooth' })
  })
}

onMounted(fetchDescription)
</script>

