<template>
  <div class="tutor-card bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-all duration-300">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-start justify-between mb-6">
      <div class="flex-1">
        <div class="flex items-center gap-3 mb-2">
          <h2 class="text-xl font-bold text-gray-900 hover:text-blue-600 transition-colors">
            <router-link :to="{ name: 'tutor.show', params: { id: tutor.id } }">
              {{ tutor.user?.name || 'Tutor' }}
            </router-link>
          </h2>
          <span v-if="tutor.level" :class="`px-3 py-1 bg-${levelColor}-100 text-${levelColor}-600 rounded-full text-xs font-medium`">
            {{ levelText }}
          </span>
          <span v-if="tutor.verified" class="px-3 py-1 bg-green-100 text-green-600 rounded-full text-xs font-medium">
            <i class="fas fa-check-circle"></i> Verified
          </span>
        </div>
        <div class="flex items-center gap-4 text-gray-600 text-sm mb-3">
          <span class="flex items-center gap-1">
            <i class="fas fa-star text-yellow-400"></i>
            <span v-if="tutor.rating_avg">
              {{ tutor.rating_avg }} ({{ tutor.rating_count ?? tutor.reviews_count ?? 0 }} reviews)
            </span>
            <span v-else>No reviews</span>
          </span>
          <span class="flex items-center gap-1">
            <i :class="modeIcon"></i>
            {{ modeText }}
          </span>
        </div>
      </div>
      <div v-if="tutor.badges?.includes('top_rated')" class="mt-3 sm:mt-0">
        <div class="px-4 py-2 bg-gradient-to-r from-yellow-100 to-yellow-200 rounded-lg font-bold text-sm text-yellow-800">
          <i class="fas fa-trophy"></i> Top Rated
        </div>
      </div>
    </div>

    <!-- Skills/Subjects -->
    <div class="flex flex-wrap gap-2 mb-6">
      <span 
        v-for="subject in tutor.subjects" 
        :key="subject.id" 
        class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 text-sm hover:bg-blue-50 cursor-pointer transition-colors"
      >
        {{ subject.name }}
      </span>
      <span 
        v-for="(skill, idx) in displaySkills" 
        :key="`skill-${idx}`" 
        class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 text-sm hover:bg-blue-50 cursor-pointer transition-colors"
      >
        {{ skill }}
      </span>
    </div>

    <!-- Bio and Logo -->
    <div class="flex flex-col md:flex-row gap-6 mb-6">
      <div class="md:w-3/4">
        <p v-if="tutor.bio" class="text-gray-700 leading-relaxed line-clamp-3">
          {{ tutor.bio }}
        </p>
        <p v-else class="text-gray-700 leading-relaxed line-clamp-3">
          {{ tutor.headline || 'Professional tutor with years of experience.' }}
        </p>
      </div>
      <div v-if="tutor.organization" class="md:w-1/4 flex justify-center">
        <div class="w-24 h-24 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl flex items-center justify-center p-4">
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-800">{{ organizationShort }}</div>
            <div v-if="organizationRest" class="text-gray-600 text-sm">{{ organizationRest }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Expertise -->
    <div v-if="tutor.expertise || tutor.specializations?.length" class="mb-6">
      <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
        <i class="fas fa-tools text-blue-600"></i>
        Areas of Expertise
      </h3>
      <div class="bg-gray-50 rounded-lg p-4">
        <p v-if="tutor.expertise" class="text-gray-700 whitespace-pre-line">{{ tutor.expertise }}</p>
        <div v-else class="flex flex-wrap gap-2">
          <span 
            v-for="spec in tutor.specializations" 
            :key="spec" 
            class="text-gray-700"
          >
            • {{ spec }}
          </span>
        </div>
      </div>
    </div>

    <!-- Footer Info -->
    <div class="pt-6 border-t border-gray-200">
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm mb-6">
        <!-- Location -->
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
            <i class="fas fa-map-marker-alt text-blue-600"></i>
          </div>
          <div>
            <div class="text-gray-500">Location</div>
            <div class="font-medium">{{ tutor.city || 'N/A' }}</div>
          </div>
        </div>

        <!-- Price -->
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
            <i class="fas fa-rupee-sign text-green-600"></i>
          </div>
          <div>
            <div class="text-gray-500">Price</div>
            <div class="font-medium">₹{{ tutor.price_per_hour || '0' }}/hr</div>
          </div>
        </div>

        <!-- Experience -->
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
            <i class="fas fa-briefcase text-purple-600"></i>
          </div>
          <div>
            <div class="text-gray-500">Experience</div>
            <div class="font-medium">{{ tutor.experience_years || '0' }} yr.</div>
          </div>
        </div>

        <!-- Teaching -->
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
            <i class="fas fa-user-graduate text-yellow-600"></i>
          </div>
          <div>
            <div class="text-gray-500">Teaching</div>
            <div class="font-medium">{{ tutor.teaching_years || tutor.experience_years || '0' }} yr.</div>
          </div>
        </div>

        <!-- Students Taught -->
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
            <i class="fas fa-users text-red-600"></i>
          </div>
          <div>
            <div class="text-gray-500">Students</div>
            <div class="font-medium">{{ tutor.students_count || '0' }}+</div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  props: { 
    tutor: { 
      type: Object, 
      required: true 
    } 
  },
  data() {
    return {
      isFavorite: false
    };
  },
  computed: {
    levelColor() {
      const colors = {
        'beginner': 'green',
        'intermediate': 'blue',
        'advanced': 'purple',
        'expert': 'red'
      };
      return colors[this.tutor.level] || 'gray';
    },
    levelText() {
      const texts = {
        'beginner': 'Beginner',
        'intermediate': 'Intermediate',
        'advanced': 'Advanced',
        'expert': 'Expert'
      };
      return texts[this.tutor.level] || 'All Levels';
    },
    modeIcon() {
      const icons = {
        'online': 'fas fa-laptop',
        'home': 'fas fa-home',
        'assignment': 'fas fa-tasks',
        'offline': 'fas fa-user',
        'both': 'fas fa-exchange-alt'
      };
      return icons[this.tutor.mode] || 'fas fa-laptop';
    },
    modeText() {
      if (!this.tutor.mode) return 'Online';
      return this.tutor.mode.charAt(0).toUpperCase() + this.tutor.mode.slice(1);
    },
    displaySkills() {
      // If tutor has a skills array separate from subjects
      return this.tutor.skills || [];
    },
    organizationShort() {
      if (!this.tutor.organization) return '';
      return this.tutor.organization.split(' ')[0];
    },
    organizationRest() {
      if (!this.tutor.organization) return '';
      const parts = this.tutor.organization.split(' ');
      return parts.length > 1 ? parts[1] : '';
    }
  },
  methods: {
    toggleFavorite() {
      this.isFavorite = !this.isFavorite;
      this.$emit('favorite', this.tutor);
    }
  }
};
</script>

<style scoped>
.tutor-card {
  /* Card is styled via Tailwind classes in template */
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Remove default link styling */
a {
  text-decoration: none;
  color: inherit;
}

a:hover {
  color: inherit;
}
</style>