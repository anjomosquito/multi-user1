<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-2xl font-semibold mb-6">Sales and Payment Reports</h2>

                <!-- Date Range Selection -->
                <div class="mb-6 flex gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" v-model="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" v-model="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Report Type Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700">Report Type</label>
                    <select v-model="reportType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="sales">Sales Report</option>
                        <option value="payment">Payment Report</option>
                    </select>
                </div>

                <!-- Generate Report Button -->
                <div class="flex gap-4 items-center">
                    <button @click="generateReport" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        Generate Report
                    </button>
                    
                    <div v-if="reportData" class="flex gap-2">
                        <button @click="downloadReport('pdf')" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd" />
                            </svg>
                            Download PDF
                        </button>
                        <button @click="downloadReport('excel')" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd" />
                            </svg>
                            Download Excel
                        </button>
                    </div>
                </div>

                <!-- Report Display Section -->
                <div v-if="reportData" class="mt-6">
                    <!-- Sales Report -->
                    <div v-if="reportType === 'sales' && reportData.sales_data">
                        <div class="bg-gray-100 p-4 rounded-md mb-4">
                            <h3 class="text-lg font-semibold mb-2">Summary</h3>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Total Sales</p>
                                    <p class="text-lg font-semibold">₱{{ reportData.summary.total_sales.toFixed(2) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total Orders</p>
                                    <p class="text-lg font-semibold">{{ reportData.summary.total_orders }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Average Order Value</p>
                                    <p class="text-lg font-semibold">₱{{ reportData.summary.average_order_value.toFixed(2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="sale in reportData.sales_data" :key="sale.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ sale.id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ sale.user }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ sale.total_amount }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span :class="{
                                                'px-2 py-1 rounded-full text-xs font-medium': true,
                                                'bg-green-100 text-green-800': sale.payment_status === 'paid',
                                                'bg-yellow-100 text-yellow-800': sale.payment_status === 'pending',
                                                'bg-red-100 text-red-800': sale.payment_status === 'failed'
                                            }">
                                                {{ sale.payment_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ sale.created_at }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Report -->
                    <div v-if="reportType === 'payment' && reportData.payment_data">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div v-for="payment in reportData.payment_data" :key="payment.payment_status" 
                                class="bg-white p-4 rounded-lg shadow">
                                <h3 class="text-lg font-semibold capitalize mb-2">{{ payment.payment_status }}</h3>
                                <div class="space-y-2">
                                    <p class="text-gray-600">Count: {{ payment.count }}</p>
                                    <p class="text-gray-600">Total: ₱{{ payment.total_amount }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue'

export default {
    setup() {
        const startDate = ref('')
        const endDate = ref('')
        const reportType = ref('sales')
        const reportData = ref(null)

        const generateReport = async () => {
            try {
                const response = await axios.post(
                    `/reports/${reportType.value}`,
                    {
                        start_date: startDate.value,
                        end_date: endDate.value
                    }
                )
                reportData.value = response.data
            } catch (error) {
                console.error('Error generating report:', error)
            }
        }

        const downloadReport = async (format) => {
            try {
                const response = await axios.post(
                    `/reports/${reportType.value}/download`,
                    {
                        start_date: startDate.value,
                        end_date: endDate.value,
                        format: format
                    },
                    {
                        responseType: 'blob'
                    }
                )

                const url = window.URL.createObjectURL(new Blob([response.data]))
                const link = document.createElement('a')
                link.href = url
                link.setAttribute('download', `${reportType.value}-report.${format === 'pdf' ? 'pdf' : 'xlsx'}`)
                document.body.appendChild(link)
                link.click()
                document.body.removeChild(link)
            } catch (error) {
                console.error('Error downloading report:', error)
            }
        }

        return {
            startDate,
            endDate,
            reportType,
            reportData,
            generateReport,
            downloadReport
        }
    }
}
</script>
