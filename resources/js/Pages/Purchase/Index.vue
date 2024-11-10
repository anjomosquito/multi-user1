<template>
  <AuthenticatedLayout>
    <Head title="Purchase History" />

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
      <h2 class="text-2xl font-semibold mb-6 text-center">Purchase History</h2>

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
        <table class="min-w-full bg-white border border-gray-200 shadow-md rounded-lg overflow-hidden">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-6 py-3 text-left text-gray-600">Medicine Name</th>
              <th class="px-6 py-3 text-left text-gray-600">Quantity</th>
              <th class="px-6 py-3 text-left text-gray-600">Medium Price</th>
              <th class="px-6 py-3 text-left text-gray-600">Total Price</th>
              <th class="px-6 py-3 text-left text-gray-600">Dosage</th>
              <th class="px-6 py-3 text-left text-gray-600">Exp Date</th>
              <th class="px-6 py-3 text-left text-gray-600">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="purchase in purchases" :key="purchase.id" class="border-b hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4">{{ purchase.name }}</td>
              <td class="px-6 py-4">{{ purchase.quantity }}</td>
              <td class="px-6 py-4">{{ purchase.mprice }}</td>
              <td class="px-6 py-4">{{ purchase.mprice * purchase.quantity }}</td>
              <td class="px-6 py-4">{{ purchase.dosage }}</td>
              <td class="px-6 py-4">{{ purchase.expdate }}</td>
              <td class="px-6 py-4">
                <Link :href="route('purchase.cancel', purchase.id)" method="delete" as="button" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded">
                  Cancel
                </Link>
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
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  purchases: Array,
});
</script>
