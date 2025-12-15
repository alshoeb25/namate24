<template>
  <div>
    <HeaderRoot />

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto p-4">
      <router-view />
    </main>
  </div>
</template>

<script>
import { useUserStore } from '../store';
import { useRouter } from 'vue-router';
import { computed, onMounted } from 'vue';
import HeaderRoot from '../components/header/HeaderRoot.vue';

export default {
  components: {
    HeaderRoot,
  },
  setup() {
    const store = useUserStore();
    const router = useRouter();
    const user = computed(() => store.user);

    onMounted(() => {
      // Hydrate user on refresh if token exists
      if (!store.user && store.token) {
        store.fetchUser();
      }
    });

    return { user };
  }
};
</script>