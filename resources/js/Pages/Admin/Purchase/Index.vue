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
              >
                <td class="px-6 py-4">{{ purchase.name }}</td>
                <td class="px-6 py-4">{{ purchase.quantity }}</td>
                <td class="px-6 py-4">{{ purchase.mprice }}</td>
                <td class="px-6 py-4">{{ purchase.mprice * purchase.quantity }}</td>
                <td class="px-6 py-4">{{ purchase.dosage }}</td>
                <td class="px-6 py-4">{{ purchase.expdate }}</td>
                <td class="px-6 py-4">{{ purchase.purchase_date }}</td>
                <td scope="col" class="flex px-6 py-4">
                  <Link
                    :href="'' + ''"
                    class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded mr-2"
                  >
                    Accept
                  </Link>
                  <Link
                    :href="'' + ''"
                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded"
                  >
                    Reject
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

const props = defineProps({
  purchases: Object, // Grouped by user_id, including name and purchases
});
</script>
