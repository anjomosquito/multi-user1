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

      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold">Purchase Management</h2>
        <div class="flex space-x-4">
          <!-- Barcode Scanner Button -->
          <button 
            @click="startScanning"
            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2m0 0H8m0 0V20m0-6H4m12 0h2M3 3h18M3 21h18" />
            </svg>
            Scan Barcode
          </button>
          <button 
            @click="showReportModal = true"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Generate Reports
          </button>
        </div>
      </div>

      <!-- Purchase List -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction No</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pickup Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="purchase in purchases" :key="purchase.transaction_id" class="hover:bg-gray-50">
              <td class="px-6 py-4">{{ purchase.transaction_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ purchase.user?.name || 'Unknown User' }}
                </div>
              </td>
              <td class="px-6 py-4">
                <div v-for="item in purchase.items" :key="item.id" class="mb-2">
                  {{ item.name }} ({{ item.quantity }}x) - ₱{{ item.total_amount }}
                </div>
              </td>
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
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-y-2">
                <!-- Confirm button for pending purchases -->
                <button v-if="purchase.status === 'pending'" @click="confirmPurchase(purchase.transaction_id)"
                  :disabled="isLoading(purchase.transaction_id)"
                  class="w-full text-blue-600 hover:text-blue-900 bg-blue-100 px-3 py-1 rounded-full">
                  {{ isLoading(purchase.transaction_id) ? 'Processing...' : 'Confirm Purchase' }}
                </button>

                <!-- Mark as Ready button for confirmed purchases -->
                <button v-if="purchase.status === 'confirmed'" @click="markAsReady(purchase.transaction_id)"
                  :disabled="isLoading(purchase.transaction_id)"
                  class="w-full text-green-600 hover:text-green-900 bg-green-100 px-3 py-1 rounded-full">
                  {{ isLoading(purchase.transaction_id) ? 'Processing...' : 'Mark Ready for Pickup' }}
                </button>

                <!-- Complete Verification button when user has verified -->
                <button v-if="purchase.status === 'verified'" @click="verifyPickup(purchase.transaction_id)"
                  :disabled="isLoading(purchase.transaction_id)"
                  class="w-full text-purple-600 hover:text-purple-900 bg-purple-100 px-3 py-1 rounded-full">
                  {{ isLoading(purchase.transaction_id) ? 'Processing...' : 'Complete Verification' }}
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
                    <button @click="verifyPayment(purchase.transaction_id, 'verified')"
                      class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                      Verify Payment
                    </button>
                    <button @click="verifyPayment(purchase.transaction_id, 'rejected')"
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

      <!-- Report Modal -->
      <ReportModal 
        v-if="showReportModal"
        @close="showReportModal = false"
        @success="handleReportSuccess"
      />
      <ReceiptModal 
        v-if="showReceiptModal"
        :show="showReceiptModal"
        :receipt-url="receiptUrl"
        @close="showReceiptModal = false"
      />
      <!-- Barcode Scanner Modal -->
      <Modal :show="showScannerModal" @close="closeScannerModal" :max-width="'2xl'">
        <div class="p-6">
          <h2 class="text-lg font-medium mb-4">Scan Barcode</h2>
          <div class="relative">
            <div id="interactive" class="viewport w-full h-[300px] bg-black mb-4">
              <!-- Loading message -->
              <div v-if="isScannerLoading" class="absolute inset-0 flex items-center justify-center text-white">
                Loading camera...
              </div>
            </div>
          </div>
          <div v-if="scannedCode" class="mb-4 p-4 bg-green-50 rounded-lg">
            <p class="text-sm text-gray-600">Scanned Code:</p>
            <p class="font-medium">{{ scannedCode }}</p>
          </div>
          <div class="flex justify-end space-x-3">
            <button
              @click="restartScanner"
              class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
            >
              Scan Again
            </button>
            <button
              @click="closeScannerModal"
              class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300"
            >
              Close
            </button>
          </div>
        </div>
      </Modal>
    </div>
  </AdminAuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ReportModal from '@/Components/ReportModal.vue';
import ReceiptModal from '@/Components/ReceiptModal.vue';
import Modal from '@/Components/Modal.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import Quagga from 'quagga';

const props = defineProps({
  purchases: {
    type: Array,
    default: () => []
  }
});

const showReportModal = ref(false);
const showReceiptModal = ref(false);
const receiptUrl = ref('');
const selectedReport = ref(null);
const loadingStates = ref(new Set());
const showScannerModal = ref(false);
const scannedCode = ref('');
const isScannerLoading = ref(true);

function viewReceipt(purchase) {
  receiptUrl.value = route('purchase.receipt', { 
    purchase_id: purchase.id 
  });
  showReceiptModal.value = true;
}

function closeModal() {
  selectedReport.value = null;
}

const handleReportSuccess = (message) => {
  Swal.fire({
    icon: 'success',
    title: 'Success',
    text: message,
    timer: 2000,
    showConfirmButton: false
  });
};

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

function confirmPurchase(transactionId) {
  Swal.fire({
    title: 'Confirm Purchase',
    text: 'Are you sure you want to confirm this purchase?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, confirm it',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      setLoading(transactionId, true);
      router.post(route('admin.purchase.confirm', transactionId), {}, {
        preserveScroll: true,
        onSuccess: () => {
          setLoading(transactionId, false);
          Swal.fire('Confirmed!', 'Purchase has been confirmed.', 'success');
        },
        onError: () => {
          setLoading(transactionId, false);
          Swal.fire('Error!', 'Failed to confirm purchase.', 'error');
        }
      });
    }
  });
}

function markAsReady(transactionId) {
  Swal.fire({
    title: 'Mark as Ready',
    text: 'Mark this purchase as ready for pickup?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, mark as ready',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      setLoading(transactionId, true);
      router.post(route('admin.purchase.ready', transactionId), {}, {
        preserveScroll: true,
        onSuccess: () => {
          setLoading(transactionId, false);
          Swal.fire('Ready!', 'Purchase marked as ready for pickup.', 'success');
        },
        onError: () => {
          setLoading(transactionId, false);
          Swal.fire('Error!', 'Failed to mark as ready.', 'error');
        }
      });
    }
  });
}

function verifyPickup(transactionId) {
  Swal.fire({
    title: 'Complete Verification',
    text: 'Confirm that you have verified the user pickup?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, complete verification',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      setLoading(transactionId, true);
      router.post(route('admin.purchase.verify-pickup', transactionId), {}, {
        preserveScroll: true,
        onSuccess: () => {
          setLoading(transactionId, false);
          Swal.fire('Completed!', 'Purchase has been verified and completed.', 'success');
        },
        onError: () => {
          setLoading(transactionId, false);
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

function verifyPayment(transactionId, status) {
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
      router.post(route('admin.purchase.verify-payment', transactionId), {
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

function startScanning() {
  showScannerModal.value = true;
  isScannerLoading.value = true;
  nextTick(() => {
    initializeScanner();
  });
}

function initializeScanner() {
  Quagga.init({
    inputStream: {
      name: "Live",
      type: "LiveStream",
      target: document.querySelector("#interactive"),
      constraints: {
        width: 640,
        height: 480,
        facingMode: "environment"
      },
    },
    locator: {
      patchSize: "medium",
      halfSample: true
    },
    numOfWorkers: 2,
    decoder: {
      readers: ["ean_reader", "ean_8_reader", "code_128_reader", "code_39_reader", "upc_reader"]
    },
    locate: true
  }, function(err) {
    if (err) {
      console.error(err);
      Swal.fire({
        title: 'Error',
        text: 'Failed to start camera. Please make sure you have given camera permissions.',
        icon: 'error'
      });
      return;
    }
    Quagga.start();
    isScannerLoading.value = false;
  });

  Quagga.onDetected(function(result) {
    if (result.codeResult.code) {
      scannedCode.value = result.codeResult.code;
      // Play a success sound
      const audio = new Audio('/sounds/beep.mp3');
      audio.play();
      // Temporarily stop scanning
      Quagga.stop();
    }
  });
}

function restartScanner() {
  scannedCode.value = '';
  isScannerLoading.value = true;
  initializeScanner();
}

function closeScannerModal() {
  Quagga.stop();
  showScannerModal.value = false;
  scannedCode.value = '';
  isScannerLoading.value = true;
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

#interactive.viewport {
  position: relative;
}

#interactive.viewport > canvas, #interactive.viewport > video {
  max-width: 100%;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.drawingBuffer {
  position: absolute;
  top: 0;
  left: 0;
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
  @apply bg-blue-100 text-blue-800;
}

.status-completed {
  @apply bg-green-100 text-green-800;
}

.status-cancelled {
  @apply bg-red-100 text-red-800;
}
</style>
