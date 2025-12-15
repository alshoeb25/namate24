<template>
  <div class="max-w-2xl mx-auto p-4">
    <h2 class="text-xl font-semibold mb-4">Request Booking</h2>
    <form @submit.prevent="submit" class="space-y-3 bg-white p-4 rounded shadow">
      <div>
        <label class="block text-sm font-medium">Tutor ID</label>
        <input v-model="form.tutor_id" class="mt-1 p-2 border rounded w-full" />
      </div>

      <div>
        <label class="block text-sm font-medium">Start time</label>
        <input v-model="form.start_at" type="datetime-local" class="mt-1 p-2 border rounded w-full" />
      </div>

      <div>
        <label class="block text-sm font-medium">End time</label>
        <input v-model="form.end_at" type="datetime-local" class="mt-1 p-2 border rounded w-full" />
      </div>

      <div>
        <label class="block text-sm font-medium">Session price</label>
        <input v-model="form.session_price" type="number" class="mt-1 p-2 border rounded w-full" />
      </div>

      <div class="text-right">
        <button class="bg-emerald-500 text-white px-4 py-2 rounded">Request & Pay</button>
      </div>
    </form>
  </div>
</template>

<script>
import { reactive } from 'vue';
import { useRouter } from 'vue-router';

export default {
  setup() {
    const router = useRouter();
    const form = reactive({
      tutor_id: '',
      start_at: '',
      end_at: '',
      session_price: '',
    });

    async function submit() {
      try {
        const res = await axios.post('/api/bookings', form);
        alert('Booking requested. Proceed to payment if required.');
        router.push('/bookings');
      } catch (e) {
        console.error(e);
        alert('Failed to create booking');
      }
    }

    return { form, submit };
  }
};
</script>