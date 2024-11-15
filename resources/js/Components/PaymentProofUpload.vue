<template>
  <div class="mt-2 space-y-2">
    <div v-if="!purchase.payment_proof">
      <form @submit.prevent="uploadPaymentProof" class="space-y-2">
        <div class="flex items-center space-x-2">
          <input 
            type="file" 
            ref="paymentProofInput" 
            @change="handleFileChange"
            accept="image/*"
            class="hidden"
          />
          <button 
            type="button"
            @click="$refs.paymentProofInput.click()"
            class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm"
          >
            Select Payment Proof
          </button>
          <span v-if="selectedFile" class="text-sm text-gray-600">
            {{ selectedFile.name }}
          </span>
        </div>
        
        <button 
          v-if="selectedFile"
          type="submit"
          :disabled="form.processing"
          class="w-full px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm"
        >
          {{ form.processing ? 'Uploading...' : 'Upload Payment Proof' }}
        </button>
      </form>
    </div>

    <div v-else class="text-sm">
      <div class="flex items-center space-x-2">
        <span :class="{
          'text-yellow-600': purchase.payment_status === 'pending',
          'text-green-600': purchase.payment_status === 'verified',
          'text-red-600': purchase.payment_status === 'rejected'
        }">
          <template v-if="purchase.payment_status === 'pending'">
            ⏳ Payment proof under review
          </template>
          <template v-if="purchase.payment_status === 'verified'">
            ✓ Payment verified
          </template>
          <template v-if="purchase.payment_status === 'rejected'">
            ✗ Payment proof rejected
          </template>
        </span>
        
        <a 
          v-if="purchase.payment_proof"
          :href="`/storage/${purchase.payment_proof}`"
          target="_blank"
          class="text-blue-500 hover:text-blue-700 underline"
        >
          View Proof
        </a>
        
        <button 
          v-if="purchase.payment_status === 'rejected'"
          @click="$refs.paymentProofInput.click()"
          class="text-blue-500 hover:text-blue-700"
        >
          Upload New Proof
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  purchase: {
    type: Object,
    required: true
  }
});

const paymentProofInput = ref(null);
const selectedFile = ref(null);
const form = useForm({
  payment_proof: null
});

function handleFileChange(e) {
  const file = e.target.files[0];
  if (file) {
    selectedFile.value = file;
    form.payment_proof = file;
  }
}

function uploadPaymentProof() {
  if (!selectedFile.value) return;

  form.post(route('purchase.upload-payment', props.purchase.id), {
    preserveScroll: true,
    onSuccess: () => {
      selectedFile.value = null;
      form.reset();
    }
  });
}
</script> 