<template>
  <div class="relative notification-bell-root" @keydown.escape="close">
    <!-- Bell Button -->
    <button
      class="relative p-2 hover:bg-gray-100 rounded-full transition"
      @click="toggle"
      :aria-expanded="open"
      aria-haspopup="true"
      aria-label="Notifications"
    >
      <svg xmlns="http://www.w3.org/2000/svg"
           class="h-6 w-6 text-gray-700"
           fill="none"
           viewBox="0 0 24 24"
           stroke="currentColor">
        <path stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14V11a6 6 0 10-12 0v3c0 .386-.149.735-.395 1.0L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>

      <!-- Unread Badge -->
      <span
        v-if="unreadCount"
        class="absolute top-1 right-1 block min-w-[0.75rem] px-1 h-3 text-[10px]
               leading-3 text-center rounded-full bg-red-500 text-white">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown -->
    <div v-if="open"
         class="absolute right-0 mt-2 w-80 bg-white shadow-lg rounded-lg
                border overflow-hidden z-50">

      <div class="px-4 py-2 border-b text-sm text-gray-600 flex items-center justify-between">
        <span>Notifications</span>
        <button
          v-if="unreadCount"
          class="text-xs text-indigo-600 hover:underline"
          :disabled="markingAll"
          @click.stop="markAllRead"
        >
          {{ markingAll ? 'Marking…' : 'Mark all read' }}
        </button>
      </div>

      <div v-if="statusMessage" class="px-4 py-2 text-xs text-green-700 bg-green-50 border-b">
        {{ statusMessage }}
      </div>

      <div v-if="loading" class="p-3 text-sm text-gray-500">Loading…</div>
      <div v-else-if="error" class="p-3 text-sm text-red-600">{{ error }}</div>
      <div v-else-if="!notifications.length"
           class="p-3 text-sm text-gray-500">
        No notifications yet.
      </div>

      <ul v-else class="max-h-80 overflow-auto divide-y">
        <li
          v-for="n in notifications"
          :key="n.id"
          class="p-3 hover:bg-gray-50 cursor-pointer"
          @click="handleClick(n)"
        >
          <div class="flex items-start justify-between gap-2">
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

            <!-- unread dot -->
            <span v-if="!n.read_at"
                  class="w-2 h-2 mt-1 rounded-full bg-indigo-500"></span>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import axios from '../../bootstrap'
import { useUserStore } from '../../store'
import { getEcho } from '../../echo'

const notifications = ref([])
const unreadCount = ref(0)
const open = ref(false)
const loading = ref(false)
const error = ref('')
const statusMessage = ref('')
const markingAll = ref(false)

const userStore = useUserStore()
const user = computed(() => userStore.user)

let echoChannel = null
let channelName = ''

/* ------------------------------
   Fetch notifications (DB)
-------------------------------- */
const fetchNotifications = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/notifications')
    const fetchedNotifications = res.data || []
    
    // De-duplicate: merge with existing, keep unique by ID
    const existingIds = new Set(notifications.value.map(n => n.id))
    const newNotifications = fetchedNotifications.filter(n => !existingIds.has(n.id))
    
    // Keep existing + new, remove duplicates
    const allNotifications = [...notifications.value, ...newNotifications]
    const uniqueMap = new Map()
    allNotifications.forEach(n => {
      if (!uniqueMap.has(n.id)) {
        uniqueMap.set(n.id, n)
      }
    })
    
    notifications.value = Array.from(uniqueMap.values())
      .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      .slice(0, 50)
    
    unreadCount.value = notifications.value.filter(n => !n.read_at).length
  } catch (e) {
    error.value = 'Unable to load notifications'
  } finally {
    loading.value = false
  }
}

/* ------------------------------
   Click handler
-------------------------------- */
const handleClick = async (n) => {
  // mark read only on click
  if (!n.read_at) {
    try {
      await axios.post(`/api/notifications/${n.id}/read`)
      n.read_at = new Date().toISOString()
      unreadCount.value = Math.max(unreadCount.value - 1, 0)
      // show brief feedback when not navigating
      if (!n.data?.url) {
        statusMessage.value = 'Notification marked as read'
        setTimeout(() => { statusMessage.value = '' }, 2000)
      }
    } catch (e) {
      console.error('mark read error', e)
    }
  }

  // navigate if URL exists
  if (n.data?.url) {
    window.location.href = n.data.url
  } else {
    // close dropdown when no URL to navigate
    close()
  }
}

/* ------------------------------
   Mark all as read
-------------------------------- */
const markAllRead = async () => {
  const unread = notifications.value.filter(n => !n.read_at)
  if (!unread.length) return

  try {
    markingAll.value = true
    // Try bulk endpoint if available
    await axios.post('/api/notifications/read-all', {
      ids: unread.map(n => n.id),
    })

    unread.forEach(n => { n.read_at = new Date().toISOString() })
    unreadCount.value = 0
    statusMessage.value = 'All notifications marked as read'
    setTimeout(() => { statusMessage.value = '' }, 2000)
  } catch (bulkErr) {
    try {
      await Promise.all(unread.map(n => axios.post(`/api/notifications/${n.id}/read`)))
      unread.forEach(n => { n.read_at = new Date().toISOString() })
      unreadCount.value = 0
      statusMessage.value = 'All notifications marked as read'
      setTimeout(() => { statusMessage.value = '' }, 2000)
    } catch (e) {
      console.error('mark all read error', e)
      error.value = 'Unable to mark all as read'
      statusMessage.value = ''
    }
  } finally {
    markingAll.value = false
  }
}

/* ------------------------------
   Toggle dropdown
-------------------------------- */
const toggle = () => {
  open.value = !open.value
}

const close = () => {
  open.value = false
}

const handleClickOutside = (event) => {
  if (!event.target.closest('.notification-bell-root')) {
    close()
  }
}

/* ------------------------------
   Real-time (Pusher / Echo)
-------------------------------- */
const handleNotification = (notification) => {
  // Strict de-duplication check
  const isDuplicate = notifications.value.some(n => {
    // Check by ID first
    if (n.id === notification.id) return true
    
    // Also check by content similarity (same title + message + timestamp within 1 second)
    if (n.data?.title === notification.data?.title && 
        n.data?.message === notification.data?.message) {
      const timeDiff = Math.abs(
        new Date(n.created_at).getTime() - 
        new Date(notification.created_at || new Date()).getTime()
      )
      if (timeDiff < 1000) return true
    }
    
    return false
  })

  if (isDuplicate) {
    return
  }

  const normalized = {
    id: notification.id,
    data: notification.data || {},
    read_at: null,
    created_at: notification.created_at || new Date().toISOString(),
  }

  notifications.value = [normalized, ...notifications.value].slice(0, 50)
  unreadCount.value += 1
}

const subscribeToNotifications = () => {
  const echo = getEcho()
  if (!echo || !user.value?.id) return

  channelName = `App.Models.User.${user.value.id}`
  echoChannel = echo.private(channelName)
  echoChannel.notification(handleNotification)
}

const unsubscribeFromNotifications = () => {
  if (window.Echo && channelName) {
    window.Echo.leave(`private-${channelName}`)
  }
  echoChannel = null
  channelName = ''
}

/* ------------------------------
   Helpers
-------------------------------- */
const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleString()
}

/* ------------------------------
   Lifecycle
-------------------------------- */
onMounted(() => {
  fetchNotifications()
  subscribeToNotifications()
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  unsubscribeFromNotifications()
})

watch(user, (next, prev) => {
  if (next?.id !== prev?.id) {
    unsubscribeFromNotifications()
    subscribeToNotifications()
  }
})
</script>

<style scoped>
/* no custom CSS required */
</style>