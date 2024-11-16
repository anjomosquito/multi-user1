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

      <div v-if="$page.props.flash.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ $page.props.flash.error }}
      </div>

      <div v-if="purchases.length === 0" class="flex items-center justify-center h-64">
        <div class="flex flex-col items-center">
          <div class="text-center text-gray-500 mb-4">
            No purchases found.
          </div>
          <div class="animate-float">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-500" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 3h18M3 3l3 18h12l3-18M3 3l3 18h12l3-18" />
            </svg>
          </div>
        </div>
      </div>

      <div v-else>
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction No</th>
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
              <td class="px-6 py-4">{{ purchase.transaction_number }}</td>
              <td class="px-6 py-4">{{ purchase.name }}</td>
              <td class="px-6 py-4">{{ purchase.quantity }}</td>
              <td class="px-6 py-4">₱{{ purchase.total_amount }}</td>
              <td class="px-6 py-4">
                <span :class="{
                  'px-2 py-1 rounded text-xs font-medium': true,
                  'bg-yellow-100 text-yellow-800': purchase.status === 'pending',
                  'bg-blue-100 text-blue-800': purchase.status === 'confirmed',
                  'bg-green-200 text-green-800 hover:bg-green-300': purchase.status === 'completed',
                  'bg-red-100 text-red-800': purchase.status === 'cancelled'
                }">
                  {{ formatStatus(purchase.status) }}
                </span>
                <div v-if="purchase.status === 'rejected' || purchase.status === 'completed'" class="mt-2">
                  <button @click="viewReport(purchase)"
                    class="px-2 py-1 bg-green-200 hover:bg-green-300 text-gray-800 rounded text-xs font-medium">
                    View Report
                  </button>

                </div>
              </td>
              <td class="px-6 py-4">
                <div v-if="purchase.user_pickup_verified && purchase.admin_pickup_verified"
                  class="text-green-600 text-sm font-medium">
                  Done
                </div>
                <div v-else-if="purchase.ready_for_pickup" class="space-y-2">
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
                </div>
                <span v-else class="text-gray-500 text-sm">
                  Not Ready Yet
                </span>
              </td>
              <td class="px-6 py-4">{{ new Date(purchase.created_at).toLocaleDateString() }}</td>
              <td class="px-6 py-4 space-y-2">
                <!-- Cancel button for pending purchases -->
                <button v-if="purchase.status === 'pending'" @click="confirmCancel(purchase)"
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
                <span v-if="purchase.status === 'completed'" class="block text-center text-green-600 text-sm">
                  Completed ✓
                </span>

                <!-- Payment proof upload -->
                <PaymentProofUpload v-if="purchase.status === 'confirmed' || purchase.status === 'ready_for_pickup'"
                  :purchase="purchase" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Floating Modal -->
      <teleport to="body">
        <div v-if="selectedReport" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
          <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-lg transition-transform transform scale-95"
            role="dialog" aria-modal="true">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-800">Purchase Receipt</h3>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                  stroke="currentColor" class="w-6 h-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div class="mt-4 space-y-2">
              <p><strong>Transaction No:</strong> {{ selectedReport.transaction_number }}</p>
              <p><strong>Customer Name:</strong> {{ selectedReport.user?.name || 'Unknown User' }}</p>
              <p><strong>Medicine:</strong> {{ selectedReport.name }}</p>
              <p><strong>Quantity:</strong> {{ selectedReport.quantity }}</p>
              <p><strong>Total:</strong> ₱{{ selectedReport.total_amount }}</p>
              <p><strong>Status:</strong> {{ formatStatus(selectedReport.status) }}</p>
              <p><strong>Date:</strong> {{ new Date(selectedReport.created_at).toLocaleString() }}</p>
              <p v-if="selectedReport.pickup_deadline">
                <strong>Pickup Deadline:</strong>
                {{ new Date(selectedReport.pickup_deadline).toLocaleString() }}
              </p>
            </div>
            <button @click="closeModal"
              class="mt-6 w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition">
              Close
            </button>
          </div>
        </div>
      </teleport>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PaymentProofUpload from '@/Components/PaymentProofUpload.vue';

const props = defineProps({
  purchases: Array,
});

const selectedFile = ref(null);
const form = useForm({
  payment_proof: null
});

const selectedReport = ref(null);

function viewReport(purchase) {
  selectedReport.value = purchase;
}

function closeModal() {
  selectedReport.value = null;
}


function formatStatus(status) {
  switch (status) {
    case 'pending':
      return 'Pending';
    case 'confirmed':
      return 'Confirmed';
    case 'ready_for_pickup':
      return 'Ready for Pickup';
    case 'verified':
      return 'User Verified';
    case 'completed':
      return 'Completed';
    case 'cancelled':
      return 'Cancelled';
    default:
      return status;
  }
}

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

function handleFileChange(e) {
  selectedFile.value = e.target.files[0];
}

function uploadPaymentProof(purchaseId) {
  if (!selectedFile.value) return;

  const formData = new FormData();
  formData.append('payment_proof', selectedFile.value);

  form.post(route('purchase.upload-payment', purchaseId), {
    preserveScroll: true,
    onSuccess: () => {
      selectedFile.value = null;
    }
  });
}
</script>
