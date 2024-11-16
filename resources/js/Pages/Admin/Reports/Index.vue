<template>
    <AdminAuthenticatedLayout>
        <Head title="Purchase Reports" />
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
            <div class="mb-6">
                <Link href="/admin/purchase/index"
                    class="text-white bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-blue-300">
                    Back
                </Link>
            </div>
            <h2 class="text-2xl font-semibold mb-6">Purchase Reports</h2>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Sales</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Purchases</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Quantity</th> <!-- New Column -->
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="report in reports" :key="report.date" class="hover:bg-gray-50">
                            <td class="px-6 py-4">{{ new Date(report.date).toLocaleDateString() }}</td>
                            <td class="px-6 py-4">₱{{ report.total_sales }}</td>
                            <td class="px-6 py-4">{{ report.total_purchases }}</td>
                            <td class="px-6 py-4">{{ report.total_quantity }}</td> <!-- Displaying Total Quantity -->
                        </tr>
                    </tbody>
                </table>

                <div v-if="!reports.length" class="text-center py-12">
                    <div class="text-gray-500">No reports available</div>
                </div>
            </div>
        </div>
    </AdminAuthenticatedLayout>
</template>

<script setup>
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    reports: {
        type: Array,
        required: true
    }
});
</script>
