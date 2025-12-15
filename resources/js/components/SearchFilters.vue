<template>
  <div class="filters-panel">
    <h4>Filters</h4>
    <div>
      <label>Subject</label>
      <select v-model="form.subject_id">
        <option value="">All</option>
        <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
      </select>
    </div>

    <div>
      <label>City</label>
      <input v-model="form.city" placeholder="City" />
    </div>

    <div>
      <label>Price range</label>
      <input v-model.number="form.min_price" placeholder="Min" type="number" />
      <input v-model.number="form.max_price" placeholder="Max" type="number" />
    </div>

    <div>
      <label>Mode</label>
      <select v-model="form.mode">
        <option value="">Any</option>
        <option value="online">Online</option>
        <option value="offline">Offline</option>
      </select>
    </div>

    <div style="margin-top:8px;">
      <button @click="apply">Apply</button>
      <button @click="reset">Reset</button>
    </div>
  </div>
</template>

<script>
import { reactive } from 'vue';
export default {
  props: { subjects: { type: Array, default: () => [] } },
  emits: ['search'],
  setup(props, { emit }) {
    const form = reactive({
      subject_id: '',
      city: '',
      min_price: '',
      max_price: '',
      mode: ''
    });

    function apply() {
      emit('search', { ...form });
    }

    function reset() {
      form.subject_id = '';
      form.city = '';
      form.min_price = '';
      form.max_price = '';
      form.mode = '';
      emit('search', { ...form });
    }

    return { form, apply, reset };
  }
};
</script>

<style scoped>
.filters-panel { background:#fff; padding:12px; border:1px solid #e6e6f0; border-radius:8px; }
.filters-panel label { display:block; font-size:12px; margin-bottom:4px; color:#333; }
.filters-panel input, .filters-panel select { width:100%; margin-bottom:8px; padding:6px; border:1px solid #ddd; border-radius:4px; }
</style>