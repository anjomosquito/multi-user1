<template>
    <Head title="Purchase Details" />

    <AdminAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Purchase Details #{{ purchase.id }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Purchase Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">Purchase Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Order ID</p>
                                    <p class="font-medium">#{{ purchase.id }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800': purchase.status === 'pending',
                                            'bg-blue-100 text-blue-800': purchase.status === 'processing',
                                            'bg-green-100 text-green-800': purchase.status === 'completed'
                                        }">
                                        {{ purchase.status }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Date Ordered</p>
                                    <p class="font-medium">{{ formatDate(purchase.created_at) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Last Updated</p>
                                    <p class="font-medium">{{ formatDate(purchase.updated_at) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">Customer Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Name</p>
                                    <p class="font-medium">{{ purchase.user.name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Email</p>
                                    <p class="font-medium">{{ purchase.user.email }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Medicine Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">Medicine Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Medicine Name</p>
                                    <p class="font-medium">{{ purchase.name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Quantity</p>
                                    <p class="font-medium">{{ purchase.quantity }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Amount</p>
                                    <p class="font-medium">₱{{ purchase.total_amount }}</p>
                                </div>
                                <div v-if="purchase.dosage">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Dosage</p>
                                    <p class="font-medium">{{ purchase.dosage }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end space-x-4">
                            <Link 
                                :href="route('admin.purchase.index')"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                            >
                                Back to List
                            </Link>
                            <button
                                v-if="purchase.status === 'pending'"
                                @click="markAsReady"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                            >
                                Mark as Ready
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminAuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';

const props = defineProps({
    purchase: Object
});

function formatDate(dateString) {
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function markAsReady() {
    router.post(route('admin.purchase.mark-ready', props.purchase.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Handle success
        }
    });
}
</script>
