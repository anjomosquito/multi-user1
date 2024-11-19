<template>
    <Head title="Inventory Report" />

    <AdminAuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Inventory Report</h2>
                <button @click="downloadReport" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Download Report
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <!-- Filters -->
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                            <input type="date" v-model="filters.start_date" @change="applyFilters"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                            <input type="date" v-model="filters.end_date" @change="applyFilters"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Action Type</label>
                            <select v-model="filters.action_type" @change="applyFilters"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Actions</option>
                                <option value="add">Add</option>
                                <option value="update">Update</option>
                                <option value="delete">Delete</option>
                            </select>
                        </div>
                    </div>

                    <!-- Log Table -->
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Date</th>
                                    <th scope="col" class="px-6 py-3">Medicine</th>
                                    <th scope="col" class="px-6 py-3">Action</th>
                                    <th scope="col" class="px-6 py-3">Quantity Change</th>
                                    <th scope="col" class="px-6 py-3">Price Change</th>
                                    <th scope="col" class="px-6 py-3">Status Change</th>
                                    <th scope="col" class="px-6 py-3">Admin</th>
                                    <th scope="col" class="px-6 py-3">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in logs" :key="log.id" 
                                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-6 py-4">{{ formatDate(log.created_at) }}</td>
                                    <td class="px-6 py-4">{{ log.medicine ? log.medicine.name : 'Unknown' }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="{
                                            'px-2 py-1 rounded text-white': true,
                                            'bg-green-500': log.action_type === 'add',
                                            'bg-blue-500': log.action_type === 'update',
                                            'bg-red-500': log.action_type === 'delete'
                                        }">
                                            {{ log.action_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="{
                                            'text-green-500': log.quantity_change > 0,
                                            'text-red-500': log.quantity_change < 0
                                        }">
                                            {{ log.quantity_change > 0 ? '+' : '' }}{{ log.quantity_change }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <template v-if="log.old_price && log.new_price">
                                            ₱{{ log.old_price }} → ₱{{ log.new_price }}
                                        </template>
                                    </td>
                                    <td class="px-6 py-4">
                                        <template v-if="log.old_status || log.new_status">
                                            {{ log.old_status || '-' }} → {{ log.new_status || '-' }}
                                        </template>
                                    </td>
                                    <td class="px-6 py-4">{{ log.admin.name }}</td>
                                    <td class="px-6 py-4">{{ log.notes }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminAuthenticatedLayout>
</template>

<script setup>
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    logs: Array,
    filters: Object
});

const filters = ref({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    action_type: props.filters?.action_type || ''
});

const formatDate = (date) => {
    return new Date(date).toLocaleString();
};

const applyFilters = () => {
    router.get(route('admin.inventory.report'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const downloadReport = () => {
    router.post(route('admin.inventory.report.download'), filters.value, {
        preserveState: true,
    });
};
</script>
