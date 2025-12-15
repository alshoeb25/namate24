<template>
  <div v-if="loading">Loading...</div>
  <div v-else class="profile">
    <h1>{{ tutor.user?.name }}</h1>
    <div class="meta">
      <div>{{ tutor.city }} • ₹{{ tutor.price_per_hour }}/hr • {{ tutor.experience_years }} years</div>
      <div>Rating: {{ tutor.rating_avg }} ({{ tutor.rating_count }})</div>
    </div>

    <div class="about">
      <h3>About</h3>
      <p>{{ tutor.about }}</p>
    </div>

    <div class="subjects">
      <h4>Subjects</h4>
      <div v-for="s in tutor.subjects" :key="s.id">{{ s.name }} <small v-if="s.pivot?.level">({{ s.pivot.level }})</small></div>
    </div>

    <div class="actions">
      <button @click="sendMessage">Send Message</button>
      <button @click="requestTutor">Request Tutor</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

export default {
  setup() {
    const route = useRoute();
    const id = route.params.id;
    const tutor = ref(null);
    const loading = ref(true);

    async function load() {
      loading.value = true;
      try {
        const res = await axios.get(`/api/tutors/${id}`);
        tutor.value = res.data;
      } catch (e) {
        console.error(e);
        alert('Tutor not found or not available');
      } finally {
        loading.value = false;
      }
    }

    function sendMessage() {
      alert('Open messaging to tutor - integrate conversations');
    }
    function requestTutor() {
      alert('Open booking/request flow');
    }

    onMounted(load);
    return { tutor, loading, sendMessage, requestTutor };
  }
};
</script>

<style scoped>
.profile { padding:20px; background:#fff; border-radius:8px; border:1px solid #eee; }
.meta { color:#666; margin-bottom:12px; }
.actions { margin-top:12px; display:flex; gap:8px; }
</style>