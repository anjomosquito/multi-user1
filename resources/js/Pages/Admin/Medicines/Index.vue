<script setup>
import AuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    medicines: Array
});

 /*props: {
    medicine: {
      type: Object,
      required: true
    }
}*/

function confirmDelete(medicineId) {
  if (confirm('Are you sure you want to delete this medicine?')) {
    // Call the deleteMedicine method
    deleteMedicine(medicineId);
  }
}
</script>

<template>
    <Head title="Medicines" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Medicine Index</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-end m-2 p-2">
                    <Link :href="route('admin.medicines.create')" class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded">Add Medicines</Link>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">
                                        Name
                                    </th>
                                    <!--<th scope="col" class="px-6 py-3">
                                        Price
                                    </th>-->
                                    <th scope="col" class="px-6 py-3">
                                        Low Price
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Median Price
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Highest Price
                                    </th>

                                    <th scope="col" class="px-6 py-3">
                                        Quantity
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Dosage
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Exp Date
                                    </th>
                                    <!--<th scope="col" class="px-6 py-3">
                                        Actions
                                    </th>-->
                                </tr>
                            </thead>
                            <tbody>
                                
                                <tr v-for="medicine in medicines" :key="medicine.id"  class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ medicine.name }}
                                    </th>
                                    <!--<td class="px-6 py-4">
                                        {{ medicine.price }}
                                    </td>-->
                                    <td class="px-6 py-4">
                                        {{ medicine.lprice }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ medicine.mprice }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ medicine.hprice }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ medicine.quantity}}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ medicine.dosage }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ medicine.expdate }}
                                    </td>
                                    <!--<td class="flex px-6 py-4">
                                        <Link :href="'/admin/medicines/' + medicine.id + '/edit'" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded">
                                        Edit
                                        </Link>
                                        <Link :href="`/admin/medicines/${medicine.id}`" method="delete" @click="confirmDelete(medicine.id)" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded">
                                            Delete
                                        </Link>
                                    </td>-->
                                    
                                </tr>
                                
                                
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
