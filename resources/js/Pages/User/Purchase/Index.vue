<template>
  <!-- ... existing template code ... -->
  <td class="px-6 py-4 whitespace-nowrap">
    <span :class="{
      'px-2 py-1 rounded-full text-xs font-medium': true,
      'bg-yellow-100 text-yellow-800': purchase.status === 'pending',
      'bg-blue-100 text-blue-800': purchase.status === 'confirmed',
      'bg-purple-100 text-purple-800': purchase.status === 'verified',
      'bg-green-400 text-black-800': purchase.status === 'completed',
      'bg-red-100 text-red-800': purchase.status === 'cancelled'
    }">
      {{ formatStatus(purchase.status) }}
    </span>
    <div v-if="purchase.status === 'completed'" class="mt-2">
      <button @click="viewReport(purchase)"
        class="px-2 py-1 bg-green-200 hover:bg-green-300 text-gray-800 rounded text-xs font-medium">
        View Receipt
      </button>
    </div>
  </td>
  <!-- ... rest of the template ... -->
</template>

<script setup>
// ... existing imports ...
import { router } from '@inertiajs/vue3';

// Add the viewReport function
function viewReport(purchase) {
  window.open(route('purchase.report', purchase.id), '_blank');
}
</script> 