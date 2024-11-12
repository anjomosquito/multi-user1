<template>
  <AdminAuthenticatedLayout>
    <Head title="Purchase Management" />

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
      <!-- Flash Messages -->
      <div v-if="showFlashMessages.success" 
           class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ showFlashMessages.success }}
      </div>

      <div v-if="showFlashMessages.error"
           class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ showFlashMessages.error }}
      </div>

      <div v-if="showFlashMessages.warning"
           class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
        {{ showFlashMessages.warning }}
      </div>

      <div v-if="showFlashMessages.info"
           class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
        {{ showFlashMessages.info }}
      </div>

      <!-- Admin-only content -->
      <div v-if="isAdmin">
        Admin content here
      </div>

      <!-- Purchase List -->
      <div v-for="(userGroup, userId) in purchases" :key="userId" class="mb-8">
        <div v-for="purchase in userGroup.purchases" :key="purchase.id" 
             class="bg-white shadow rounded-lg p-6 mb-4">
          <div class="flex justify-between items-center">
            <!-- Purchase Details -->
            <div class="space-y-2">
              <h3 class="text-lg font-semibold">{{ purchase.name }}</h3>
              <p class="text-gray-600">Quantity: {{ purchase.quantity }}</p>
              <p class="text-gray-600">
                Status: 
                <span :class="{
                  'text-yellow-600': purchase.status === 'pending',
                  'text-green-600': purchase.status === 'confirmed'
                }" class="font-medium">
                  {{ purchase.status }}
                </span>
              </p>
            </div>

            <!-- Action Buttons -->
            <div class="space-x-2">
              <button v-if="purchase.status === 'pending'"
                      @click="confirmPurchase(purchase.id)"
                      :disabled="isLoading(purchase.id)"
                      class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 
                             disabled:opacity-50 disabled:cursor-not-allowed
                             transition-colors duration-200">
                <span v-if="isLoading(purchase.id)">
                  Processing...
                </span>
                <span v-else>
                  Confirm Purchase
                </span>
              </button>

              <button v-if="purchase.status === 'confirmed' && !purchase.ready_for_pickup"
                      @click="markAsReady(purchase.id)"
                      :disabled="isLoading(purchase.id)"
                      class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600
                             disabled:opacity-50 disabled:cursor-not-allowed
                             transition-colors duration-200">
                <span v-if="isLoading(purchase.id)">
                  Processing...
                </span>
                <span v-else>
                  Mark as Ready
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminAuthenticatedLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  purchases: {
    type: Object,
    required: true,
    default: () => ({})
  }
});

// Access auth data
const auth = computed(() => usePage().props.auth);
const isAdmin = computed(() => auth.value.isAdmin);

// Access flash messages
const flash = computed(() => usePage().props.flash);

// Access app settings
const appName = computed(() => usePage().props.app.name);

// Example usage of flash messages
const showFlashMessages = computed(() => ({
    success: flash.value.success,
    error: flash.value.error,
    warning: flash.value.warning,
    info: flash.value.info,
}));

// Loading states
const loadingStates = ref(new Set());

function setLoading(id, isLoading) {
  if (isLoading) {
    loadingStates.value.add(id);
  } else {
    loadingStates.value.delete(id);
  }
}

function isLoading(id) {
  return loadingStates.value.has(id);
}

function confirmPurchase(purchaseId) {
  if (confirm('Are you sure you want to confirm this purchase?')) {
    setLoading(purchaseId, true);
    
    router.post(route('admin.purchase.confirm', purchaseId), {}, {
      preserveScroll: true,
      onSuccess: () => {
        setLoading(purchaseId, false);
      },
      onError: (errors) => {
        setLoading(purchaseId, false);
        console.error('Confirmation error:', errors);
        alert('Failed to confirm purchase. Please try again.');
      }
    });
  }
}

function markAsReady(purchaseId) {
  if (confirm('Mark this purchase as ready for pickup?')) {
    setLoading(purchaseId, true);
    
    router.post(route('admin.purchase.ready', purchaseId), {}, {
      preserveScroll: true,
      onSuccess: () => {
        setLoading(purchaseId, false);
      },
      onError: (errors) => {
        setLoading(purchaseId, false);
        console.error('Ready status error:', errors);
        alert('Failed to mark purchase as ready. Please try again.');
      }
    });
  }
}
</script>
