<script setup>
import AuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';

const props = defineProps({
    medicines: {
        type: Array,
        default: () => []
    }
});

function confirmDelete(medicineId) {
    if (!medicineId) return;
    
    if (confirm('Are you sure you want to delete this medicine?')) {
        deleteMedicine(medicineId);
    }
}

const searchQuery = ref('');

// Debounced search function
const performSearch = debounce((query) => {
    router.get(route('admin.medicines.index'), 
        { search: query },
        { preserveState: true, preserveScroll: true }
    );
}, 300);

// Watch for search input changes
watch(searchQuery, (newValue) => {
    performSearch(newValue);
});
</script>

<template>
    <Head title="Medicines" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Medicine Index</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Add search bar and Add Medicines button in a flex container -->
                <div class="flex justify-between items-center mb-6">
                    <!-- Search Bar -->
                    <div class="w-1/2">
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search medicines by name or dosage..."
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600"
                            />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <!-- Add Medicines Button -->
                    <Link 
                        :href="route('admin.medicines.create')" 
                        class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded"
                    >
                        Add Medicines
                    </Link>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Name</th>
                                    <th scope="col" class="px-6 py-3">Low Price</th>
                                    <th scope="col" class="px-6 py-3">Median Price</th>
                                    <th scope="col" class="px-6 py-3">Highest Price</th>
                                    <th scope="col" class="px-6 py-3">Quantity</th>
                                    <th scope="col" class="px-6 py-3">Dosage</th>
                                    <th scope="col" class="px-6 py-3">Exp Date</th>
                                    <th scope="col" class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!medicines || medicines.length === 0">
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                        No medicines found
                                    </td>
                                </tr>
                                <tr 
                                    v-for="(medicine, index) in medicines" 
                                    :key="medicine?.id || index"
                                    class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700"
                                >
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ medicine?.name || 'N/A' }}
                                    </th>
                                    <td class="px-6 py-4">₱{{ medicine?.lprice || '0' }}</td>
                                    <td class="px-6 py-4">₱{{ medicine?.mprice || '0' }}</td>
                                    <td class="px-6 py-4">₱{{ medicine?.hprice || '0' }}</td>
                                    <td class="px-6 py-4">{{ medicine?.quantity || '0' }}</td>
                                    <td class="px-6 py-4">{{ medicine?.dosage || 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ medicine?.expdate || 'N/A' }}</td>
                                    <td class="flex px-6 py-4 space-x-2">
                                        <Link 
                                            v-if="medicine"
                                            :href="route('admin.medicines.edit', medicine.id)" 
                                            class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded"
                                        >
                                            Edit
                                        </Link>
                                        <Link 
                                            v-if="medicine"
                                            :href="route('admin.medicines.destroy', medicine.id)"
                                            method="delete" 
                                            @click.prevent="confirmDelete(medicine.id)" 
                                            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded"
                                        >
                                            Delete
                                        </Link>
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
