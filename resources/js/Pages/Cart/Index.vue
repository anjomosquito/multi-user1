<template>
  <AuthenticatedLayout>

    <Head title="Cart" />

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
      <h2 class="text-2xl font-semibold mb-6 text-center">Your Cart</h2>

      <div v-if="cartItems.length === 0" class="flex items-center justify-center h-64">
        <div class="flex flex-col items-center">
          <div class="text-center text-gray-500 mb-4">
            Your cart is empty.
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
        <table class="min-w-full bg-white border border-gray-200 shadow-md rounded-lg overflow-hidden">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-6 py-3 text-left text-gray-600">Medicine Name</th>
              <th class="px-6 py-3 text-left text-gray-600">Quantity</th>
              <th class="px-6 py-3 text-left text-gray-600">Price</th>
              <th class="px-6 py-3 text-left text-gray-600">Total Price</th>
              <th class="px-6 py-3 text-left text-gray-600">Dosage</th>
              <th class="px-6 py-3 text-left text-gray-600">Exp Date</th>
              <th class="px-6 py-3 text-left text-gray-600">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in cartItems" :key="item.id" class="border-b hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4">{{ item.name }}</td>
              <td class="px-6 py-4">{{ item.quantity }}</td>
              <td class="px-6 py-4">{{ item.mprice }}</td>
              <td class="px-6 py-4">{{ item.mprice * item.quantity }}</td>
              <td class="px-6 py-4">{{ item.dosage }}</td>
              <td class="px-6 py-4">{{ item.expdate }}</td>
              <td class="px-6 py-4">
                <Link :href="route('cart.destroy', item.id)" method="delete" as="button"
                  class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded mr-1">

                Remove
                </Link>
                <Link :href="route('purchase.store')" method="post" :data="{ cartItems: cartItems }" as="button"
                  class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded">
                Order
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
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  cartItems: Array,
});
</script>