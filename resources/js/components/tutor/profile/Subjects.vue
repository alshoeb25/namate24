<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Subjects</h2>
    
    <div class="space-y-4">
      <!-- Current Subjects -->
      <div v-if="subjects.length > 0" class="mb-6">
        <h3 class="text-lg font-semibold mb-3">Current Subjects</h3>
        <div class="flex flex-wrap gap-2">
          <div 
            v-for="subject in subjects" 
            :key="subject.id"
            class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full flex items-center gap-2"
          >
            <span>{{ subject.name }}</span>
            <span v-if="subject.from_level_id" class="text-xs">
              ({{ getLevelName(subject.from_level_id) }} - {{ getLevelName(subject.to_level_id) }})
            </span>
            <button 
              @click="startEditSubject(subject)"
              class="text-blue-700 hover:text-blue-900 font-bold"
            >
              ✎
            </button>
            <button 
              @click="removeSubject(subject.id)"
              class="text-red-600 hover:text-red-800 font-bold"
            >
              ×
            </button>
          </div>
        </div>
      </div>

      <!-- Add Subject -->
      <div class="border-t pt-4">
        <h3 class="text-lg font-semibold mb-3">Add Subject</h3>
        
        <div class="space-y-4">
          <!-- Subject Dropdown -->
          <div>
            <label class="block text-sm font-medium mb-2">Select Subject</label>
            <div class="relative">
              <input 
                v-model="searchQuery" 
                type="text" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="Search for a subject..."
                @focus="showDropdown = true"
                @blur="hideDropdown"
              />
              <div 
                v-if="showDropdown && filteredSubjects.length > 0"
                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
              >
                <div 
                  v-for="subject in filteredSubjects" 
                  :key="subject.id"
                  @mousedown="onDropdownItemMouseDown(subject)"
                  :class="[
                    'px-4 py-2 flex items-center justify-between',
                    isSubjectDisabled(subject) ? 'text-gray-400 bg-gray-50 cursor-not-allowed' : 'hover:bg-blue-50 cursor-pointer'
                  ]"
                  :title="isSubjectDisabled(subject) ? 'Subject already added' : ''"
                >
                  <span>{{ subject.name }}</span>
                  <span v-if="isSubjectDisabled(subject)" class="text-xs px-2 py-0.5 bg-gray-200 rounded-full">Added</span>
                </div>
              </div>
              <div 
                v-if="showDropdown && filteredSubjects.length === 0 && searchQuery"
                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg"
              >
                <div class="px-4 py-2 text-gray-500">
                  No subject found. 
                  <button 
                    @click="openCreateSubjectModal"
                    class="text-blue-600 hover:underline"
                  >
                    Create new subject
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- From Level Dropdown -->
          <div v-if="selectedSubject">
            <label class="block text-sm font-medium mb-2">From Level</label>
            <select 
              v-model="fromLevelId" 
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              :disabled="!selectedSubject"
              @change="updateToLevelOptions"
            >
              <option value="">-- Select Lowest Level --</option>
              <optgroup 
                v-for="(groupLevels, groupName) in levels" 
                :key="groupName" 
                :label="`-- ${groupName} --`"
              >
                <option 
                  v-for="level in groupLevels" 
                  :key="level.id" 
                  :value="level.id"
                >
                  {{ level.name }}
                </option>
              </optgroup>
            </select>
          </div>

          <!-- To Level Dropdown -->
          <div v-if="selectedSubject">
            <label class="block text-sm font-medium mb-2">To Level</label>
            <select 
              v-model="toLevelId" 
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              :disabled="!fromLevelId"
            >
              <option value="">-- Select Highest Level --</option>
              <optgroup 
                v-for="(groupLevels, groupName) in filteredToLevels" 
                :key="groupName" 
                :label="`-- ${groupName} --`"
              >
                <option 
                  v-for="level in groupLevels" 
                  :key="level.id" 
                  :value="level.id"
                >
                  {{ level.name }}
                </option>
              </optgroup>
            </select>
          </div>

          <!-- Add / Update & Cancel Buttons -->
          <div class="flex gap-2">
            <button 
              @click="addSubject" 
              class="flex-1 px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
              :disabled="!selectedSubject || !fromLevelId || !toLevelId || loading"
            >
              {{ loading ? (isEditing ? 'Updating...' : 'Adding...') : (isEditing ? 'Update Subject' : 'Add Subject') }}
            </button>
            <button 
              v-if="isEditing"
              @click="cancelEdit"
              class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400"
              :disabled="loading"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>

      <!-- Messages -->
      <div v-if="message" class="p-4 bg-green-100 text-green-700 rounded-lg">
        {{ message }}
      </div>
      <div v-if="error" class="p-4 bg-red-100 text-red-700 rounded-lg">
        {{ error }}
      </div>
    </div>

    <!-- Create Subject Modal -->
    <div 
      v-if="showCreateModal" 
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click="closeCreateSubjectModal"
    >
      <div 
        class="bg-white rounded-lg p-6 max-w-md w-full mx-4"
        @click.stop
      >
        <h3 class="text-xl font-bold mb-4">Create New Subject</h3>
        <input 
          v-model="newSubjectName" 
          type="text" 
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 mb-4"
          placeholder="Enter subject name"
        />
        <div class="flex gap-2">
          <button 
            @click="createSubject" 
            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            :disabled="!newSubjectName || loading"
          >
            {{ loading ? 'Creating...' : 'Create' }}
          </button>
          <button 
            @click="closeCreateSubjectModal" 
            class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Subjects',
  data() {
    return {
      subjects: [],
      allSubjects: [],
      levels: {},
      allLevelsFlat: [],
      searchQuery: '',
      selectedSubject: null,
      isEditing: false,
      editingSubjectId: null,
      fromLevelId: '',
      toLevelId: '',
      showDropdown: false,
      showCreateModal: false,
      newSubjectName: '',
      loading: false,
      message: '',
      error: '',
    };
  },
  computed: {
    filteredSubjects() {
      if (!this.searchQuery) return this.allSubjects;
      const query = this.searchQuery.toLowerCase();
      return this.allSubjects.filter(subject => 
        subject.name.toLowerCase().includes(query)
      );
    },
    filteredToLevels() {
      if (!this.fromLevelId) return {};

      const fromLevel = this.allLevelsFlat.find(l => l.id == this.fromLevelId);
      if (!fromLevel) return {};

      const groupName = fromLevel.group_name;
      const groupLevels = (this.levels[groupName] || []).filter(
        level => level.value > fromLevel.value
      );
      return groupLevels.length ? { [groupName]: groupLevels } : {};
    },
  },
  mounted() {
    this.fetchSubjects();
    this.fetchAllSubjects();
    this.fetchLevels();
  },
  methods: {
    isSubjectDisabled(subject) {
      // Disable if subject is already in the user's subject list
      return this.subjects.some(s => s.id === subject.id);
    },
    onDropdownItemMouseDown(subject) {
      if (this.isSubjectDisabled(subject)) return;
      this.selectSubject(subject);
    },
    async fetchSubjects() {
      try {
        const response = await axios.get('/api/tutor/profile/subjects');
        this.subjects = response.data.subjects || [];
      } catch (err) {
        this.error = 'Failed to load subjects';
      }
    },
    async fetchAllSubjects() {
      try {
        const response = await axios.get('/api/tutor/profile/subjects/all');
        this.allSubjects = response.data.subjects || [];
      } catch (err) {
        console.error('Failed to load all subjects');
      }
    },
    async fetchLevels() {
      try {
        const response = await axios.get('/api/tutor/profile/levels/all');
        console.log(response.data);
        this.levels = response.data.levels || {};
        
        // Flatten levels for easier lookup
        this.allLevelsFlat = [];
        Object.values(this.levels).forEach(groupLevels => {
          this.allLevelsFlat.push(...groupLevels);
        });
      } catch (err) {
        console.error('Failed to load levels');
      }
    },
    selectSubject(subject) {
      this.selectedSubject = subject;
      this.searchQuery = subject.name;
      this.showDropdown = false;
    },
    hideDropdown() {
      setTimeout(() => {
        this.showDropdown = false;
      }, 200);
    },
    updateToLevelOptions() {
      // Reset to level if from level changes
      const fromLevel = this.allLevelsFlat.find(l => l.id == this.fromLevelId);
      const toLevel = this.allLevelsFlat.find(l => l.id == this.toLevelId);
      if (!fromLevel) {
        this.toLevelId = '';
        return;
      }
      // Ensure same group and strictly greater value
      if (
        toLevel && (
          toLevel.group_name !== fromLevel.group_name ||
          toLevel.value <= fromLevel.value
        )
      ) {
        this.toLevelId = '';
      }
    },
    getLevelName(levelId) {
      const level = this.allLevelsFlat.find(l => l.id == levelId);
      return level ? level.name : '';
    },
    async addSubject() {
      if (!this.selectedSubject || !this.fromLevelId || !this.toLevelId) return;

      try {
        this.loading = true;
        this.error = '';
        const fromLevel = this.allLevelsFlat.find(l => l.id == this.fromLevelId);
        const toLevel = this.allLevelsFlat.find(l => l.id == this.toLevelId);
        if (!fromLevel || !toLevel) {
          this.error = 'Please select valid levels';
          this.loading = false;
          return;
        }
        if (fromLevel.group_name !== toLevel.group_name) {
          this.error = 'Levels must be from the same group';
          this.loading = false;
          return;
        }
        if (toLevel.value <= fromLevel.value) {
          this.error = 'Highest level must be greater than lowest level';
          this.loading = false;
          return;
        }
        let response;
        if (this.isEditing && this.editingSubjectId) {
          // Update existing subject levels
          response = await axios.patch(`/api/tutor/profile/subjects/${this.editingSubjectId}`, {
            from_level_id: this.fromLevelId,
            to_level_id: this.toLevelId,
          });
        } else {
          // Add new subject
          response = await axios.post('/api/tutor/profile/subjects/add', {
            subject_id: this.selectedSubject.id,
            from_level_id: this.fromLevelId,
            to_level_id: this.toLevelId,
          });
        }
        this.message = response.data?.message || 'Data saved successfully.';
        this.subjects = response.data.subjects;
        this.resetForm();
        setTimeout(() => this.message = '', 3000);
      } catch (err) {
        this.error = err.response?.data?.message || (this.isEditing ? 'Failed to update subject' : 'Failed to add subject');
      } finally {
        this.loading = false;
      }
    },
    cancelEdit() {
      this.resetForm();
    },
    startEditSubject(subject) {
      // Prefill form with existing subject and levels
      this.isEditing = true;
      this.editingSubjectId = subject.id;
      const match = this.allSubjects.find(s => s.id === subject.id);
      this.selectedSubject = match ? match : { id: subject.id, name: subject.name };
      this.searchQuery = this.selectedSubject.name;
      this.fromLevelId = subject.from_level_id || '';
      this.toLevelId = subject.to_level_id || '';
      this.showDropdown = false;
      // Ensure levels are filtered appropriately when editing
      this.updateToLevelOptions();
    },
    resetForm() {
      this.isEditing = false;
      this.editingSubjectId = null;
      this.selectedSubject = null;
      this.searchQuery = '';
      this.fromLevelId = '';
      this.toLevelId = '';
    },
    async removeSubject(subjectId) {
      try {
        this.loading = true;
        this.error = '';
        const response = await axios.delete(`/api/tutor/profile/subjects/${subjectId}`);
        this.subjects = response.data.subjects;
        this.message = 'Subject removed';
        setTimeout(() => this.message = '', 3000);
      } catch (err) {
        this.error = 'Failed to remove subject';
      } finally {
        this.loading = false;
      }
    },
    openCreateSubjectModal() {
      this.showDropdown = false;
      this.showCreateModal = true;
      this.newSubjectName = this.searchQuery;
    },
    closeCreateSubjectModal() {
      this.showCreateModal = false;
      this.newSubjectName = '';
    },
    async createSubject() {
      if (!this.newSubjectName.trim()) return;

      try {
        this.loading = true;
        this.error = '';
        const response = await axios.post('/api/tutor/profile/subjects/create', {
          name: this.newSubjectName,
        });
        
        this.message = response.data?.message || 'Data saved successfully.';
        const newSubject = response.data.subject;
        
        // Add to all subjects list
        this.allSubjects.push(newSubject);
        
        // Select the newly created subject
        this.selectSubject(newSubject);
        
        // Close modal
        this.closeCreateSubjectModal();
        
        setTimeout(() => this.message = '', 3000);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to create subject';
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
