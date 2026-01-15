<template>
  <transition name="fade">
    <div
      v-if="visible"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
      aria-modal="true"
      role="dialog"
      @click.self="$emit('close')"
    >
      <div class="relative w-full max-w-5xl max-h-[90vh] overflow-hidden rounded-2xl bg-white shadow-2xl">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 bg-gradient-to-r from-pink-500 to-purple-600">
          <div class="flex items-center gap-3 text-white">
            <i class="fas fa-file-invoice text-2xl"></i>
            <div>
              <p class="text-xs uppercase tracking-wide opacity-90">Invoice</p>
              <h3 class="text-xl font-bold">{{ invoiceNumber }}</h3>
            </div>
          </div>
          <button 
            @click="$emit('close')" 
            class="rounded-full p-2 text-white hover:bg-white/20 transition" 
            aria-label="Close modal"
          >
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>

        <!-- Invoice Content -->
        <div class="overflow-y-auto max-h-[calc(90vh-80px)] bg-gray-50 p-6">
          <InvoiceViewer v-if="invoiceId" :invoiceId="invoiceId" />
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import InvoiceViewer from './InvoiceViewer.vue';

export default {
  name: 'InvoiceModal',
  components: {
    InvoiceViewer
  },
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    invoiceId: {
      type: [String, Number],
      default: null
    },
    invoiceNumber: {
      type: String,
      default: 'Invoice'
    }
  },
  emits: ['close']
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.fade-enter-active > div,
.fade-leave-active > div {
  transition: transform 0.3s ease;
}

.fade-enter-from > div {
  transform: scale(0.95);
}

.fade-leave-to > div {
  transform: scale(0.95);
}
</style>
