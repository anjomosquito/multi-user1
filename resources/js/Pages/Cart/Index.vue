<template>
  <AuthenticatedLayout>
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
          <h2 class="text-xl font-semibold mb-6">Your Cart</h2>

          <div v-if="cartItems.length === 0">
              <p>Your cart is empty.</p>
          </div>

          <div v-else>
              <table class="min-w-full table-auto text-left">
                  <thead>
                      <tr>
                          <th class="px-6 py-3">Medicine Name</th>
                          <th class="px-6 py-3">Quantity</th>
                          <th class="px-6 py-3">Low Price</th>
                          <th class="px-6 py-3">Median Price</th>
                          <th class="px-6 py-3">High Price</th>
                          <th class="px-6 py-3">Total Price</th>
                          <th class="px-6 py-3">Dosage</th>
                          <th class="px-6 py-3">Exp Date</th>
                          <th class="px-6 py-3">Action</th>
                      </tr>
                  </thead>
                  <tbody>
                      <tr v-for="item in cartItems" :key="item.id">
                          <td class="px-6 py-4">{{ item.name }}</td>
                          <td class="px-6 py-4">{{ item.quantity }}</td>
                          <td class="px-6 py-4">{{ item.lprice }}</td>
                          <td class="px-6 py-4">{{ item.mprice }}</td>
                          <td class="px-6 py-4">{{ item.hprice }}</td>
                          <td class="px-6 py-4">{{ item.mprice * item.quantity }}</td>
                          <td class="px-6 py-4">{{ item.dosage }}</td>
                          <td class="px-6 py-4">{{ item.expdate }}</td>
                          <td class="px-6 py-4">
                              <!-- Updated Link component -->
                              <Link
                                  :href="route('cart.destroy', item.id)"
                                  method="delete"
                                  as="button"
                                  class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded"
                              >
                                  Remove
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
  cartItems: Array
});
</script>
