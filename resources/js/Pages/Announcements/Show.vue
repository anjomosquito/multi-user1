<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Announcement Details
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8">
                        <!-- Back Button -->
                        <div class="mb-6">
                            <Link
                                :href="route('announcements.index')"
                                class="text-indigo-600 hover:text-indigo-800 flex items-center"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Announcements
                            </Link>
                        </div>

                        <!-- Announcement Content -->
                        <article class="prose lg:prose-xl max-w-none">
                            <header class="mb-8">
                                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                                    {{ announcement.title }}
                                </h1>
                                <div class="flex items-center text-gray-600">
                                    <span class="mr-4">
                                        Published by {{ announcement.admin.name }}
                                    </span>
                                    <span>
                                        {{ formatDate(announcement.published_at) }}
                                    </span>
                                </div>
                            </header>

                            <div class="text-gray-800 leading-relaxed whitespace-pre-wrap">
                                {{ announcement.content }}
                            </div>
                        </article>

                        <!-- Share Links -->
                        <div class="mt-8 pt-8 border-t border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Share this announcement</h3>
                            <div class="flex space-x-4">
                                <a
                                    :href="'https://twitter.com/intent/tweet?text=' + encodeURIComponent(announcement.title) + '&url=' + encodeURIComponent(window.location.href)"
                                    target="_blank"
                                    class="text-blue-400 hover:text-blue-500"
                                >
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                    </svg>
                                </a>
                                <a
                                    :href="'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href)"
                                    target="_blank"
                                    class="text-blue-600 hover:text-blue-700"
                                >
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { format } from 'date-fns';

const props = defineProps({
    announcement: Object,
});

const formatDate = (date) => {
    return format(new Date(date), 'MMMM dd, yyyy');
};
</script>

<style>
.prose {
    max-width: none;
}
</style>
