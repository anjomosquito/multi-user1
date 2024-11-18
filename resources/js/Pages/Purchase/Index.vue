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
                  'bg-green-400 text-black-800': purchase.status === 'completed',
                  'bg-red-100 text-red-800': purchase.status === 'cancelled'
                }">
                  {{ formatStatus(purchase.status) }}
                </span>
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
                <!-- Cancel button -->
                <button v-if="purchase.status === 'pending'" @click="confirmCancel(purchase)"
                  class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded text-sm">
                  Cancel
                </button>

                <!-- Payment Status Messages -->
                <div v-if="purchase.payment_status === 'rejected'" class="text-sm text-red-600 mb-2">
                  Payment proof was rejected. Please upload a new one.
                </div>
                <div v-if="purchase.payment_status === 'pending'" class="text-sm text-amber-600 mb-2">
                  Payment proof is pending verification.
                </div>

                <!-- Payment Proof Upload -->
                <div v-if="(purchase.status === 'confirmed' || purchase.status === 'ready_for_pickup') && 
                         (purchase.payment_status !== 'verified' && 
                         (purchase.payment_status === 'pending' || purchase.payment_status === 'rejected' || !purchase.payment_status))"
                     class="space-y-2">
                  <input
                    type="file"
                    :ref="'fileInput' + purchase.id"
                    @change="(e) => handleFileUpload(e.target.files[0], purchase.id)"
                    accept="image/*,.pdf"
                    class="hidden"
                  />
                  <button
                    @click="$refs['fileInput' + purchase.id][0].click()"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 
                           rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  >
                    {{ purchase.payment_status === 'rejected' ? 'Upload New Payment Proof' : 'Upload Payment Proof' }}
                  </button>
                  <div v-if="purchase.payment_proof_url" class="text-sm text-gray-500">
                    Previous proof: 
                    <a :href="purchase.payment_proof_url" target="_blank" class="text-indigo-600 hover:text-indigo-800">
                      View
                    </a>
                  </div>
                </div>

                <!-- Verify Pickup button -->
                <button v-if="purchase.ready_for_pickup && !purchase.user_pickup_verified && purchase.payment_status === 'verified'"
                  @click="confirmPickupVerification(purchase)"
                  class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm">
                  Verify Pickup
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Report Modal -->
      <teleport to="body">
        <div v-if="selectedReport" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
          <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-lg">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold">Purchase Report</h3>
              <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                <span class="sr-only">Close</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <!-- Report Content -->
            <div class="space-y-4">
              <p><strong>Transaction ID:</strong> {{ selectedReport.transaction_number }}</p>
              <p><strong>Medicine:</strong> {{ selectedReport.name }}</p>
              <p><strong>Quantity:</strong> {{ selectedReport.quantity }}</p>
              <p><strong>Total Amount:</strong> ₱{{ selectedReport.total_amount }}</p>
              <p><strong>Status:</strong> {{ formatStatus(selectedReport.status) }}</p>
              <p><strong>Purchase Date:</strong> {{ new Date(selectedReport.created_at).toLocaleString() }}</p>
            </div>
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

const props = defineProps({
  purchases: Array
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
    case 'completed':
      return 'Completed';
    case 'cancelled':
      return 'Cancelled';
    case 'rejected':
      return 'Rejected';
    default:
      return status;
  }
}

function confirmCancel(purchase) {
  Swal.fire({
    title: 'Cancel Purchase',
    text: 'Are you sure you want to cancel this purchase?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, cancel it!',
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    cancelButtonText: 'No, keep it'
  }).then((result) => {
    if (result.isConfirmed) {
      router.post(route('purchase.cancel', purchase.id), {}, {
        onSuccess: () => {
          Swal.fire({
            title: 'Cancelled!',
            text: 'Your purchase has been cancelled.',
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
  if (purchase.payment_status !== 'verified') {
    Swal.fire({
      title: 'Cannot Verify Pickup',
      text: 'Please wait for admin to verify your payment proof first.',
      icon: 'warning',
      confirmButtonText: 'OK'
    });
    return;
  }

  Swal.fire({
    title: 'Verify Pickup',
    text: 'Are you sure you want to verify this pickup? This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, verify pickup',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      router.post(route('purchase.verify-pickup', purchase.id), {}, {
        onSuccess: () => {
          Swal.fire({
            title: 'Pickup Verified!',
            text: 'Thank you for verifying your pickup.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
        }
      });
    }
  });
}

const handleFileUpload = async (file, purchaseId) => {
  if (!file) return;

  const formData = new FormData();
  formData.append('payment_proof', file);

  try {
    await router.post(route('purchase.upload-payment', purchaseId), formData, {
      onSuccess: () => {
        Swal.fire({
          title: 'Success!',
          text: 'Payment proof uploaded successfully.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });
      },
      onError: (errors) => {
        console.error('Upload error:', errors);
        Swal.fire({
          title: 'Error!',
          text: errors.payment_proof || Object.values(errors)[0] || 'Failed to upload payment proof.',
          icon: 'error',
          confirmButtonText: 'OK'
        });
      }
    });
  } catch (error) {
    console.error('Unexpected error:', error);
    Swal.fire({
      title: 'Error!',
      text: 'An unexpected error occurred while uploading the payment proof. Please try again.',
      icon: 'error',
      confirmButtonText: 'OK'
    });
  }
};
</script>