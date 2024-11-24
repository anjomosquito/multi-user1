<template>
    <Head title="Announcements" />
    <AdminAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Announcements</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex flex-col sm:flex-row justify-between mb-6 gap-4">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <div class="flex items-center">
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Search announcements..."
                                        class="border rounded-lg px-4 py-2 w-full sm:w-80 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                                        @input="filter"
                                    />
                                </div>
                                <div class="flex items-center">
                                    <select
                                        v-model="status"
                                        class="border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                                        @change="filter"
                                    >
                                        <option value="">All Status</option>
                                        <option value="draft">Draft</option>
                                        <option value="published">Published</option>
                                    </select>
                                </div>
                            </div>
                            <Link
                                :href="route('admin.announcements.create')"
                                class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg inline-flex items-center justify-center"
                            >
                                <i class="fas fa-plus mr-2"></i>
                                Create Announcement
                            </Link>
                        </div>

                        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow">
                            <table class="w-full whitespace-nowrap">
                                <thead>
                                    <tr class="text-left font-bold bg-gray-50 dark:bg-gray-700">
                                        <th class="px-6 py-3 text-gray-600 dark:text-gray-200">Title</th>
                                        <th class="px-6 py-3 text-gray-600 dark:text-gray-200">Status</th>
                                        <th class="px-6 py-3 text-gray-600 dark:text-gray-200">Published At</th>
                                        <th class="px-6 py-3 text-gray-600 dark:text-gray-200">Created At</th>
                                        <th class="px-6 py-3 text-gray-600 dark:text-gray-200">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr v-for="announcement in announcements.data" 
                                        :key="announcement.id" 
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ announcement.title }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-md">
                                                {{ announcement.content }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                :class="{
                                                    'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100': announcement.status === 'published',
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100': announcement.status === 'draft'
                                                }"
                                                class="px-3 py-1 rounded-full text-xs font-medium"
                                            >
                                                {{ announcement.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                            {{ formatDate(announcement.published_at) }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                            {{ formatDate(announcement.created_at) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-4">
                                                <Link
                                                    :href="route('admin.announcements.edit', announcement.id)"
                                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </Link>
                                                <button
                                                    @click="confirmAnnouncementDeletion(announcement)"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="announcements.data.length === 0">
                                        <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400" colspan="5">
                                            No announcements found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <Pagination :links="announcements.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="confirmingAnnouncementDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Delete Announcement
                </h2>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete this announcement? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end space-x-4">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <DangerButton
                        class="ml-3"
                        :class="{ 'opacity-25': processing }"
                        :disabled="processing"
                        @click="deleteAnnouncement"
                    >
                        Delete Announcement
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AdminAuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { format } from 'date-fns';

const props = defineProps({
    announcements: Object,
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: ''
        })
    },
});

const search = ref(props.filters.search);
const status = ref(props.filters.status);
const confirmingAnnouncementDeletion = ref(false);
const announcementToDelete = ref(null);
const processing = ref(false);

function filter() {
    router.get(
        route('admin.announcements.index'),
        { search: search.value, status: status.value },
        {
            preserveState: true,
            replace: true,
        }
    );
}

function confirmAnnouncementDeletion(announcement) {
    announcementToDelete.value = announcement;
    confirmingAnnouncementDeletion.value = true;
}

function deleteAnnouncement() {
    processing.value = true;
    router.delete(route('admin.announcements.destroy', announcementToDelete.value.id), {
        onSuccess: () => {
            closeModal();
        },
        onFinish: () => {
            processing.value = false;
        }
    });
}

function closeModal() {
    confirmingAnnouncementDeletion.value = false;
    announcementToDelete.value = null;
}

function formatDate(date) {
    if (!date) return '';
    return format(new Date(date), 'MMM dd, yyyy HH:mm');
}
</script>