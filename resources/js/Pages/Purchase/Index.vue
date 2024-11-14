<template>
  <AuthenticatedLayout>
    <Head title="Purchase History" />

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
      <h2 class="text-2xl font-semibold mb-6 text-center">Purchase History</h2>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash.success" 
           class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ $page.props.flash.success }}
      </div>

      <div v-if="$page.props.flash.error"
           class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ $page.props.flash.error }}
      </div>

      <div v-if="purchases.length === 0" class="flex items-center justify-center h-64">
        <div class="flex flex-col items-center">
          <div class="text-center text-gray-500 mb-4">
            No purchases found.
          </div>
          <div class="animate-float">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18M3 3l3 18h12l3-18M3 3l3 18h12l3-18" />
            </svg>
          </div>
        </div>
      </div>

      <div v-else>
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medicine</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pickup Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="purchase in purchases" :key="purchase.id">
              <td class="px-6 py-4">{{ purchase.name }}</td>
              <td class="px-6 py-4">{{ purchase.quantity }}</td>
              <td class="px-6 py-4">₱{{ purchase.total_amount }}</td>
              <td class="px-6 py-4">
                <span :class="{
                  'px-2 py-1 rounded text-xs font-medium': true,
                  'bg-yellow-100 text-yellow-800': purchase.status === 'pending',
                  'bg-blue-100 text-blue-800': purchase.status === 'confirmed',
                  'bg-green-100 text-green-800': purchase.status === 'completed',
                  'bg-red-100 text-red-800': purchase.status === 'cancelled'
                }">
                  {{ purchase.status }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div v-if="purchase.ready_for_pickup" class="space-y-2">
                  <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium block">
                    Ready for Pickup
                  </span>
                  <template v-if="purchase.pickup_deadline">
                    <p class="text-xs text-gray-500">
                      Pickup Before: {{ new Date(purchase.pickup_deadline).toLocaleString() }}
                    </p>
                    <p class="text-xs font-medium" :class="{
                      'text-red-600': purchase.time_remaining === 'Expired',
                      'text-blue-600': purchase.time_remaining !== 'Expired'
                    }">
                      {{ purchase.time_remaining }}
                    </p>
                  </template>
                  <div v-if="purchase.admin_pickup_verified && !purchase.user_pickup_verified" 
                       class="text-yellow-600 text-xs">
                    Waiting for your verification
                  </div>
                  <div v-if="purchase.user_pickup_verified" class="text-green-600 text-xs">
                    You verified pickup ✓
                  </div>
                </div>
                <span v-else-if="purchase.status === 'cancelled'" 
                      class="text-red-500 text-sm">
                  Cancelled
                </span>
                <span v-else class="text-gray-500 text-sm">
                  Not Ready Yet
                </span>
              </td>
              <td class="px-6 py-4">{{ new Date(purchase.created_at).toLocaleDateString() }}</td>
              <td class="px-6 py-4 space-y-2">
                <!-- Cancel button for pending purchases -->
                <button v-if="purchase.status === 'pending'"
                        @click="confirmCancel(purchase)" 
                        class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded text-sm">
                  Cancel
                </button>

                <!-- Verify Pickup button -->
                <button v-if="purchase.ready_for_pickup && !purchase.user_pickup_verified"
                        @click="confirmPickupVerification(purchase)" 
                        class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm">
                  Verify Pickup
                </button>

                <!-- Status indicators -->
                <span v-if="purchase.status === 'completed'" 
                      class="block text-center text-green-600 text-sm">
                  Completed ✓
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
  purchases: Array,
});

function confirmCancel(purchase) {
  Swal.fire({
    title: 'Cancel Purchase',
    text: `Are you sure you want to cancel this purchase of ${purchase.name}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, cancel it!',
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    cancelButtonText: 'No, keep it'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('purchase.cancel', purchase.id), {
        onSuccess: () => {
          Swal.fire({
            title: 'Cancelled!',
            text: 'Your purchase has been successfully canceled.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
        }
      });
    }
  });
}

function confirmPickupVerification(purchase) {
  Swal.fire({
    title: 'Verify Pickup',
    text: 'Confirm that you have picked up your medicine?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, I have it',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    cancelButtonText: 'Not yet'
  }).then((result) => {
    if (result.isConfirmed) {
      router.post(route('purchase.verify-pickup', purchase.id), {}, {
        onSuccess: () => {
          Swal.fire({
            title: 'Verified!',
            text: 'Thank you for confirming your pickup.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
        },
        onError: () => {
          Swal.fire({
            title: 'Error!',
            text: 'Failed to verify pickup. Please try again.',
            icon: 'error'
          });
        }
      });
    }
  });
}
</script>
