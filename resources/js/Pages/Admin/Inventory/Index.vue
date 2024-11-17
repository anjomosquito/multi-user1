<script setup>
import AuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    medicines: Array
});

function confirmDelete(medicineId) {
  if (confirm('Are you sure you want to delete this medicine?')) {
    // Call the delete endpoint for the medicine
    $inertia.delete(`/admin/medicines/${medicineId}`);
  }
}
</script>

<template>
    <Head title="Inventory" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Inventory Index</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Name</th>
                                    <th scope="col" class="px-6 py-3">Low Price</th>
                                    <th scope="col" class="px-6 py-3">Median Price</th>
                                    <th scope="col" class="px-6 py-3">Highest Price</th>
                                    <th scope="col" class="px-6 py-3">Quantity</th>
                                    <th scope="col" class="px-6 py-3">Dosage</th>
                                    <th scope="col" class="px-6 py-3">Exp Date</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="medicine in medicines" :key="medicine.id"
                                    :class="{'bg-red-100 text-red-700': medicine.quantity === 0}">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ medicine.name }}
                                    </th>
                                    <td class="px-6 py-4">₱{{ medicine.lprice }}</td>
                                    <td class="px-6 py-4">₱{{ medicine.mprice }}</td>
                                    <td class="px-6 py-4">₱{{ medicine.hprice }}</td>
                                    <td class="px-6 py-4">{{ medicine.quantity }}</td>
                                    <td class="px-6 py-4">{{ medicine.dosage }}</td>
                                    <td class="px-6 py-4">{{ medicine.expdate }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="medicine.status === 'disabled' ? 'text-red-600' : 'text-green-600'">
                                            {{ medicine.status }}
                                        </span>
                                    </td>
                                    <td class="flex px-6 py-4 space-x-2">
                                        <Link :href="'/admin/medicines/' + medicine.id + '/edit'"
                                              class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded">
                                            Edit
                                        </Link>
                                        <button @click="confirmDelete(medicine.id)"
                                                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
