<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script setup>
import AuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    totalMedicines: Number,
    totalPurchases: Number,
    totalRevenue: Number,
    lowStockCount: Number,
    lowStockMedicines: {
        type: Array,
        default: () => []
    },
    recentPurchases: {
        type: Array,
        default: () => []
    },
    recentActivities: {
        type: Array,
        default: () => []
    },
    expiringMedicines: {
        type: Array,
        default: () => []
    },
    revenueData: {
        type: Array,
        default: () => []
    },
    searchResults: {
        type: Object,
        default: () => ({
            medicines: [],
            purchases: [],
            users: []
        })
    },
    filters: {
        type: Object,
        default: () => ({
            query: ''
        })
    }
});

const searchQuery = ref(props.filters.query || '');
const isLoading = ref(false);
const searchResults = ref(props.searchResults || { medicines: [], purchases: [], users: [] });

// Format date to readable format
function formatDate(dateString) {
  return new Date(dateString).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

// Calculate days until expiry
function getDaysUntilExpiry(expiryDate) {
  const today = new Date();
  const expiry = new Date(expiryDate);
  const diffTime = expiry - today;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
}

// Get action needed based on expiry and stock
function getActionNeeded(medicine) {
  const daysUntilExpiry = getDaysUntilExpiry(medicine.expdate);
  if (daysUntilExpiry <= 0) return 'Remove';
  if (daysUntilExpiry <= 7) return 'Discount Sale';
  if (medicine.quantity <= 10) return 'Restock';
  return 'Monitor';
}

// Debounce function
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Handle search with debounce
const handleSearch = debounce(() => {
    if (searchQuery.value.length < 2) {
        searchResults.value = { medicines: [], purchases: [], users: [] };
        return;
    }

    isLoading.value = true;
    router.get(
        route('admin.dashboard.search'),
        { query: searchQuery.value },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                searchResults.value = page.props.searchResults;
                isLoading.value = false;
            },
            onError: () => {
                isLoading.value = false;
            }
        }
    );
}, 300);

// Watch for search query changes
watch(searchQuery, () => {
    handleSearch();
});

// Handle restock action
const handleRestock = (medicine) => {
  router.post(route('admin.inventory.restock'), {
    medicine_id: medicine.id,
    quantity: 50 // Default restock quantity
  }, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      // Show success message
      alert('Restock order created successfully');
    }
  });
};

// Handle discount action
const handleDiscount = (medicine) => {
  const discountPercent = 20; // Default 20% discount
  router.post(route('admin.inventory.discount'), {
    medicine_id: medicine.id,
    discount_percent: discountPercent
  }, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      // Show success message
      alert('Discount applied successfully');
    }
  });
};

// Setup revenue chart
let revenueChart = null;

onMounted(() => {
  const ctx = document.getElementById('revenueChart');
  if (ctx) {
    if (revenueChart) {
      revenueChart.destroy();
    }

    // Process revenue data
    const labels = props.revenueData?.map(item => formatDate(item.date)) || [];
    const values = props.revenueData?.map(item => item.total) || [];

    revenueChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Daily Revenue',
          data: values,
          backgroundColor: 'rgba(34, 197, 94, 0.2)',
          borderColor: 'rgb(34, 197, 94)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
            labels: {
              color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
            }
          },
          title: {
            display: true,
            text: 'Daily Revenue Trend',
            color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return '₱' + value.toLocaleString();
              },
              color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
            },
            grid: {
              color: document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
            }
          },
          x: {
            ticks: {
              color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
            },
            grid: {
              color: document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
            }
          }
        }
      }
    });
  }
});

// Watch for dark mode changes to update chart
watch(() => document.documentElement.classList.contains('dark'), (isDark) => {
  if (revenueChart) {
    revenueChart.options.plugins.title.color = isDark ? '#fff' : '#000';
    revenueChart.options.plugins.legend.labels.color = isDark ? '#fff' : '#000';
    revenueChart.options.scales.y.ticks.color = isDark ? '#fff' : '#000';
    revenueChart.options.scales.x.ticks.color = isDark ? '#fff' : '#000';
    revenueChart.update();
  }
}, { immediate: true });
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>
        </template>

        <div class="py-2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search Bar -->
                <div class="mb-4 relative">
                    <div class="relative">
                        <input 
                            type="text" 
                            placeholder="Search medicines, purchases, or users..." 
                            class="w-full px-4 py-2 pl-10 pr-4 rounded-lg border focus:outline-none focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            v-model="searchQuery"
                            @input="handleSearch"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    <!-- Search Results -->
                    <div v-if="searchQuery.length >= 2 && !isLoading" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border dark:border-gray-700">
                        <div v-if="searchResults.medicines?.length || searchResults.purchases?.length || searchResults.users?.length" class="p-4">
                            <!-- Medicines -->
                            <div v-if="searchResults.medicines?.length" class="mb-4">
                                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Medicines</h3>
                                <div class="space-y-2">
                                    <Link 
                                        v-for="medicine in searchResults.medicines" 
                                        :key="medicine.id"
                                        :href="route('admin.inventory.index')"
                                        class="block p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg"
                                    >
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">{{ medicine.name }}</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    Dosage: {{ medicine.dosage }} | Stock: {{ medicine.quantity }}
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-medium text-gray-900 dark:text-white">₱{{ medicine.price }}</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    Expires: {{ formatDate(medicine.expdate) }}
                                                </p>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                            </div>

                            <!-- Purchases -->
                            <div v-if="searchResults.purchases?.length" class="mb-4">
                                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Purchases</h3>
                                <div class="space-y-2">
                                    <Link 
                                        v-for="purchase in searchResults.purchases" 
                                        :key="purchase.id"
                                        :href="route('admin.purchase.show', purchase.id)"
                                        class="block p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg"
                                    >
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">Order #{{ purchase.id }}</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    By: {{ purchase.user }}
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-medium text-gray-900 dark:text-white">₱{{ purchase.total }}</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ formatDate(purchase.created_at) }}
                                                </p>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                            </div>

                            <!-- Users -->
                            <div v-if="searchResults.users?.length" class="mb-4">
                                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Users</h3>
                                <div class="space-y-2">
                                    <Link 
                                        v-for="user in searchResults.users" 
                                        :key="user.id"
                                        :href="route('admin.users.index')"
                                        class="block p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg"
                                    >
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    Joined: {{ formatDate(user.created_at) }}
                                                </p>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-4 text-center text-gray-500 dark:text-gray-400">
                            No results found
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div v-if="isLoading" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border dark:border-gray-700 p-4">
                        <div class="animate-pulse flex space-x-4">
                            <div class="flex-1 space-y-4 py-1">
                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                                <div class="space-y-2">
                                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-5/6"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-12 gap-4">
                    <!-- Total Medicines -->
                    <div class="col-span-12 md:col-span-3">
                        <Link :href="route('admin.inventory.index')" class="block">
                            <div class="mt-4 bg-white dark:bg-gray-800 overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 sm:rounded-lg">
                                <div class="p-6 text-gray-900 dark:text-gray-100">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold">Total Medicines</h3>
                                            <p class="text-3xl font-bold mt-2">{{ props.totalMedicines }}</p>
                                        </div>
                                        <div class="p-3 bg-blue-100 rounded-full">
                                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Total Purchases -->
                    <div class="col-span-12 md:col-span-3">
                        <Link :href="route('admin.purchase.index')" class="block">
                            <div class="mt-4 bg-white dark:bg-gray-800 overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 sm:rounded-lg">
                                <div class="p-6 text-gray-900 dark:text-gray-100">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold">Total Purchases</h3>
                                            <p class="text-3xl font-bold mt-2">{{ props.totalPurchases }}</p>
                                        </div>
                                        <div class="p-3 bg-green-100 rounded-full">
                                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Low Stock Alert -->
                    <div class="col-span-12 md:col-span-3">
                        <Link :href="route('admin.inventory.index')" class="block">
                            <div class="mt-4 bg-white dark:bg-gray-800 overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 sm:rounded-lg">
                                <div class="p-6 text-gray-900 dark:text-gray-100">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold">Low Stock Items</h3>
                                            <p class="text-3xl font-bold mt-2 text-red-500">{{ props.lowStockCount }}</p>
                                        </div>
                                        <div class="p-3 bg-red-100 rounded-full">
                                            <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="mt-4 space-y-2">
                                        <div v-for="medicine in props.lowStockMedicines?.slice(0, 3)" :key="medicine.id"
                                            class="flex justify-between items-center p-2 bg-red-50 rounded-md">
                                            <div class="flex-1">
                                                <p class="font-medium truncate">{{ medicine.name }}</p>
                                                <p class="text-xs text-gray-500">Stock: {{ medicine.quantity }}</p>
                                            </div>
                                            <button @click="handleRestock(medicine)"
                                                class="px-3 py-1 text-sm text-white bg-red-500 hover:bg-red-600 rounded-md">
                                                Restock
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Total Revenue -->
                    <div class="col-span-12 md:col-span-3">
                        <div class="mt-4 bg-white dark:bg-gray-800 overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 sm:rounded-lg">
                            <div class="p-6 text-gray-900 dark:text-gray-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold">Total Revenue</h3>
                                        <p class="text-3xl font-bold mt-2 text-green-500">₱{{ props.totalRevenue }}</p>
                                    </div>
                                    <div class="p-3 bg-green-100 rounded-full">
                                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Section -->
                <div class="grid grid-cols-12 gap-4 mt-4">
                    <!-- Recent Purchases -->
                    <div class="col-span-12 md:col-span-4">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold">Recent Purchases</h3>
                                    <Link :href="route('admin.purchase.index')" class="text-blue-500 hover:text-blue-700 text-sm">
                                        View All →
                                    </Link>
                                </div>
                                <!-- Scrollable Container -->
                                <div class="space-y-3 overflow-y-auto max-h-[400px] custom-scrollbar">
                                    <div v-for="purchase in props.recentPurchases" :key="purchase.id" 
                                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-medium">{{ purchase.user?.name || purchase.name || 'Unknown User' }}</span>
                                                    <span :class="{
                                                        'px-2 py-1 rounded-full text-xs font-medium': true,
                                                        'bg-yellow-100 text-yellow-800': purchase.status === 'pending',
                                                        'bg-green-100 text-green-800': purchase.status === 'completed',
                                                        'bg-blue-100 text-blue-800': purchase.status === 'confirmed'
                                                    }">
                                                        {{ purchase.status }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-500 mt-1">{{ formatDate(purchase.created_at) }}</p>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-sm font-medium">${{ purchase.total_amount }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="col-span-12 md:col-span-4">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold">Recent Activity</h3>
                                </div>
                                <div class="space-y-3 overflow-y-auto max-h-[400px] custom-scrollbar">
                                    <div v-for="activity in props.recentActivities" :key="activity.id" 
                                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ activity.description }}</p>
                                                <div class="mt-1 flex items-center space-x-2">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        By {{ activity.causer_name }}
                                                    </span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">•</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ formatDate(activity.created_at) }}
                                                    </span>
                                                </div>
                                                <div v-if="activity.properties && Object.keys(activity.properties).length > 0" 
                                                    class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                    <div v-if="activity.properties.old_price !== undefined" class="flex space-x-2">
                                                        <span>Price changed from ${{ activity.properties.old_price }} to ${{ activity.properties.new_price }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="!props.recentActivities.length" class="text-center text-gray-500 dark:text-gray-400 py-4">
                                        No recent activity
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expiring Medicines -->
                    <div class="col-span-12 md:col-span-4">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold">Expiring Medicines</h3>
                                    <Link :href="route('admin.inventory.index')" class="text-blue-500 hover:text-blue-700 text-sm">
                                        View All →
                                    </Link>
                                </div>
                                <!-- Scrollable Container -->
                                <div class="space-y-3 overflow-y-auto max-h-[400px] custom-scrollbar">
                                    <div v-for="medicine in props.expiringMedicines" :key="medicine.id" 
                                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-medium">{{ medicine.name }}</span>
                                                    <span :class="{
                                                        'px-2 py-1 rounded-full text-xs font-medium': true,
                                                        'bg-red-100 text-red-800': getDaysUntilExpiry(medicine.expdate) <= 7,
                                                        'bg-yellow-100 text-yellow-800': getDaysUntilExpiry(medicine.expdate) > 7 && getDaysUntilExpiry(medicine.expdate) <= 30,
                                                        'bg-green-100 text-green-800': getDaysUntilExpiry(medicine.expdate) > 30
                                                    }">
                                                        {{ getDaysUntilExpiry(medicine.expdate) }} days left
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-500 mt-1">{{ medicine.dosage }}</p>
                                                <div class="mt-2 grid grid-cols-2 gap-2">
                                                    <p class="text-sm">
                                                        <span class="text-gray-600 dark:text-gray-400">Stock:</span>
                                                        <span :class="{
                                                            'font-medium ml-1': true,
                                                            'text-red-500': medicine.quantity <= 10,
                                                            'text-yellow-500': medicine.quantity > 10 && medicine.quantity <= 20,
                                                            'text-green-500': medicine.quantity > 20
                                                        }">
                                                            {{ medicine.quantity }} units
                                                        </span>
                                                    </p>
                                                    <p class="text-sm">
                                                        <span class="text-gray-600 dark:text-gray-400">Action:</span>
                                                        <span class="font-medium ml-1">{{ getActionNeeded(medicine) }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <button @click="handleRestock(medicine)" 
                                                    class="p-2 hover:bg-blue-100 rounded-full transition-colors duration-200" 
                                                    title="Restock">
                                                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                                <button @click="handleDiscount(medicine)" 
                                                    class="p-2 hover:bg-green-100 rounded-full transition-colors duration-200" 
                                                    title="Apply Discount">
                                                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Chart -->
                <div class="mt-4">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 sm:rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Revenue Trend</h3>
                        </div>
                        <div class="h-[300px]">
                            <canvas id="revenueChart"></canvas>
                        </div>
                        <div v-if="!props.revenueData?.length" class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                            No revenue data available
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
