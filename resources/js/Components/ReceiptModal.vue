<template>
  <Modal :show="show" @close="$emit('close')" :closeable="true" maxWidth="4xl">
    <div class="p-6">
      <h2 class="text-lg font-medium text-gray-900 mb-4">
        Purchase Receipt
      </h2>
      <div class="relative" style="height: 80vh;">
        <iframe v-if="receiptUrl" :src="receiptUrl" class="w-full h-full" frameborder="0"></iframe>
      </div>
      <div class="mt-4 flex justify-end space-x-2">
        <button type="button"
          class="inline-flex justify-center rounded-md border border-transparent bg-blue-100 px-4 py-2 text-sm font-medium text-blue-900 hover:bg-blue-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
          @click="printReceipt">
          Print
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import Modal from '@/Components/Modal.vue';

defineProps({
  show: {
    type: Boolean,
    default: false
  },
  receiptUrl: {
    type: String,
    default: ''
  }
});

defineEmits(['close']);

function printReceipt() {
  const iframe = document.querySelector('iframe');
  if (iframe) {
    iframe.contentWindow.print();
  }
}
</script>
