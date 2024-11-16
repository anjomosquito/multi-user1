<template>
  <AdminAuthenticatedLayout>

    <Head title="Purchase Management" />

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
      <!-- Flash Messages -->
      <div v-if="$page.props.flash.success"
        class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ $page.props.flash.success }}
      </div>

      <div v-if="$page.props.flash.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ $page.props.flash.error }}
      </div>

      <h2 class="text-2xl font-semibold mb-6">Purchase Management</h2>
      <button 
          @click="generateReport"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
          Generate Reports
        </button>

      <!-- Purchase List -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction No</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medicine</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="purchase in purchases" :key="purchase.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">{{ purchase.transaction_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ purchase.user?.name || 'Unknown User' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ purchase.name }}</div>
                <div class="text-sm text-gray-500">{{ purchase.dosage }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ purchase.quantity }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ₱{{ purchase.total_amount }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="{
                  'px-2 py-1 rounded-full text-xs font-medium': true,
                  'bg-yellow-100 text-yellow-800': purchase.status === 'pending',
                  'bg-blue-100 text-blue-800': purchase.status === 'confirmed',
                  'bg-purple-100 text-purple-800': purchase.status === 'verified',
                  'bg-green-100 text-green-800': purchase.status === 'completed',
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
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ new Date(purchase.created_at).toLocaleDateString() }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-y-2">
                <!-- Confirm button for pending purchases -->
                <button v-if="purchase.status === 'pending'" @click="confirmPurchase(purchase.id)"
                  :disabled="isLoading(purchase.id)"
                  class="w-full text-blue-600 hover:text-blue-900 bg-blue-100 px-3 py-1 rounded-full">
                  {{ isLoading(purchase.id) ? 'Processing...' : 'Confirm Purchase' }}
                </button>

                <!-- Mark as Ready button for confirmed purchases -->
                <button v-if="purchase.status === 'confirmed'" @click="markAsReady(purchase.id)"
                  :disabled="isLoading(purchase.id)"
                  class="w-full text-green-600 hover:text-green-900 bg-green-100 px-3 py-1 rounded-full">
                  {{ isLoading(purchase.id) ? 'Processing...' : 'Mark Ready for Pickup' }}
                </button>

                <!-- Complete Verification button when user has verified -->
                <button v-if="purchase.status === 'verified'" @click="verifyPickup(purchase.id)"
                  :disabled="isLoading(purchase.id)"
                  class="w-full text-purple-600 hover:text-purple-900 bg-purple-100 px-3 py-1 rounded-full">
                  {{ isLoading(purchase.id) ? 'Processing...' : 'Complete Verification' }}
                </button>

                <!-- Status messages -->
                <div v-if="purchase.verification_status" class="text-sm">
                  <span v-if="purchase.verification_status === 'verified_by_user'" class="text-purple-600 font-medium">
                    User verified pickup - Click to complete
                  </span>
                  <span v-if="purchase.verification_status === 'waiting_user'" class="text-yellow-600">
                    Waiting for user verification
                  </span>
                  <span v-if="purchase.verification_status === 'completed'" class="text-green-600">
                    Pickup Complete ✓
                  </span>
                </div>

                <!-- Ready for pickup status -->
                <div v-if="purchase.ready_for_pickup && !purchase.user_pickup_verified" class="text-sm text-blue-600">
                  Waiting for user pickup
                </div>

                <!-- Completed status -->
                <div v-if="purchase.status === 'completed'" class="text-sm text-green-600 font-medium">
                  Order Completed ✓
                </div>

                <!-- Payment Proof Section -->
                <div v-if="purchase.payment_proof" class="mt-2 space-y-2">
                  <div class="flex items-center space-x-2">
                    <span :class="{
                      'text-yellow-600': purchase.payment_status === 'pending',
                      'text-green-600': purchase.payment_status === 'verified',
                      'text-red-600': purchase.payment_status === 'rejected'
                    }">
                      <template v-if="purchase.payment_status === 'pending'">
                        ⏳ Payment verification needed
                      </template>
                      <template v-if="purchase.payment_status === 'verified'">
                        ✓ Payment verified
                      </template>
                      <template v-if="purchase.payment_status === 'rejected'">
                        ✗ Payment rejected
                      </template>
                    </span>

                    <!-- View Payment Proof Button -->
                    <a v-if="purchase.payment_proof" :href="purchase.payment_proof_url" target="_blank"
                      class="text-blue-500 hover:text-blue-700 underline text-sm">
                      View Proof
                    </a>
                  </div>

                  <!-- Verification Buttons -->
                  <div v-if="purchase.payment_status === 'pending'" class="flex space-x-2">
                    <button @click="verifyPayment(purchase.id, 'verified')"
                      class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                      Verify Payment
                    </button>
                    <button @click="verifyPayment(purchase.id, 'rejected')"
                      class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                      Reject Payment
                    </button>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty State -->
        <div v-if="!purchases.length" class="text-center py-12">
          <div class="text-gray-500">No purchases found</div>
        </div>
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
  </AdminAuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
  purchases: {
    type: Array,
    required: true
  }
});
const selectedReport = ref(null);

function viewReport(purchase) {
  selectedReport.value = purchase;
}

function closeModal() {
  selectedReport.value = null;
}
const loadingStates = ref(new Set());

function generateReport() {
  Swal.fire({
    title: 'Generate Report',
    text: 'Do you want to generate a report of all purchases?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, generate it',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      router.get(route('admin.purchase.report'), {}, {
        onSuccess: () => {
          Swal.fire('Report Generated!', 'Your report is ready.', 'success');
        },
        onError: () => {
          Swal.fire('Error!', 'Failed to generate the report.', 'error');
        }
      });
    }
  });
}


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
  Swal.fire({
    title: 'Confirm Purchase',
    text: 'Are you sure you want to confirm this purchase?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, confirm it',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      setLoading(purchaseId, true);
      router.post(route('admin.purchase.confirm', purchaseId), {}, {
        preserveScroll: true,
        onSuccess: () => {
          setLoading(purchaseId, false);
          Swal.fire('Confirmed!', 'Purchase has been confirmed.', 'success');
        },
        onError: () => {
          setLoading(purchaseId, false);
          Swal.fire('Error!', 'Failed to confirm purchase.', 'error');
        }
      });
    }
  });
}

function markAsReady(purchaseId) {
  Swal.fire({
    title: 'Mark as Ready',
    text: 'Mark this purchase as ready for pickup?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, mark as ready',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      setLoading(purchaseId, true);
      router.post(route('admin.purchase.ready', purchaseId), {}, {
        preserveScroll: true,
        onSuccess: () => {
          setLoading(purchaseId, false);
          Swal.fire('Ready!', 'Purchase marked as ready for pickup.', 'success');
        },
        onError: () => {
          setLoading(purchaseId, false);
          Swal.fire('Error!', 'Failed to mark as ready.', 'error');
        }
      });
    }
  });
}

function verifyPickup(purchaseId) {
  Swal.fire({
    title: 'Complete Verification',
    text: 'Confirm that you have verified the user pickup?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, complete verification',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      setLoading(purchaseId, true);
      router.post(route('admin.purchase.verify-pickup', purchaseId), {}, {
        preserveScroll: true,
        onSuccess: () => {
          setLoading(purchaseId, false);
          Swal.fire('Completed!', 'Purchase has been verified and completed.', 'success');
        },
        onError: () => {
          setLoading(purchaseId, false);
          Swal.fire('Error!', 'Failed to complete verification.', 'error');
        }
      });
    }
  });
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

function verifyPayment(purchaseId, status) {
  const action = status === 'verified' ? 'verify' : 'reject';

  Swal.fire({
    title: `${action.charAt(0).toUpperCase() + action.slice(1)} Payment?`,
    text: `Are you sure you want to ${action} this payment?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: `Yes, ${action}`,
    cancelButtonText: 'Cancel',
    confirmButtonColor: status === 'verified' ? '#10B981' : '#EF4444',
  }).then((result) => {
    if (result.isConfirmed) {
      router.post(route('admin.purchase.verify-payment', purchaseId), {
        status: status
      }, {
        preserveScroll: true,
        onSuccess: () => {
          Swal.fire({
            title: 'Success!',
            text: `Payment ${status} successfully.`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
        },
        onError: () => {
          Swal.fire({
            title: 'Error!',
            text: 'Failed to process payment verification.',
            icon: 'error'
          });
        }
      });
    }
  });
}
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}

.status-badge {
  @apply px-2 py-1 rounded-full text-xs font-medium;
}

.status-pending {
  @apply bg-yellow-100 text-yellow-800;
}

.status-confirmed {
  @apply bg-blue-100 text-blue-800;
}

.status-ready {
  @apply bg-green-100 text-green-800;
}

.status-verified {
  @apply bg-purple-100 text-purple-800;
}

.status-completed {
  @apply bg-green-100 text-green-800;
}

.status-cancelled {
  @apply bg-red-100 text-red-800;
}
</style>
