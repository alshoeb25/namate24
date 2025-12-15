<template>
  <div class="max-w-4xl mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold">Conversation {{ id }}</h2>
      <router-link to="/conversations" class="text-sm text-gray-500 hover:underline">Back</router-link>
    </div>

    <div class="border rounded p-4 h-[60vh] overflow-auto mb-4 bg-white" ref="messagesBox">
      <div v-for="m in messages" :key="m.id" class="mb-3">
        <div class="text-sm text-gray-600">{{ m.sender_name || m.sender_id }} <span class="text-xs text-gray-400">• {{ prettyDate(m.created_at) }}</span></div>
        <div class="mt-1 p-2 rounded bg-gray-100 inline-block">{{ m.body }}</div>
      </div>
    </div>

    <form @submit.prevent="sendMessage" class="flex gap-2">
      <input v-model="body" placeholder="Type a message…" class="flex-1 p-2 border rounded" />
      <button type="submit" class="bg-emerald-500 text-white px-4 py-2 rounded">Send</button>
    </form>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

export default {
  setup() {
    const route = useRoute();
    const id = route.params.id;
    const messages = ref([]);
    const body = ref('');
    const messagesBox = ref(null);

    async function fetchMessages() {
      const res = await axios.get(`/api/conversations/${id}/messages`);
      messages.value = res.data;
      scrollBottom();
    }

    function scrollBottom() {
      nextTick(() => {
        if (messagesBox.value) messagesBox.value.scrollTop = messagesBox.value.scrollHeight;
      });
    }

    async function sendMessage() {
      if (!body.value.trim()) return;
      try {
        const res = await axios.post(`/api/conversations/${id}/messages`, { body: body.value });
        messages.value.push(res.data);
        body.value = '';
        scrollBottom();
      } catch (e) {
        console.error(e);
        alert('Failed to send message');
      }
    }

    onMounted(fetchMessages);
    return { id, messages, body, sendMessage, messagesBox, prettyDate: (d)=> new Date(d).toLocaleString() };
  }
};
</script>