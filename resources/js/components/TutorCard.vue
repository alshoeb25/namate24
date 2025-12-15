<template>
  <div class="card">
    <div class="card-header">
      <h3>{{ tutor.user?.name || 'Tutor' }}</h3>
      <div class="badges">
        <span v-if="tutor.verified" class="badge verified">Verified</span>
        <span v-if="tutor.badges?.includes('top_rated')" class="badge top">Top</span>
      </div>
    </div>

    <div class="card-body">
      <div class="headline">{{ tutor.headline }}</div>
      <div class="subjects">
        <span v-for="s in tutor.subjects" :key="s.id" class="subject">{{ s.name }}</span>
      </div>
      <div class="meta">
        <div>₹{{ tutor.price_per_hour }}/hr</div>
        <div>{{ tutor.city }}</div>
        <div>Rating: {{ tutor.rating_avg || '-' }}</div>
      </div>
    </div>

    <div class="card-footer">
      <router-link :to="{ name: 'tutor.show', params: { id: tutor.id } }">
        <button>View</button>
      </router-link>
      <button @click="message">Send Message</button>
      <button @click="requestTutor">Request Tutor</button>
    </div>
  </div>
</template>

<script>
export default {
  props: { tutor: { type: Object, required: true } },
  methods: {
    message() {
      // TODO: open messaging / contact flow
      alert('Open messaging flow - not implemented');
    },
    requestTutor() {
      // TODO: open request form / booking
      alert('Open request/booking flow - not implemented');
    }
  }
};
</script>

<style scoped>
.card { border:1px solid #e5e7eb; padding:12px; border-radius:8px; background:#fff; display:flex; flex-direction:column; justify-content:space-between; height:220px; }
.card-header { display:flex; justify-content:space-between; align-items:flex-start; }
.headline { font-weight:600; margin:8px 0; }
.subject { display:inline-block; background:#f3f4f6; padding:3px 8px; border-radius:4px; margin-right:6px; margin-bottom:6px; }
.card-footer { display:flex; gap:8px; }
.badge { padding:4px 6px; border-radius:4px; font-size:12px; }
.verified { background: #ecfccb; color:#15803d; }
.top { background:#fde68a; color:#92400e; }
</style>