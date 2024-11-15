<template>
  <AdminAuthenticatedLayout>
    <Head title="User Purchase History" />

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
      <h2 class="text-2xl font-semibold mb-6 text-center">User Purchase History</h2>

      <div
        v-if="Object.keys(purchases).length === 0"
        class="flex items-center justify-center h-64"
      >
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
        <div v-for="(userGroup, userId) in purchases" :key="userId" class="mb-8">
          <!-- Display username and user ID -->
          <h3 class="text-lg font-bold mb-4">
            User: {{ userGroup.name }} (ID: {{ userId }})
          </h3>

          <table
            class="min-w-full bg-white border border-gray-200 shadow-md rounded-lg overflow-hidden mb-4"
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
                <th class="px-6 py-3 text-left text-gray-600">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="purchase in userGroup.purchases"
                :key="purchase.id"
                class="border-b hover:bg-gray-50 transition-colors"
                :class="{
                  'bg-green-100': purchase.status === 'accepted',
                  'bg-red-100': purchase.status === 'rejected',
                }"
              >
                <td class="px-6 py-4">{{ purchase.name }}</td>
                <td class="px-6 py-4">{{ purchase.quantity }}</td>
                <td class="px-6 py-4">{{ purchase.mprice }}</td>
                <td class="px-6 py-4">{{ purchase.mprice * purchase.quantity }}</td>
                <td class="px-6 py-4">{{ purchase.dosage }}</td>
                <td class="px-6 py-4">{{ purchase.expdate }}</td>
                <td class="px-6 py-4">{{ purchase.purchase_date }}</td>
                <td scope="col" class="flex px-6 py-4 space-x-2">
                  <!-- Buttons for actions, always visible -->
                  <Link
                    @click.prevent="confirmAction(purchase, 'accept')"
                    :class="[
                      purchase.status === 'accepted'
                        ? 'bg-green-200 text-green-800'
                        : 'bg-green-500 hover:bg-green-600 text-white',
                      'px-4 py-2 rounded',
                    ]"
                  >
                    {{ purchase.status === "accepted" ? "Re-accept" : "Accept" }}
                  </Link>
                  <Link
                    @click.prevent="confirmAction(purchase, 'reject')"
                    :class="[
                      purchase.status === 'rejected'
                        ? 'bg-red-200 text-red-800'
                        : 'bg-red-500 hover:bg-red-600 text-white',
                      'px-4 py-2 rounded',
                    ]"
                  >
                    {{ purchase.status === "rejected" ? "Re-reject" : "Reject" }}
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminAuthenticatedLayout>
</template>

<script setup>
import AdminAuthenticatedLayout from "@/Layouts/AdminAuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";
import { Inertia } from "@inertiajs/inertia";
import Swal from "sweetalert2"; // Import SweetAlert2

const props = defineProps({
  purchases: Object, // Grouped by user_id, including name and purchases
});

// Function to confirm the action
const confirmAction = (purchase, actionType) => {
  const actionText = actionType === "accept" ? "Accept" : "Reject";
  const confirmMessage = `Are you sure you want to ${actionText} this purchase?`;

  Swal.fire({
    title: "Are you sure?",
    text: confirmMessage,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: actionType === "accept" ? "#3085d6" : "#d33",
    cancelButtonColor: "#aaa",
    confirmButtonText: actionType === "accept" ? "Yes, Accept" : "Yes, Reject",
  }).then((result) => {
    if (result.isConfirmed) {
      if (actionType === "accept") {
        acceptPurchase(purchase.id); // Proceed with acceptance
      } else {
        rejectPurchase(purchase.id); // Proceed with rejection
      }
    }
  });
};

const acceptPurchase = (id) => {
  Inertia.post(
    route("admin.purchase.accept", id),
    {},
    {
      preserveState: false,
      onSuccess: () => {
        // Update the purchase status after acceptance
        const purchase = findPurchaseById(id);
        if (purchase) {
          purchase.status = purchase.status === "accepted" ? "pending" : "accepted"; // Toggle status
        }
      },
    }
  );
};

const rejectPurchase = (id) => {
  Inertia.post(
    route("admin.purchase.reject", id),
    {},
    {
      preserveState: false,
      onSuccess: () => {
        // Update the purchase status after rejection
        const purchase = findPurchaseById(id);
        if (purchase) {
          purchase.status = purchase.status === "rejected" ? "pending" : "rejected"; // Toggle status
        }
      },
    }
  );
};

// Helper to find purchase by id
const findPurchaseById = (id) => {
  for (const userId in props.purchases) {
    const userGroup = props.purchases[userId];
    const purchase = userGroup.purchases.find((purchase) => purchase.id === id);
    if (purchase) return purchase;
  }
  return null;
};
</script>
