<script setup>
import AuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const { props } = usePage();
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>
        </template>

        <div class="py-2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Summary Cards -->
                <div class="grid grid-cols-12 gap-4">
                    <!-- Total Medicines -->
                    <div class="col-span-12 md:col-span-3">
                        <div class="mt-4 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900 dark:text-gray-100">
                                <h3 class="text-lg font-semibold">Total Medicines:</h3>
                                <p class="text-2xl">{{ props.medicineCount }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Purchases -->
                    <div class="col-span-12 md:col-span-3">
                        <div class="mt-4 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900 dark:text-gray-100">
                                <h3 class="text-lg font-semibold">Total Purchases:</h3>
                                <p class="text-2xl">{{ props.purchaseCount }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Alert -->
                    <div class="col-span-12 md:col-span-3">
                        <div class="mt-4 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900 dark:text-gray-100">
                                <h3 class="text-lg font-semibold">Low Stock Items:</h3>
                                <p class="text-2xl text-red-500">{{ props.lowStockCount }}</p>
                                <div class="mt-2 space-y-2">
                                    <div v-for="medicine in props.lowStockMedicines" 
                                         :key="medicine.id" 
                                         class="text-sm p-2 bg-red-50 rounded-md">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="font-medium">{{ medicine.name }}</p>
                                                <p class="text-xs text-gray-500">{{ medicine.dosage }}</p>
                                            </div>
                                            <span class="text-red-600 font-medium">
                                                {{ medicine.quantity }} left
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Revenue -->
                    <div class="col-span-12 md:col-span-3">
                        <div class="mt-4 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900 dark:text-gray-100">
                                <h3 class="text-lg font-semibold">Total Revenue:</h3>
                                <p class="text-2xl text-green-500">₱{{ props.totalRevenue }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Section -->
                <div class="grid grid-cols-12 gap-4 mt-4">
                    <!-- Recent Purchases -->
                    <div class="col-span-12 md:col-span-6">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Recent Purchases</h3>
                                <div class="space-y-3">
                                    <div v-for="purchase in props.recentPurchases" :key="purchase.id" 
                                         class="flex justify-between items-center border-b pb-2">
                                        <div>
                                            <p class="font-medium">{{ purchase.user?.name || purchase.name || 'Unknown User' }}</p>
                                            <p class="text-sm text-gray-500">{{ purchase.created_at }}</p>
                                        </div>
                                        <div class="flex items-center">
                                            <span :class="{
                                                'px-2 py-1 rounded text-xs font-medium mr-3': true,
                                                'bg-yellow-100 text-yellow-800': purchase.status === 'pending',
                                                'bg-green-100 text-green-800': purchase.status === 'completed'
                                            }">
                                                {{ purchase.status }}
                                            </span>
                                            <span class="text-green-500">₱{{ purchase.total_amount }}</span>
                                        </div>
                                    </div>
                                </div>
                                <Link :href="route('admin.purchase.index')" 
                                      class="mt-4 text-blue-500 hover:text-blue-700">
                                    View All Purchases →
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Expiring Medicines -->
                    <div class="col-span-12 md:col-span-6">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Expiring Medicines</h3>
                                <div class="space-y-3">
                                    <div v-for="medicine in props.expiringMedicines" 
                                         :key="medicine.id" 
                                         class="flex justify-between items-center border-b pb-2">
                                        <div>
                                            <p class="font-medium">{{ medicine.name }}</p>
                                            <p class="text-sm">{{ medicine.dosage }}</p>
                                            <p class="text-sm text-gray-500">
                                                Expires: {{ new Date(medicine.expdate).toLocaleDateString() }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <span :class="{
                                                'px-2 py-1 rounded text-xs font-medium': true,
                                                'bg-red-100 text-red-800': medicine.is_expired,
                                                'bg-red-100 text-red-800': medicine.status === 'critical',
                                                'bg-yellow-100 text-yellow-800': medicine.status === 'warning' && !medicine.is_expired
                                            }">
                                                {{ medicine.time_until_expiry }}
                                            </span>
                                            <p class="text-sm mt-1">
                                                <span :class="{
                                                    'text-yellow-500': medicine.quantity > 10,
                                                    'text-red-500': medicine.quantity <= 10
                                                }">
                                                    {{ medicine.quantity }} in stock
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <Link :href="route('admin.medicines.index')" 
                                      class="mt-4 text-blue-500 hover:text-blue-700">
                                    View All Medicines →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
