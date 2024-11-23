<template>
  <div class="space-y-2">
    <!-- Confirm button -->
    <button v-if="purchase.status === 'pending'" 
      @click="$emit('confirm', purchase.transaction_id)"
      class="w-full px-4 py-2 text-sm text-white bg-blue-500 rounded hover:bg-blue-600">
      Confirm
    </button>

    <!-- Ready for pickup button -->
    <button v-if="purchase.status === 'confirmed' && !purchase.ready_for_pickup && purchase.payment_status === 'verified'"
      @click="$emit('ready', purchase.transaction_id)"
      class="w-full px-4 py-2 text-sm text-white bg-green-500 rounded hover:bg-green-600">
      Mark Ready
    </button>

    <!-- Verify pickup button -->
    <button v-if="purchase.ready_for_pickup && purchase.user_pickup_verified && !purchase.admin_pickup_verified"
      @click="$emit('verify-pickup', purchase.transaction_id)"
      class="w-full px-4 py-2 text-sm text-white bg-purple-500 rounded hover:bg-purple-600">
      Verify Pickup
    </button>

    <!-- Print receipt button -->
    <button v-if="purchase.status === 'completed'"
      @click="$emit('print-receipt', purchase.transaction_id)"
      class="w-full px-4 py-2 text-sm text-green-600 bg-green-100 rounded hover:bg-green-200">
      Print Receipt
    </button>
  </div>
</template>

<script setup>
defineProps({
  purchase: {
    type: Object,
    required: true
  }
});

defineEmits(['confirm', 'ready', 'verify-pickup', 'print-receipt']);
</script>
