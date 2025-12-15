<template>
  <div class="pagination" v-if="meta && meta.last_page > 1">
    <button :disabled="meta.current_page === 1" @click="$emit('page', meta.current_page - 1)">Prev</button>
    <span>Page {{ meta.current_page }} / {{ meta.last_page }}</span>
    <button :disabled="meta.current_page === meta.last_page" @click="$emit('page', meta.current_page + 1)">Next</button>
  </div>
</template>

<script>
export default {
  props: { meta: { type: Object, default: () => ({}) } }
};
</script>

<style scoped>
.pagination { display:flex; gap:8px; align-items:center; margin-top:12px; }
</style>