<template>
  <AuthenticatedLayout>
    <Head title="Purchase History" />

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
      <h2 class="text-2xl font-semibold mb-6 text-center">Purchase History</h2>

      <div v-if="purchases.length === 0" class="flex items-center justify-center h-64">
        <div class="flex flex-col items-center">
          <div class="text-center text-gray-500 mb-4">No purchases found.</div>
          <div class="animate-float">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-16 w-16 text-gray-500"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 3h18M3 3l3 18h12l3-18M3 3l3 18h12l3-18"
              />
            </svg>
          </div>
        </div>
      </div>

      <div v-else>
        <table
          class="min-w-full bg-white border border-gray-200 shadow-md rounded-lg overflow-hidden"
        >
          <thead class="bg-gray-100">
            <tr>
              <th class="px-6 py-3 text-left text-gray-600">Medicine Name</th>
              <th class="px-6 py-3 text-left text-gray-600">Quantity</th>
              <th class="px-6 py-3 text-left text-gray-600">Medium Price</th>
              <th class="px-6 py-3 text-left text-gray-600">Total Price</th>
              <th class="px-6 py-3 text-left text-gray-600">Dosage</th>
              <th class="px-6 py-3 text-left text-gray-600">Exp Date</th>
              <th class="px-6 py-3 text-left text-gray-600">Purchase Date</th>
              <th class="px-6 py-3 text-left text-gray-600">Action</th>
              <th class="px-6 py-3 text-left text-gray-600">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="purchase in purchases"
              :key="purchase.id"
              class="border-b hover:bg-gray-50 transition-colors"
              :class="{
                'bg-green-100': purchase.status === 'accepted',
                'bg-red-100': purchase.status === 'rejected',
                'opacity-50': purchase.status === 'rejected',
              }"
            >
              <td class="px-6 py-4">{{ purchase.name }}</td>
              <td class="px-6 py-4">{{ purchase.quantity }}</td>
              <td class="px-6 py-4">{{ purchase.mprice }}</td>
              <td class="px-6 py-4">{{ purchase.mprice * purchase.quantity }}</td>
              <td class="px-6 py-4">{{ purchase.dosage }}</td>
              <td class="px-6 py-4">{{ purchase.expdate }}</td>
              <td class="px-6 py-4">{{ purchase.purchase_date }}</td>
              <td class="px-6 py-4">
                <button
                  v-if="purchase.status === 'pending'"
                  @click="confirmCancel(purchase)"
                  class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded"
                >
                  Cancel
                </button>
              </td>

              <td class="px-6 py-4">
                <span v-if="purchase.status === 'pending'" class="text-yellow-500"
                  >Pending</span
                >
                <span v-if="purchase.status === 'accepted'" class="text-green-500"
                  >Accepted</span
                >
                <span v-if="purchase.status === 'rejected'" class="text-red-500"
                  >Rejected</span
                >
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import { DateTime } from "luxon";

const props = defineProps({
  purchases: Array,
});

function confirmCancel(purchase) {
  Swal.fire({
    title: "Cancel Purchase",
    text: `Are you sure you want to cancel this purchase of ${purchase.name}?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, cancel it!",
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    cancelButtonText: "No, keep it",
  }).then((result) => {
    if (result.isConfirmed) {
      // Proceed with cancelation via route
      router.delete(route("purchase.cancel", purchase.id), {
        onSuccess: () => {
          Swal.fire({
            title: "Cancelled!",
            text: "Your purchase has been successfully canceled.",
            icon: "success",
            timer: 2000,
            showConfirmButton: false,
          });
        },
      });
    }
  });
}

function formatDate(dateString) {
  return DateTime.fromISO(dateString, { zone: "Asia/Singapore" }).toFormat(
    "yyyy-MM-dd HH:mm:ss"
  );
}
</script>
