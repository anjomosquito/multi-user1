<script setup>
import { ref, onMounted, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const showingNavigationDropdown = ref(false);
const unreadCount = ref(0);

// Function to fetch unread count
const fetchUnreadCount = async () => {
    try {
        const response = await axios.get(route('chat.unread-count'));
        unreadCount.value = response.data.count;
    } catch (error) {
        console.error('Failed to fetch unread count:', error);
    }
};

// Update unread count periodically
onMounted(() => {
    fetchUnreadCount();
    setInterval(fetchUnreadCount, 30000); // Update every 30 seconds
});
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                <ApplicationLogo
                                    class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    <svg width="40px" height="40px" viewBox="0 0 24 24" id="dashboard"
                                        xmlns="http://www.w3.org/2000/svg" class="icon multi-color">
                                        <path id="secondary-fill"
                                            d="M9,12H4a1,1,0,0,1-1-1V6h7v5A1,1,0,0,1,9,12Zm12,8V15H14v5a1,1,0,0,0,1,1h5A1,1,0,0,0,21,20Z"
                                            style="fill: rgb(44, 169, 188); stroke-width: 2;"></path>
                                        <path id="tertiary-fill"
                                            d="M9,21H4a1,1,0,0,1-1-1V19h7v1A1,1,0,0,1,9,21ZM21,7V6H14V7a1,1,0,0,0,1,1h5A1,1,0,0,0,21,7Z"
                                            style="fill: #b7b7b7; stroke-width: 2;"></path>
                                        <path id="primary-stroke"
                                            d="M9,12H4a1,1,0,0,1-1-1V4A1,1,0,0,1,4,3H9a1,1,0,0,1,1,1v7A1,1,0,0,1,9,12ZM21,7V4a1,1,0,0,0-1-1H15a1,1,0,0,0-1,1V7a1,1,0,0,0,1,1h5A1,1,0,0,0,21,7ZM10,20V17a1,1,0,0,0-1-1H4a1,1,0,0,0-1,1v3a1,1,0,0,0,1,1H9A1,1,0,0,0,10,20Zm11,0V13a1,1,0,0,0-1-1H15a1,1,0,0,0-1,1v7a1,1,0,0,0,1,1h5A1,1,0,0,0,21,20Z"
                                            style="fill: none; stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;">
                                        </path>
                                    </svg>
                                    Dashboard
                                </NavLink>

                                <NavLink :href="route('medicines.index')" :active="route().current('medicines.index')">
                                    <i class="fas fa-capsules text-green-500"
                                        style="font-size: 25px; width: 30px; height: 30px"></i> Medicines
                                </NavLink>


                                <NavLink :href="route('cart.index')" :active="route().current('cart.index')">
                                    <i class="fas fa-shopping-cart text-yellow-500"
                                        style="font-size: 25px; width: 30px; height: 30px"></i>
                                    Cart
                                </NavLink>

                                <NavLink :href="route('purchase.index')" :active="route().current('purchase.index')">
                                    <i class="fas fa-receipt text-yellow-500"
                                        style="font-size: 25px; width: 30px; height: 30px"></i>
                                    Purchase
                                </NavLink>

                                <NavLink :href="route('chat.index')" :active="route().current('chat.index')">
                                    <i class="fas fa-comments text-orange-500"
                                        style="font-size: 25px; width: 30px; height: 30px"></i>
                                    Chat Support
                                    <span v-if="unreadCount > 0"
                                        class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                                        {{ unreadCount }}
                                    </span>
                                </NavLink>

                                <NavLink :href="route('announcements.index')" :active="route().current('announcements.index')">
                                    <i class="fas fa-bullhorn text-blue-500"
                                        style="font-size: 25px; width: 30px; height: 30px"></i>
                                    Announcements
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <!-- Settings Dropdown -->
                            <div class="ml-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{
                                        hidden: showingNavigationDropdown,
                                        'inline-flex': !showingNavigationDropdown,
                                    }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{
                                        hidden: !showingNavigationDropdown,
                                        'inline-flex': showingNavigationDropdown,
                                    }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('medicines.index')" :active="route().current('medicines.index')">
                            Medicines
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('cart.index')" :active="route().current('cart.index')">
                            Cart
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('purchase.index')" :active="route().current('purchase.index')">
                            Purchase
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('chat.index')" :active="route().current('chat.index')">
                            Chat Support
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('announcements.index')" :active="route().current('announcements.index')">
                            Announcements
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800 dark:text-gray-200">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="font-medium text-sm text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white dark:bg-gray-800 shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
