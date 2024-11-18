<template>
    <AdminAuthenticatedLayout>
        <Head title="Purchase Reports" />
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
            <div class="mb-6 flex justify-between items-center">
                <Link href="/admin/purchase/index"
                    class="text-white bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-blue-300">
                    Back
                </Link>
                <div class="flex space-x-4">
                    <button @click="downloadReport('sales', 'pdf')"
                        class="text-white bg-red-500 hover:bg-red-600 px-4 py-2 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-red-300">
                        Download Sales PDF
                    </button>
                    <button @click="downloadReport('sales', 'excel')"
                        class="text-white bg-green-500 hover:bg-green-600 px-4 py-2 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-green-300">
                        Download Sales Excel
                    </button>
                    <button @click="downloadReport('payment', 'pdf')"
                        class="text-white bg-red-500 hover:bg-red-600 px-4 py-2 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-red-300">
                        Download Payment PDF
                    </button>
                    <button @click="downloadReport('payment', 'excel')"
                        class="text-white bg-green-500 hover:bg-green-600 px-4 py-2 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-green-300">
                        Download Payment Excel
                    </button>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Report Filters</h3>
                <div class="flex space-x-4 mb-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" v-model="filters.start_date"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" v-model="filters.end_date"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                    </div>
                    <div class="flex items-end">
                        <button @click="generateReport"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-blue-300">
                            Generate Report
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="reportData" class="mt-6">
                <ComprehensiveReport 
                    :executive-summary="reportData.executive_summary"
                    :product-analysis="reportData.product_analysis"
                    :customer-analysis="reportData.customer_analysis"
                    :daily-trends="reportData.daily_trends"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Sales Summary</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-500">Total Sales</div>
                            <div class="text-xl font-semibold">₱{{ summary.total_sales }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-500">Total Orders</div>
                            <div class="text-xl font-semibold">{{ summary.total_orders }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-500">Average Order Value</div>
                            <div class="text-xl font-semibold">₱{{ summary.average_order_value }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-500">Total Products</div>
                            <div class="text-xl font-semibold">{{ additionalStats.total_products }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Payment Status</h3>
                    <div class="space-y-4">
                        <div v-for="status in paymentStatus" :key="status.payment_status"
                            :class="{
                                'bg-yellow-50': status.payment_status === 'Pending',
                                'bg-green-50': status.payment_status === 'Completed',
                                'bg-red-50': status.payment_status === 'Failed'
                            }"
                            class="p-4 rounded-lg">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="text-sm text-gray-500">{{ status.payment_status }}</div>
                                    <div class="text-xl font-semibold">{{ status.count }} orders</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500">Total Amount</div>
                                    <div class="text-xl font-semibold">₱{{ status.total_amount }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <h3 class="text-lg font-semibold p-6 border-b">Sales Details</h3>
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
import ComprehensiveReport from './Components/ComprehensiveReport.vue';
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    reports: {
        type: Array,
        required: true
    }
});

// Initialize with a wider date range to capture all transactions
const filters = reactive({
    start_date: '2024-01-01',  // Start from beginning of 2024
    end_date: '2024-12-31'     // End at end of 2024
});

const summary = ref({
    total_sales: '0.00',
    total_orders: 0,
    average_order_value: '0.00'
});

const additionalStats = ref({
    total_customers: 0,
    total_products: 0,
    top_selling_products: []
});

const paymentStatus = ref([]);

const reportData = ref(null);

async function generateReport() {
    try {
        // Show loading state
        const loadingToast = Swal.fire({
            title: 'Generating Report',
            text: 'Please wait...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Validate dates
        if (!filters.start_date || !filters.end_date) {
            loadingToast.close();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select both start and end dates'
            });
            return;
        }

        // Ensure end date is not before start date
        if (new Date(filters.end_date) < new Date(filters.start_date)) {
            loadingToast.close();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'End date cannot be before start date'
            });
            return;
        }

        const [salesResponse, paymentResponse] = await Promise.all([
            axios.post('/admin/reports/sales', {
                start_date: filters.start_date,
                end_date: filters.end_date
            }),
            axios.post('/admin/reports/payments', {
                start_date: filters.start_date,
                end_date: filters.end_date
            })
        ]);

        // Close loading toast
        loadingToast.close();

        // Update the report data
        reportData.value = salesResponse.data.report_data;
        
        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Report Generated',
            text: 'The report has been generated successfully'
        });
    } catch (error) {
        console.error('Error generating report:', error);
        console.error('Error details:', error.response?.data);
        
        // Close loading toast if it's still open
        Swal.close();
        
        // Show error message
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data?.error || 'An error occurred while generating the report'
        });
    }
}

async function downloadReport(type, format) {
    try {
        const response = await axios.post(`/admin/reports/${type}/download`, {
            start_date: filters.start_date + ' 00:00:00',
            end_date: filters.end_date + ' 23:59:59',
            format
        }, {
            responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `${type}-report.${format}`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Error downloading report:', error);
    }
}

onMounted(() => {
    generateReport();
});
</script>
