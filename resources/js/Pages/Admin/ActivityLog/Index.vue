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
                        <div class="flex flex-col sm:flex-row justify-between mb-6 gap-4">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <!-- Search -->
                                <div class="flex items-center">
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Search in descriptions..."
                                        class="border rounded-lg px-4 py-2 w-full sm:w-80 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                                        @input="filter"
                                    />
                                </div>
                                <!-- Log Type Filter -->
                                <div class="flex items-center">
                                    <select
                                        v-model="logName"
                                        class="border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                                        @change="filter"
                                    >
                                        <option value="">All Log Types</option>
                                        <option v-for="name in logNames" :key="name" :value="name">
                                            {{ formatLogName(name) }}
                                        </option>
                                    </select>
                                </div>
                                <!-- Date Range -->
                                <div class="flex items-center space-x-2">
                                    <input
                                        v-model="startDate"
                                        type="date"
                                        class="border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                                        @change="filter"
                                    />
                                    <span class="text-gray-500">to</span>
                                    <input
                                        v-model="endDate"
                                        type="date"
                                        class="border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                                        @change="filter"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Activity Log Table -->
                        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow">
                            <table class="w-full whitespace-nowrap">
                                <thead>
                                    <tr class="text-left font-bold bg-gray-50 dark:bg-gray-700">
                                        <th class="px-6 py-3 text-gray-600 dark:text-gray-200">Description</th>
                                        <th class="px-6 py-3 text-gray-600 dark:text-gray-200">Type</th>
                                        <th class="px-6 py-3 text-gray-600 dark:text-gray-200">User</th>
                                        <th class="px-6 py-3 text-gray-600 dark:text-gray-200">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr v-for="activity in activities.data" 
                                        :key="activity.id" 
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ activity.description }}
                                            </div>
                                            <div v-if="activity.properties && Object.keys(activity.properties).length > 0" 
                                                 class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                <div v-for="(value, key) in activity.properties" :key="key">
                                                    <strong>{{ formatKey(key) }}:</strong> {{ formatValue(value) }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium"
                                                  :class="getLogTypeClass(activity.log_name)">
                                                {{ formatLogName(activity.log_name) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                            {{ activity.causer ? activity.causer.name : 'System' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                            {{ formatDate(activity.created_at) }}
                                        </td>
                                    </tr>
                                    <tr v-if="activities.data.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
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
import { Head, router } from '@inertiajs/vue3';
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { format } from 'date-fns';
import { debounce } from 'lodash';

const props = defineProps({
    activities: Object,
    logNames: Array,
    filters: {
        type: Object,
        default: () => ({
            search: '',
            log_name: '',
            start_date: '',
            end_date: ''
        })
    }
});

const search = ref(props.filters.search || '');
const logName = ref(props.filters.log_name || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

// Debounce the filter function
const debouncedFilter = debounce(() => {
    router.get(
        route('admin.activity-log.index'),
        { 
            search: search.value,
            log_name: logName.value,
            start_date: startDate.value,
            end_date: endDate.value
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
}, 300);

function filter() {
    debouncedFilter();
}

function formatDate(date) {
    if (!date) return 'N/A';
    return format(new Date(date), 'MMM d, yyyy HH:mm:ss');
}

function formatLogName(name) {
    if (!name) return 'System';
    return name.charAt(0).toUpperCase() + name.slice(1);
}

function formatKey(key) {
    return key.split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function formatValue(value) {
    if (value === null || value === undefined) return 'N/A';
    if (typeof value === 'boolean') return value ? 'Yes' : 'No';
    if (typeof value === 'object') return JSON.stringify(value);
    return value;
}

function getLogTypeClass(logName) {
    const classes = {
        announcement: 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100',
        medicine: 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
        purchase: 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100',
        default: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100'
    };
    return classes[logName] || classes.default;
}
</script>
