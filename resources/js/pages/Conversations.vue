<template>
  <div class="max-w-6xl mx-auto p-4">
    <h2 class="text-2xl font-semibold mb-4">Conversations</h2>

    <div v-if="loading" class="text-gray-500">Loading...</div>

    <ul class="space-y-3">
      <li v-for="c in conversations" :key="c.id"
          class="p-3 rounded border hover:bg-gray-50 flex justify-between items-center">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-emerald-200 flex items-center justify-center text-emerald-700 font-bold">
            {{ c.id }}
          </div>
          <div>
            <div class="font-medium">Conversation #{{ c.id }}</div>
            <div class="text-sm text-gray-500">{{ c.last_message_preview || 'No messages yet' }}</div>
          </div>
        </div>
        <router-link :to="{ name: 'conversations.show', params: { id: c.id } }"
                     class="text-emerald-600 hover:underline">Open</router-link>
      </li>
    </ul>

    <div class="mt-6">
      <h3 class="font-medium mb-2">Start a new conversation</h3>
      <form @submit.prevent="createConversation" class="flex gap-2">
        <input v-model="newParticipants" placeholder="Enter comma-separated user IDs" class="flex-1 p-2 border rounded" />
        <button class="bg-emerald-500 text-white px-4 py-2 rounded">Create</button>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  setup() {
    const conversations = ref([]);
    const loading = ref(false);
    const newParticipants = ref('');

    async function fetch() {
      loading.value = true;
      try {
        const res = await axios.get('/api/conversations');
        conversations.value = res.data;
      } catch (e) {
        console.error(e);
      } finally {
        loading.value = false;
      }
    }

    async function createConversation() {
      try {
        const ids = newParticipants.value.split(',').map(s => s.trim()).filter(Boolean);
        if (!ids.length) return alert('Provide at least one participant id');
        const res = await axios.post('/api/conversations', { participant_ids: ids });
        newParticipants.value = '';
        // redirect to conversation
        window.location.href = `/conversations/${res.data.id}`;
      } catch (e) {
        console.error(e);
        alert('Could not create conversation');
      }
    }

    onMounted(fetch);
    return { conversations, loading, newParticipants, createConversation };
  }
};
</script>