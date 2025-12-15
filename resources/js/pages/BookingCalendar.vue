<template>
  <div class="max-w-6xl mx-auto p-4">
    <h2 class="text-xl font-semibold mb-4">My Calendar</h2>
    <FullCalendar :plugins="calendarPlugins" :initialView="'dayGridMonth'" :events="events" />
  </div>
</template>

<script>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import { ref, onMounted } from 'vue';

export default {
  components: { FullCalendar },
  setup(){
    const calendarPlugins = [dayGridPlugin];
    const events = ref([]);

    async function load() {
      try {
        const res = await axios.get('/api/bookings'); // expects user's bookings with start_at,end_at
        events.value = res.data.data.map(b => ({
          id: b.id,
          title: `Tutor ${b.tutor_id} • ₹${b.session_price}`,
          start: b.start_at,
          end: b.end_at
        }));
      } catch (e) { console.error(e); }
    }

    onMounted(load);
    return { calendarPlugins, events };
  }
}
</script>

<style>
/* FullCalendar needs its CSS: import globally in your main CSS bundling or include via CDN */
/* @import "@fullcalendar/core/main.css";
@import "@fullcalendar/daygrid/main.css"; */
</style>