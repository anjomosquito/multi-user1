<template>
    <Head :title="announcement.title" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ announcement.title }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-6">
                            <Link
                                :href="route('announcements.index')"
                                class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                            >
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back to Announcements
                            </Link>
                        </div>

                        <div class="prose dark:prose-invert max-w-none">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h1 class="text-3xl font-bold">{{ announcement.title }}</h1>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                        Published on {{ formatDate(announcement.published_at) }} by {{ announcement.admin.name }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 space-y-6 text-lg">
                                {{ announcement.content }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { format } from 'date-fns';

defineProps({
    announcement: {
        type: Object,
        required: true,
    },
});

function formatDate(date) {
    return date ? format(new Date(date), 'MMMM d, yyyy') : '';
}
</script>
