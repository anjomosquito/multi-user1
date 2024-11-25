<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

defineProps({
    auth: Object
});

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 sticky top-0 z-50">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('admin.dashboard')">
                                    <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden sm:-my-px sm:ml-10 sm:flex overflow-x-auto space-x-4 items-center pb-2" style="scrollbar-width: none;">
                                <!-- Dashboard -->
                                <NavLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')" class="whitespace-nowrap">
                                    <i class="fas fa-home text-blue-500 mr-1" style="font-size: 20px;"></i>
                                    Dashboard
                                </NavLink>

                                <!-- Inventory Management Group -->
                                <div class="flex items-center space-x-4">
                                    <NavLink :href="route('admin.medicines.index')" :active="route().current('admin.medicines.index')" class="whitespace-nowrap">
                                        <i class="fas fa-capsules text-green-500 mr-1" style="font-size: 20px;"></i>
                                        Medicines
                                    </NavLink>

                                    <NavLink :href="route('admin.inventory.index')" :active="route().current('admin.inventory.index')" class="whitespace-nowrap">
                                        <i class="fas fa-box text-purple-500 mr-1" style="font-size: 20px;"></i>
                                        Inventory
                                    </NavLink>

                                    <NavLink :href="route('admin.categories.index')" :active="route().current('admin.categories.index')" class="whitespace-nowrap">
                                        <i class="fas fa-list-alt text-blue-500 mr-1" style="font-size: 20px;"></i>
                                        Categories
                                    </NavLink>
                                </div>

                                <!-- Communication Group -->
                                <div class="flex items-center space-x-4">
                                    <NavLink :href="route('admin.chat.index')" :active="route().current('admin.chat.index')" class="whitespace-nowrap">
                                        <i class="fas fa-comments text-orange-500 mr-1" style="font-size: 20px;"></i>
                                        Chat
                                    </NavLink>

                                    <NavLink :href="route('admin.purchase.index')" :active="route().current('admin.purchase.index')" class="whitespace-nowrap">
                                        <i class="fas fa-shopping-cart text-yellow-500 mr-1" style="font-size: 20px;"></i>
                                        Purchases
                                    </NavLink>
                                </div>

                                <!-- User Management Group -->
                                <div class="flex items-center space-x-4">
                                    <NavLink :href="route('admin.users.index')" :active="route().current('admin.users.index')" class="whitespace-nowrap">
                                        <i class="fas fa-users text-indigo-500 mr-1" style="font-size: 20px;"></i>
                                        Users
                                    </NavLink>

                                    
                                    <NavLink :href="route('admin.announcements.index')" :active="route().current('admin.announcements.index')" class="whitespace-nowrap">
                                        <i class="fas fa-bullhorn text-yellow-500 mr-1" style="font-size: 20px;"></i>
                                        Announcements
                                    </NavLink>
                                </div>

                                <!-- System Group -->
                                <NavLink :href="route('admin.activity-log.index')" :active="route().current('admin.activity-log.index')" class="whitespace-nowrap">
                                    <i class="fas fa-history text-gray-500 mr-1" style="font-size: 20px;"></i>
                                    Activity Log
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
                                                {{ $page.props.auth.admin.name }}

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
                                        <DropdownLink :href="route('admin.profile.edit')"> Profile </DropdownLink>
                                        <DropdownLink :href="route('admin.logout')" method="post" as="button">
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
                <div :class="{ 'transform translate-x-0': showingNavigationDropdown, 'transform -translate-x-full': !showingNavigationDropdown }"
                    class="sm:hidden fixed inset-0 z-50 bg-white dark:bg-gray-800 w-72 transition-transform duration-300 ease-in-out shadow-lg overflow-y-auto">
                    <div class="pt-2 pb-3 space-y-1">
                        <!-- Mobile Logo -->
                        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                            <ApplicationLogo class="block h-8 w-auto fill-current text-gray-800 dark:text-gray-200" />
                            <button @click="showingNavigationDropdown = false" class="text-gray-500">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Mobile Navigation Groups -->
                        <div class="px-2 py-2 space-y-1">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Main
                            </div>
                            <ResponsiveNavLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')" class="flex items-center">
                                <i class="fas fa-home text-blue-500 mr-2" style="font-size: 18px;"></i>
                                Dashboard
                            </ResponsiveNavLink>
                        </div>

                        <div class="px-2 py-2 space-y-1">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Inventory
                            </div>
                            <ResponsiveNavLink :href="route('admin.medicines.index')" :active="route().current('admin.medicines.index')" class="flex items-center">
                                <i class="fas fa-capsules text-green-500 mr-2" style="font-size: 18px;"></i>
                                Medicines
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.inventory.index')" :active="route().current('admin.inventory.index')" class="flex items-center">
                                <i class="fas fa-box text-purple-500 mr-2" style="font-size: 18px;"></i>
                                Inventory
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.categories.index')" :active="route().current('admin.categories.index')" class="flex items-center">
                                <i class="fas fa-list-alt text-blue-500 mr-2" style="font-size: 18px;"></i>
                                Categories
                            </ResponsiveNavLink>
                        </div>

                        <div class="px-2 py-2 space-y-1">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Communication
                            </div>
                            <ResponsiveNavLink :href="route('admin.chat.index')" :active="route().current('admin.chat.index')" class="flex items-center">
                                <i class="fas fa-comments text-orange-500 mr-2" style="font-size: 18px;"></i>
                                Chat
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.announcements.index')" :active="route().current('admin.announcements.index')" class="flex items-center">
                                <i class="fas fa-bullhorn text-yellow-500 mr-2" style="font-size: 18px;"></i>
                                Announcements
                            </ResponsiveNavLink>
                        </div>

                        <div class="px-2 py-2 space-y-1">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Users & Transactions
                            </div>
                            <ResponsiveNavLink :href="route('admin.users.index')" :active="route().current('admin.users.index')" class="flex items-center">
                                <i class="fas fa-users text-indigo-500 mr-2" style="font-size: 18px;"></i>
                                Users
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.purchase.index')" :active="route().current('admin.purchase.index')" class="flex items-center">
                                <i class="fas fa-shopping-cart text-yellow-500 mr-2" style="font-size: 18px;"></i>
                                Purchases
                            </ResponsiveNavLink>
                        </div>

                        <div class="px-2 py-2 space-y-1">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                System
                            </div>
                            <ResponsiveNavLink :href="route('admin.activity-log.index')" :active="route().current('admin.activity-log.index')" class="flex items-center">
                                <i class="fas fa-history text-gray-500 mr-2" style="font-size: 18px;"></i>
                                Activity Log
                            </ResponsiveNavLink>
                        </div>

                        <!-- Profile Section -->
                        <div class="px-2 py-2 space-y-1 border-t border-gray-200 dark:border-gray-700">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Account
                            </div>
                            <ResponsiveNavLink :href="route('admin.profile.edit')" class="flex items-center">
                                <i class="fas fa-user text-gray-500 mr-2" style="font-size: 18px;"></i>
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.logout')" method="post" as="button" class="flex items-center w-full">
                                <i class="fas fa-sign-out-alt text-gray-500 mr-2" style="font-size: 18px;"></i>
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>

                <!-- Backdrop -->
                <div v-if="showingNavigationDropdown" 
                     class="fixed inset-0 bg-black bg-opacity-50 z-40 sm:hidden"
                     @click="showingNavigationDropdown = false">
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
