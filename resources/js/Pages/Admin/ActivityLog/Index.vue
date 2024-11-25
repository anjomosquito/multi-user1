<template>
    <Head title="Activity Log" />
    <AdminAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Activity Log</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Filters -->
                        <div class="flex flex-wrap gap-3 mb-4">
                            <!-- Search -->
                            <div class="flex-1 min-w-[200px] max-w-xs">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search..."
                                    class="w-full border rounded-lg px-3 py-1.5 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                                    @input="debouncedFilter"
                                />
                            </div>
                            
                            <!-- Date Range -->
                            <div class="flex items-center gap-2">
                                <input
                                    v-model="startDate"
                                    type="date"
                                    class="border rounded-lg px-2 py-1.5 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                                    @change="debouncedFilter"
                                />
                                <span class="text-gray-500 text-sm">to</span>
                                <input
                                    v-model="endDate"
                                    type="date"
                                    class="border rounded-lg px-2 py-1.5 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                                    @change="debouncedFilter"
                                />
                            </div>
                            <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" @click="clearFilters">Clear Filters</button>
                        </div>

                        <!-- Activity Log Table -->
                        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow">
                            <table class="w-full whitespace-nowrap text-sm">
                                <thead>
                                    <tr class="text-left font-semibold bg-gray-50 dark:bg-gray-700">
                                        <th class="px-4 py-2 text-gray-600 dark:text-gray-200">Description</th>
                                        <th class="px-4 py-2 text-gray-600 dark:text-gray-200 w-32">User</th>
                                        <th class="px-4 py-2 text-gray-600 dark:text-gray-200 w-32">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr v-for="activity in activities.data" 
                                        :key="activity.id" 
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <td class="px-4 py-2">
                                            <div class="text-gray-900 dark:text-gray-100">
                                                {{ activity.description }}
                                            </div>
                                            <div v-if="activity.properties && Object.keys(activity.properties).length > 0" 
                                                 class="mt-1 space-y-1">
                                                <div v-for="(value, key) in activity.properties" 
                                                     :key="key"
                                                     class="text-xs text-gray-500 dark:text-gray-400">
                                                    <span class="font-medium">{{ formatKey(key) }}:</span>
                                                    <span class="ml-1">{{ formatValue(value) }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400 text-sm">
                                            {{ activity.causer ? activity.causer.name : 'System' }}
                                        </td>
                                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400 text-sm">
                                            {{ formatDate(activity.created_at) }}
                                        </td>
                                    </tr>
                                    <tr v-if="activities.data.length === 0">
                                        <td colspan="4" class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">
                                            No activities found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <Pagination :links="activities.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>
    </AdminAuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    activities: {
        type: Object,
        required: true
    }
});

const searchQuery = ref('');
const startDate = ref('');
const endDate = ref('');

// Debounced filter function
const debouncedFilter = debounce(() => {
    router.get(
        route('admin.activity-log.index'),
        {
            search: searchQuery.value,
            start_date: startDate.value,
            end_date: endDate.value
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
}, 300);

// Watch for changes in filters
watch([searchQuery, startDate, endDate], () => {
    debouncedFilter();
});

// Format date for display
function formatDate(dateString) {
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Format key for display
function formatKey(key) {
    return key.split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

// Format value for display
function formatValue(value) {
    if (value === null || value === undefined) return 'N/A';
    if (typeof value === 'boolean') return value ? 'Yes' : 'No';
    if (typeof value === 'object') return JSON.stringify(value);
    return value;
}

// Clear filters
function clearFilters() {
    searchQuery.value = '';
    startDate.value = '';
    endDate.value = '';
    debouncedFilter();
}
</script>
