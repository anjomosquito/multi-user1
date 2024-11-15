<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    medicineCount: Number,
    purchaseCount: Number,
    recentPurchases: Array,
    totalSpent: Number,
    availableMedicines: Array,
    promoMedicines: Array
});
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>
        </template>

        <div class="py-2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Summary Cards -->
                <div class="grid grid-cols-12 gap-4">
                    <!-- Total Available Medicines -->
                    <div class="col-span-12 md:col-span-4">
                        <div class="mt-4 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900 dark:text-gray-100">
                                <h3 class="text-lg font-semibold">Available Medicines:</h3>
                                <p class="text-2xl">{{ medicineCount }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Your Purchases -->
                    <div class="col-span-12 md:col-span-4">
                        <div class="mt-4 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900 dark:text-gray-100">
                                <h3 class="text-lg font-semibold">Your Purchases:</h3>
                                <p class="text-2xl">{{ purchaseCount }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Spent -->
                    <div class="col-span-12 md:col-span-4">
                        <div class="mt-4 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900 dark:text-gray-100">
                                <h3 class="text-lg font-semibold">Total Spent:</h3>
                                <p class="text-2xl text-green-500">₱{{ totalSpent }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Purchases Section -->
                <div class="mt-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Recent Purchases</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Medicine
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Quantity
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Total
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Status
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Date
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="purchase in recentPurchases" :key="purchase.id">
                                            <td class="px-6 py-4">{{ purchase.medicine?.name || purchase.name }}</td>
                                            <td class="px-6 py-4">{{ purchase.quantity }}</td>
                                            <td class="px-6 py-4">₱{{ purchase.total_amount }}</td>
                                            <td class="px-6 py-4">
                                                <span :class="{
                                                    'px-2 py-1 rounded text-xs font-medium': true,
                                                    'bg-yellow-100 text-yellow-800': purchase.status === 'pending',
                                                    'bg-green-100 text-green-800': purchase.status === 'completed'
                                                }">
                                                    {{ purchase.status || 'pending' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">{{ new Date(purchase.created_at).toLocaleDateString()
                                                }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <Link :href="route('purchase.index')"
                                class="mt-4 inline-block text-blue-500 hover:text-blue-700">
                            View All Purchases →
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Available Medicines & Promos -->
                <div class="grid grid-cols-12 gap-4 mt-6">
                    <!-- Available Medicines -->
                    <div class="col-span-12 md:col-span-8">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Available Medicines</h3>
                                <!-- Scrollable container -->
                                <div class="flex overflow-x-auto py-2 space-x-6" style="max-width: 100%;">
                                    <div v-for="medicine in availableMedicines" :key="medicine.id"
                                        class="border rounded-lg p-6 md:p-8 flex-shrink-0 flex flex-col items-start min-w-[250px]">
                                        <h4 class="font-medium text-xl">{{ medicine.name }}</h4>
                                        <p class="text-sm text-gray-500 mt-1">{{ medicine.dosage }}</p>
                                        <p class="mt-4 text-green-600 text-lg">₱{{ medicine.mprice }}</p>
                                        <p class="text-sm text-gray-500 mt-2">Stock: {{ medicine.quantity }}</p>
                                        <p class="text-sm text-gray-500 mt-2">Expiration: {{ medicine.expdate }}</p>
                                    </div>
                                </div>
                                <Link :href="route('medicines.index')"
                                    class="mt-4 inline-block text-blue-500 hover:text-blue-700">
                                View All Medicines →
                                </Link>
                            </div>
                        </div>

                    </div>

                    <!-- Special Promos -->
                    <div class="col-span-12 md:col-span-4">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-10">
                                <h3 class="text-lg font-semibold mb-3">Special Offers</h3>
                                <!-- Scrollable container -->
                                <div class="space-y-4 h-64 overflow-y-auto">
                                    <div v-for="medicine in promoMedicines" :key="medicine.id"
                                        class="border rounded-lg p-4 bg-gradient-to-r from-yellow-50 to-yellow-100">
                                        <h4 class="font-medium">{{ medicine.name }}</h4>
                                        <div class="flex items-center mt-2">
                                            <span class="text-gray-500 line-through text-sm">₱{{ medicine.hprice
                                                }}</span>
                                            <span class="text-green-600 font-bold ml-2">₱{{ medicine.mprice }}</span>
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1">Stock: {{ medicine.quantity }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
