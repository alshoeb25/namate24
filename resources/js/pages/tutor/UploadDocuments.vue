<template>
  <main class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-800">Upload Documents</h1>
      <p class="text-gray-600 mt-2">Upload your educational certificates, identification documents, and other relevant files</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-md p-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-6">Upload Your Documents</h2>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
            <select v-model="documentType" class="w-full border rounded-md p-2">
              <option value="educational">Educational Certificates</option>
              <option value="identification">Identification Proof</option>
              <option value="experience">Experience Letters</option>
              <option value="certification">Additional Certifications</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div id="uploadArea" class="file-upload-area rounded-xl p-8 text-center mb-6 border-2 border-dashed"
               @dragover.prevent
               @drop.prevent="onDrop">
            <div class="flex justify-center mb-4">
              <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center">
                <i class="fas fa-cloud-upload-alt text-3xl text-pink-500"></i>
              </div>
            </div>

            <h3 class="text-lg font-medium text-gray-800 mb-2">Drag & drop your files here</h3>
            <p class="text-gray-500 mb-6">or click to browse</p>

            <input ref="fileInputRef" type="file" multiple class="hidden" accept=".pdf,.jpg,.jpeg,.png" @change="onFileChange">
            <button @click="browse" class="bg-gradient-to-r from-pink-500 to-pink-600 text-white font-medium py-3 px-8 rounded-lg hover:opacity-90 transition">
              <i class="fas fa-folder-open mr-2"></i>Browse Files
            </button>

            <p class="text-gray-400 text-sm mt-4">Supported formats: PDF, JPG, JPEG, PNG (Max size: 10MB each)</p>
          </div>

          <div id="fileList" class="space-y-4">
            <div v-for="(f, idx) in files" :key="idx" class="flex items-center justify-between p-3 border rounded-md">
              <div class="flex items-center gap-3">
                <i class="fas fa-file text-gray-500"></i>
                <div>
                  <div class="text-sm font-medium text-gray-800">{{ f.name }}</div>
                  <div class="text-xs text-gray-500">{{ formatSize(f.size) }}</div>
                </div>
              </div>
              <button class="text-red-600 text-sm" @click="removeFile(idx)">Remove</button>
            </div>
            <div v-if="fileError" class="text-sm text-red-600">{{ fileError }}</div>
          </div>

          <div class="mt-8">
            <button id="uploadBtn" :disabled="!files.length || uploading" @click="upload"
                    class="w-full bg-gradient-to-r from-pink-500 to-pink-600 text-white font-medium py-3 px-8 rounded-lg hover:opacity-90 transition flex items-center justify-center">
              <i class="fas fa-upload mr-2"></i>{{ uploading ? 'Uploading…' : 'Upload All Documents' }}
            </button>
          </div>
        </div>

        <div id="uploadProgress" v-if="progressItems.length" class="mt-6 bg-white rounded-xl shadow-md p-6">
          <h3 class="text-lg font-medium text-gray-800 mb-4">Upload Progress</h3>
          <div class="space-y-4">
            <div v-for="(p, i) in progressItems" :key="i">
              <div class="flex justify-between text-sm mb-1">
                <span>{{ p.name }}</span>
                <span>{{ p.percent }}%</span>
              </div>
              <div class="w-full h-2 bg-gray-200 rounded">
                <div class="h-2 bg-pink-500 rounded" :style="{ width: p.percent + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
          <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
            <i class="fas fa-info-circle text-pink-500 mr-2"></i>Upload Instructions
          </h3>
          <ul class="space-y-3">
            <li class="flex items-start"><i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i><span class="text-gray-700">Maximum file size: 10MB per document</span></li>
            <li class="flex items-start"><i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i><span class="text-gray-700">Accepted formats: PDF, JPG, JPEG, PNG</span></li>
            <li class="flex items-start"><i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i><span class="text-gray-700">You can upload multiple files at once</span></li>
            <li class="flex items-start"><i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i><span class="text-gray-700">Make sure documents are clear and readable</span></li>
          </ul>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
          <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
            <i class="fas fa-file-alt text-pink-500 mr-2"></i>Your Documents
          </h3>

          <div v-if="loading" class="text-sm text-gray-500">Loading…</div>
          <div v-else-if="!documents.length" class="text-sm text-gray-500">No documents uploaded yet.</div>
          <div v-else class="space-y-3">
            <div v-for="d in documents" :key="d.id" class="p-3 border rounded-md">
              <div class="flex justify-between items-start">
                <div>
                  <div class="text-sm font-medium text-gray-800">{{ d.file_name }}</div>
                  <div class="text-xs text-gray-500">Type: {{ d.document_type }}</div>
                  <div class="text-xs mt-1" :class="statusClass(d.verification_status)">
                    Status: {{ d.verification_status }}
                    <span v-if="d.verification_status==='rejected' && d.rejection_reason"> ({{ d.rejection_reason }})</span>
                  </div>
                </div>
                <div class="flex gap-2">
                  <a v-if="d.url" :href="d.url" target="_blank" class="text-indigo-600 text-xs">View</a>
                  <button v-if="d.verification_status!=='approved'" @click="removeDoc(d)" class="text-red-600 text-xs">Delete</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '../../bootstrap'

const documents = ref([])
const loading = ref(false)
const files = ref([])
const fileInputRef = ref(null)
const fileError = ref('')
const progressItems = ref([])
const uploading = ref(false)
const documentType = ref('educational')

const fetchDocs = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/tutor/documents')
    documents.value = data || []
  } finally {
    loading.value = false
  }
}

onMounted(fetchDocs)

const browse = () => fileInputRef.value && fileInputRef.value.click()
const onFileChange = (e) => addFiles(e.target.files)
const onDrop = (e) => addFiles(e.dataTransfer.files)

const addFiles = (list) => {
  fileError.value = ''
  const allowed = ['application/pdf', 'image/jpeg', 'image/png']
  for (const f of list) {
    if (!allowed.includes(f.type)) {
      fileError.value = 'Only PDF, JPG, JPEG, PNG allowed.'
      continue
    }
    if (f.size > 10 * 1024 * 1024) {
      fileError.value = 'Each file must be <= 10MB.'
      continue
    }
    files.value.push(f)
  }
}

const removeFile = (idx) => files.value.splice(idx, 1)

const formatSize = (bytes) => {
  const mb = bytes / (1024 * 1024)
  return mb.toFixed(2) + ' MB'
}

const upload = async () => {
  if (!files.value.length) return
  uploading.value = true
  progressItems.value = files.value.map(f => ({ name: f.name, percent: 0 }))

  try {
    const form = new FormData()
    files.value.forEach(f => form.append('documents[]', f))
    form.append('document_type', documentType.value)

    await axios.post('/api/tutor/documents', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: (e) => {
        if (!e.total) return
        const percent = Math.round((e.loaded * 100) / e.total)
        progressItems.value = progressItems.value.map(p => ({ ...p, percent }))
      }
    })

    files.value = []
    await fetchDocs()
  } catch (e) {
    console.error('Upload failed', e)
  } finally {
    uploading.value = false
    setTimeout(() => { progressItems.value = [] }, 800)
  }
}

const removeDoc = async (d) => {
  try {
    await axios.delete('/api/tutor/documents/' + d.id)
    documents.value = documents.value.filter(x => x.id !== d.id)
  } catch (e) {
    console.error('Delete failed', e)
  }
}

const statusClass = (s) => {
  switch (s) {
    case 'approved': return 'text-green-600'
    case 'rejected': return 'text-red-600'
    default: return 'text-gray-600'
  }
}
</script>

<style scoped>
.file-upload-area { background: #fffafc; border-color: #f472b6; }
</style>
