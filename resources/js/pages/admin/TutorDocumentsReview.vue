<template>
  <main class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-800">Tutor Documents Review</h1>
      <select v-model="status" @change="fetchDocs" class="border rounded-md p-2 text-sm">
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
      <table class="min-w-full">
        <thead>
          <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-600">
            <th class="px-4 py-3">Tutor</th>
            <th class="px-4 py-3">Type</th>
            <th class="px-4 py-3">File</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Submitted</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="d in docs" :key="d.id" class="border-t text-sm">
            <td class="px-4 py-3">{{ d.tutor?.user?.name || '—' }}</td>
            <td class="px-4 py-3 capitalize">{{ d.document_type }}</td>
            <td class="px-4 py-3"><a :href="fileUrl(d)" target="_blank" class="text-indigo-600">View</a></td>
            <td class="px-4 py-3">
              <span :class="statusClass(d.verification_status)">{{ d.verification_status }}</span>
              <span v-if="d.verification_status==='rejected' && d.rejection_reason" class="text-gray-500"> ({{ d.rejection_reason }})</span>
            </td>
            <td class="px-4 py-3">{{ formatDate(d.created_at) }}</td>
            <td class="px-4 py-3 space-x-2">
              <button v-if="d.verification_status==='pending'" @click="approve(d)" class="px-3 py-1 text-xs rounded bg-green-100 text-green-700">Approve</button>
              <button v-if="d.verification_status==='pending'" @click="openReject(d)" class="px-3 py-1 text-xs rounded bg-red-100 text-red-700">Reject</button>
            </td>
          </tr>
          <tr v-if="!loading && !docs.length">
            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">No documents</td>
          </tr>
        </tbody>
      </table>
      <div v-if="loading" class="p-4 text-sm text-gray-500">Loading…</div>
    </div>

    <!-- Reject dialog -->
    <div v-if="rejectOpen" class="fixed inset-0 bg-black/30 flex items-center justify-center">
      <div class="bg-white rounded-lg shadow p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-3">Reject Document</h3>
        <textarea v-model="reason" class="w-full border rounded p-2" rows="3" placeholder="Reason (required)"></textarea>
        <div class="mt-4 flex justify-end gap-2">
          <button class="px-4 py-2" @click="rejectOpen=false">Cancel</button>
          <button class="px-4 py-2 bg-red-600 text-white rounded" @click="confirmReject">Reject</button>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '../../bootstrap'

const status = ref('pending')
const docs = ref([])
const loading = ref(false)
const rejectOpen = ref(false)
const reason = ref('')
const activeDoc = ref(null)

const fetchDocs = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/tutor-documents', { params: { status: status.value } })
    // If paginated, normalize
    docs.value = Array.isArray(data) ? data : (data.data || [])
  } finally {
    loading.value = false
  }
}

onMounted(fetchDocs)

const fileUrl = (d) => d.file_path ? (window.location.origin + '/storage/' + d.file_path) : '#'
const formatDate = (iso) => (iso ? new Date(iso).toLocaleString() : '')
const statusClass = (s) => ({ pending: 'text-gray-600', approved: 'text-green-600', rejected: 'text-red-600' }[s] || 'text-gray-600')

const approve = async (d) => {
  await axios.post(`/api/admin/tutor-documents/${d.id}/approve`)
  await fetchDocs()
}

const openReject = (d) => { activeDoc.value = d; reason.value=''; rejectOpen.value = true }
const confirmReject = async () => {
  if (!activeDoc.value || !reason.value.trim()) return
  await axios.post(`/api/admin/tutor-documents/${activeDoc.value.id}/reject`, { reason: reason.value })
  rejectOpen.value = false
  await fetchDocs()
}
</script>
