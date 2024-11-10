<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const { props } = usePage();
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>
        </template>

        <div class="py-2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-12 gap-4">
                    <!-- Total Medicines section taking up 3/12 of the width -->
                    <div class="col-span-12 md:col-span-3">
                        <div class="mt-4 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900 dark:text-gray-100">
                                <h3 class="text-lg font-semibold">Total Medicines:</h3>
                                <p>{{ props.medicineCount }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- You can add other sections here if needed -->
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
