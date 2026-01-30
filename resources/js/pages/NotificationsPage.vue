<template>
  <main class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold text-gray-800">Notifications</h1>
      <button
        v-if="unreadCount"
        class="text-sm text-indigo-600 hover:underline"
        :disabled="markingAll"
        @click="markAllRead"
      >
        {{ markingAll ? 'Marking…' : 'Mark all read' }}
      </button>
    </div>

    <div v-if="statusMessage" class="mb-4 px-4 py-2 text-sm text-green-700 bg-green-50 border rounded">
      {{ statusMessage }}
    </div>

    <div v-if="loading" class="p-4 text-gray-500">Loading…</div>
    <div v-else-if="error" class="p-4 text-red-600">{{ error }}</div>
    <div v-else-if="!notifications.length" class="p-4 text-gray-500">No notifications yet.</div>

    <ul v-else class="divide-y bg-white border rounded-lg shadow-sm">
      <li
        v-for="n in notifications"
        :key="n.id"
        class="p-4 flex items-start justify-between gap-4"
      >
        <div>
          <div class="text-sm font-semibold text-gray-800">
            {{ n.data?.title || 'Notification' }}
          </div>
          <div class="text-sm text-gray-600">
            {{ n.data?.message }}
          </div>
          <div class="text-xs text-gray-400 mt-1">
            {{ formatDate(n.created_at) }}
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span v-if="!n.read_at" class="w-2 h-2 rounded-full bg-indigo-500"></span>
          <button
            v-if="!n.read_at"
            class="text-xs text-indigo-600 hover:underline"
            @click="markRead(n)"
          >
            Mark read
          </button>
        </div>
      </li>
    </ul>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '../bootstrap'

const notifications = ref([])
const unreadCount = ref(0)
const loading = ref(false)
const error = ref('')
const statusMessage = ref('')
const markingAll = ref(false)

const fetchNotifications = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/notifications')
    notifications.value = (res.data || []).sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    unreadCount.value = notifications.value.filter(n => !n.read_at).length
  } catch (e) {
    error.value = 'Unable to load notifications'
  } finally {
    loading.value = false
  }
}

const markRead = async (n) => {
  if (n.read_at) return
  try {
    await axios.post(`/api/notifications/${n.id}/read`)
    n.read_at = new Date().toISOString()
    unreadCount.value = Math.max(unreadCount.value - 1, 0)
    statusMessage.value = 'Notification marked as read'
    setTimeout(() => { statusMessage.value = '' }, 2000)
  } catch (e) {
    error.value = 'Unable to mark as read'
  }
}

const markAllRead = async () => {
  const unread = notifications.value.filter(n => !n.read_at)
  if (!unread.length) return
  try {
    markingAll.value = true
    await axios.post('/api/notifications/read-all', { ids: unread.map(n => n.id) })
    unread.forEach(n => { n.read_at = new Date().toISOString() })
    unreadCount.value = 0
    statusMessage.value = 'All notifications marked as read'
    setTimeout(() => { statusMessage.value = '' }, 2000)
  } catch (e) {
    error.value = 'Unable to mark all as read'
  } finally {
    markingAll.value = false
  }
}

const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleString()
}

onMounted(fetchNotifications)
</script>
