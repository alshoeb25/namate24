<template>
  <div class="subject-modules-manager p-6">
    <div class="mb-6">
      <h2 class="text-3xl font-bold text-gray-900 mb-4">Subject Modules Management</h2>
      
      <!-- Subject Filter -->
      <div class="mb-4 flex gap-4">
        <select 
          v-model="selectedSubjectId"
          @change="loadModules"
          class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="">Select Subject...</option>
          <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
            {{ subject.name }}
          </option>
        </select>

        <button 
          v-if="selectedSubjectId"
          @click="showCreateModuleModal = true"
          class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-semibold"
        >
          + Add Module
        </button>
      </div>
    </div>

    <!-- Modules List -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-gray-600">Loading modules...</p>
    </div>

    <div v-else-if="modules.length === 0" class="text-center py-8 bg-gray-50 rounded-lg">
      <p class="text-gray-500">No modules found. Create one to get started.</p>
    </div>

    <div v-else class="space-y-4">
      <div 
        v-for="module in modules"
        :key="module.id"
        class="bg-white rounded-lg shadow border border-gray-200 p-6"
      >
        <div class="flex justify-between items-start mb-4">
          <div class="flex-1">
            <h3 class="text-xl font-bold text-gray-900">{{ module.name }}</h3>
            <p class="text-sm text-gray-600 mt-1">{{ module.description }}</p>
            <div class="flex gap-4 mt-2 text-sm">
              <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded">{{ module.code }}</span>
              <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded">{{ module.difficulty_level }}</span>
              <span v-if="module.estimated_hours" class="bg-green-100 text-green-800 px-3 py-1 rounded">
                {{ module.estimated_hours }}h
              </span>
            </div>
          </div>
          <div class="flex gap-2">
            <button 
              @click="editModule(module)"
              class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition text-sm"
            >
              Edit
            </button>
            <button 
              @click="deleteModule(module.id)"
              class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition text-sm"
            >
              Delete
            </button>
          </div>
        </div>

        <!-- Topics Section -->
        <div class="mt-4 pl-4 border-l-4 border-indigo-200">
          <h4 class="font-semibold text-gray-700 mb-2">Topics ({{ module.topics?.length || 0 }})</h4>
          <div v-if="module.topics?.length" class="space-y-2">
            <div 
              v-for="topic in module.topics"
              :key="topic.id"
              class="bg-gray-50 p-3 rounded flex justify-between items-center"
            >
              <div>
                <p class="font-medium text-gray-800">{{ topic.title }}</p>
                <p v-if="topic.description" class="text-sm text-gray-600">{{ topic.description }}</p>
              </div>
              <button 
                @click="deleteTopic(module.id, topic.id)"
                class="text-red-600 hover:text-red-800 text-sm"
              >
                Remove
              </button>
            </div>
          </div>
          <button 
            @click="showAddTopicModal(module)"
            class="text-sm text-indigo-600 hover:text-indigo-800 mt-2 font-medium"
          >
            + Add Topic
          </button>
        </div>

        <!-- Competencies Section -->
        <div class="mt-4 pl-4 border-l-4 border-green-200">
          <h4 class="font-semibold text-gray-700 mb-2">Competencies ({{ module.competencies?.length || 0 }})</h4>
          <div v-if="module.competencies?.length" class="space-y-2">
            <div 
              v-for="comp in module.competencies"
              :key="comp.id"
              class="bg-gray-50 p-3 rounded flex justify-between items-center"
            >
              <div>
                <p class="font-medium text-gray-800">{{ comp.name }}</p>
                <p class="text-xs text-gray-600 mt-1">Type: {{ comp.competency_type }}</p>
              </div>
              <button 
                @click="deleteCompetency(module.id, comp.id)"
                class="text-red-600 hover:text-red-800 text-sm"
              >
                Remove
              </button>
            </div>
          </div>
          <button 
            @click="showAddCompetencyModal(module)"
            class="text-sm text-indigo-600 hover:text-indigo-800 mt-2 font-medium"
          >
            + Add Competency
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Module Modal -->
    <div v-if="showCreateModuleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-xl font-bold">{{ editingModule ? 'Edit Module' : 'Create Module' }}</h3>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Module Name *</label>
            <input 
              v-model="moduleForm.name"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea 
              v-model="moduleForm.description"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              rows="3"
            ></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
              <input 
                v-model="moduleForm.code"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Difficulty Level *</label>
              <select 
                v-model="moduleForm.difficulty_level"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              >
                <option value="beginner">Beginner</option>
                <option value="intermediate">Intermediate</option>
                <option value="advanced">Advanced</option>
                <option value="expert">Expert</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Hours</label>
            <input 
              v-model.number="moduleForm.estimated_hours"
              type="number"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
          <div class="flex items-center">
            <input 
              v-model="moduleForm.is_active"
              type="checkbox"
              class="rounded"
            />
            <label class="ml-2 text-sm text-gray-700">Active</label>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex gap-2 justify-end">
          <button 
            @click="showCreateModuleModal = false"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
          >
            Cancel
          </button>
          <button 
            @click="saveModule"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
          >
            {{ editingModule ? 'Update' : 'Create' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Add Topic Modal -->
    <div v-if="showTopicModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-xl font-bold">Add Topic</h3>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Topic Title *</label>
            <input 
              v-model="topicForm.title"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea 
              v-model="topicForm.description"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              rows="3"
            ></textarea>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex gap-2 justify-end">
          <button 
            @click="showTopicModal = false"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
          >
            Cancel
          </button>
          <button 
            @click="saveTopic"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
          >
            Add Topic
          </button>
        </div>
      </div>
    </div>

    <!-- Add Competency Modal -->
    <div v-if="showCompetencyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-xl font-bold">Add Competency</h3>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Competency Name *</label>
            <input 
              v-model="competencyForm.name"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
            <select 
              v-model="competencyForm.competency_type"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="knowledge">Knowledge</option>
              <option value="skill">Skill</option>
              <option value="attitude">Attitude</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea 
              v-model="competencyForm.description"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              rows="3"
            ></textarea>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex gap-2 justify-end">
          <button 
            @click="showCompetencyModal = false"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
          >
            Cancel
          </button>
          <button 
            @click="saveCompetency"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
          >
            Add Competency
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
  name: 'SubjectModulesManager',
  setup() {
    const selectedSubjectId = ref('');
    const modules = ref([]);
    const subjects = ref([]);
    const loading = ref(false);

    const showCreateModuleModal = ref(false);
    const showTopicModal = ref(false);
    const showCompetencyModal = ref(false);

    const editingModule = ref(null);
    const currentModule = ref(null);

    const moduleForm = ref({
      name: '',
      description: '',
      code: '',
      difficulty_level: 'intermediate',
      estimated_hours: null,
      is_active: true,
    });

    const topicForm = ref({
      title: '',
      description: '',
    });

    const competencyForm = ref({
      name: '',
      competency_type: 'skill',
      description: '',
    });

    onMounted(async () => {
      await loadSubjects();
    });

    const loadSubjects = async () => {
      try {
        const res = await axios.get('/api/subjects');
        subjects.value = res.data.data || res.data;
      } catch (error) {
        console.error('Failed to load subjects', error);
      }
    };

    const loadModules = async () => {
      if (!selectedSubjectId.value) return;

      loading.value = true;
      try {
        const res = await axios.get('/api/admin/subject-modules', {
          params: { subject_id: selectedSubjectId.value },
        });
        modules.value = res.data.data;
      } catch (error) {
        console.error('Failed to load modules', error);
        alert('Error loading modules');
      } finally {
        loading.value = false;
      }
    };

    const saveModule = async () => {
      try {
        const payload = {
          ...moduleForm.value,
          subject_id: selectedSubjectId.value,
        };

        if (editingModule.value) {
          await axios.put(`/api/admin/subject-modules/${editingModule.value.id}`, payload);
          alert('Module updated successfully');
        } else {
          await axios.post('/api/admin/subject-modules', payload);
          alert('Module created successfully');
        }

        showCreateModuleModal.value = false;
        editingModule.value = null;
        resetModuleForm();
        await loadModules();
      } catch (error) {
        console.error('Failed to save module', error);
        alert('Error saving module');
      }
    };

    const editModule = (module) => {
      editingModule.value = module;
      moduleForm.value = { ...module };
      showCreateModuleModal.value = true;
    };

    const deleteModule = async (moduleId) => {
      if (!confirm('Are you sure?')) return;

      try {
        await axios.delete(`/api/admin/subject-modules/${moduleId}`);
        alert('Module deleted successfully');
        await loadModules();
      } catch (error) {
        console.error('Failed to delete module', error);
        alert('Error deleting module');
      }
    };

    const showAddTopicModal = (module) => {
      currentModule.value = module;
      topicForm.value = { title: '', description: '' };
      showTopicModal.value = true;
    };

    const saveTopic = async () => {
      try {
        await axios.post(
          `/api/admin/subject-modules/${currentModule.value.id}/topics`,
          topicForm.value
        );
        alert('Topic added successfully');
        showTopicModal.value = false;
        await loadModules();
      } catch (error) {
        console.error('Failed to add topic', error);
        alert('Error adding topic');
      }
    };

    const deleteTopic = async (moduleId, topicId) => {
      if (!confirm('Remove this topic?')) return;

      try {
        await axios.delete(`/api/admin/subject-modules/${moduleId}/topics/${topicId}`);
        await loadModules();
      } catch (error) {
        console.error('Failed to delete topic', error);
        alert('Error deleting topic');
      }
    };

    const showAddCompetencyModal = (module) => {
      currentModule.value = module;
      competencyForm.value = { name: '', competency_type: 'skill', description: '' };
      showCompetencyModal.value = true;
    };

    const saveCompetency = async () => {
      try {
        await axios.post(
          `/api/admin/subject-modules/${currentModule.value.id}/competencies`,
          competencyForm.value
        );
        alert('Competency added successfully');
        showCompetencyModal.value = false;
        await loadModules();
      } catch (error) {
        console.error('Failed to add competency', error);
        alert('Error adding competency');
      }
    };

    const deleteCompetency = async (moduleId, competencyId) => {
      if (!confirm('Remove this competency?')) return;

      try {
        await axios.delete(
          `/api/admin/subject-modules/${moduleId}/competencies/${competencyId}`
        );
        await loadModules();
      } catch (error) {
        console.error('Failed to delete competency', error);
        alert('Error deleting competency');
      }
    };

    const resetModuleForm = () => {
      moduleForm.value = {
        name: '',
        description: '',
        code: '',
        difficulty_level: 'intermediate',
        estimated_hours: null,
        is_active: true,
      };
    };

    return {
      selectedSubjectId,
      modules,
      subjects,
      loading,
      showCreateModuleModal,
      showTopicModal,
      showCompetencyModal,
      editingModule,
      moduleForm,
      topicForm,
      competencyForm,
      loadModules,
      saveModule,
      editModule,
      deleteModule,
      showAddTopicModal,
      saveTopic,
      deleteTopic,
      showAddCompetencyModal,
      saveCompetency,
      deleteCompetency,
    };
  },
};
</script>

<style scoped>
/* Smooth transitions */
button {
  transition: all 0.3s ease;
}
</style>
