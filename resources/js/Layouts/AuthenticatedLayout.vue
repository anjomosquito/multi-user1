<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
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
                                        class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    <svg width="40px" height="40px" viewBox="0 0 24 24" id="dashboard" xmlns="http://www.w3.org/2000/svg" class="icon multi-color"><path id="secondary-fill" d="M9,12H4a1,1,0,0,1-1-1V6h7v5A1,1,0,0,1,9,12Zm12,8V15H14v5a1,1,0,0,0,1,1h5A1,1,0,0,0,21,20Z" style="fill: rgb(44, 169, 188); stroke-width: 2;"></path><path id="tertiary-fill" d="M9,21H4a1,1,0,0,1-1-1V19h7v1A1,1,0,0,1,9,21ZM21,7V6H14V7a1,1,0,0,0,1,1h5A1,1,0,0,0,21,7Z" style="fill: #b7b7b7; stroke-width: 2;"></path><path id="primary-stroke" d="M9,12H4a1,1,0,0,1-1-1V4A1,1,0,0,1,4,3H9a1,1,0,0,1,1,1v7A1,1,0,0,1,9,12ZM21,7V4a1,1,0,0,0-1-1H15a1,1,0,0,0-1,1V7a1,1,0,0,0,1,1h5A1,1,0,0,0,21,7ZM10,20V17a1,1,0,0,0-1-1H4a1,1,0,0,0-1,1v3a1,1,0,0,0,1,1H9A1,1,0,0,0,10,20Zm11,0V13a1,1,0,0,0-1-1H15a1,1,0,0,0-1,1v7a1,1,0,0,0,1,1h5A1,1,0,0,0,21,20Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path></svg>
                                    Dashboard
                                </NavLink>

                                <NavLink :href="route('medicines.index')" :active="route().current('medicines.index')">
                                    <svg width="45px" height="45px" viewBox="0 0 1024 1024" class="icon"  version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M776 239l-48-13 48-13 13-49 13 49 48 13-48 13-13 48-13-48zM824 292l-19-5 19-4 4-19 5 19 18 4-18 5-5 18-4-18z" fill="#FDCD60" /><path d="M184 562l-21-5 21-5 5-21 5 21 21 5-21 5-5 21-5-21z" fill="#FDCD60" /><path d="M212 255m-9 0a9 9 0 1 0 18 0 9 9 0 1 0-18 0Z" fill="#5546CB" /><path d="M805 754a25 25 0 1 1 25-25 25 25 0 0 1-25 25z m0-36a10 10 0 1 0 10 10 10 10 0 0 0-10-10z" fill="#5546CB" /><path d="M747.553228 751.6268l51.618795 51.618795-125.942789 125.942788-51.618795-51.618795 125.942789-125.942788Z" fill="#F97744" /><path d="M695 820l-40 40c-10 10-10 27 1 38 9 8 25 9 36-1l40-40zM232 847l-27-6 27-7 6-27 7 27 27 7-27 6-7 27-6-27z" fill="#FDCD60" /><path d="M723 677V460H308v217zM459 559h49v-49h20v49h49v20h-49v49h-20v-49h-49z" fill="#FFFFFF" /><path d="M632 765a47 47 0 0 1 66 0l11 11 5-5 9-7v-67H307v132a34 34 0 0 0 34 34h288a47 47 0 0 1 11-19l3-3-11-11a47 47 0 0 1 0-65zM723 369a34 34 0 0 0-34-34H544v-74h-58v73H341a34 34 0 0 0-34 34v71h416zM508 628h20v-49h49v-20h-49v-49h-20v49h-49v20h49v49z" fill="#FF8859" /><path d="M424 153h19v87.98h-19zM464 153h19v87.98h-19zM385 154v87h19v-88h-19zM645 240v-86h-24v88h23zM582 153h19v87.98h-19zM503 153h19v87.98h-19zM543 153h19v87.98h-19z" fill="#FDCD60" /><path d="M724 893a27 27 0 0 0 37-37zM695 790l-11-11a27 27 0 0 0-38 38l11 11z" fill="#AFBCF3" /><path d="M650 889l-2-6zM748 777a26 26 0 0 0-18 8l-48 48 13-13 37 36-20 20 55-55a26 26 0 0 0-18-44z" fill="#F97744" /><path d="M781 836a47 47 0 0 0-38-80V369a54 54 0 0 0-54-54H564v-54h80a21 21 0 0 0 21-21v-86a21 21 0 0 0-21-21H386a21 21 0 0 0-21 21v87a21 21 0 0 0 21 21h80v54H341a54 54 0 0 0-54 54v459a54 54 0 0 0 54 54h286a47 47 0 0 0 80 28l3-3a47 47 0 0 0 66-66zM621 153h23v87h-23z m-39 0h19v88h-19z m141 307v217H308V460zM543 153h19v88h-19z m-39 0h19v88h-20z m-99 88h-20v-87h19z m39 0h-20v-88h19z m20 0v-88h19v88zM307 369a34 34 0 0 1 34-34h145v-74h59v73h144a34 34 0 0 1 34 34v71H307z m333 475a47 47 0 0 0-11 19H341a34 34 0 0 1-34-34V697h416v66l-9 7-5 5-11-11a47 47 0 0 0-66 66l11 11z m7-65a27 27 0 0 1 38 0l11 11-38 38-11-11a27 27 0 0 1 0-38z m111 112a27 27 0 0 1-34 3l37-37a27 27 0 0 1-3 34z m8-69l-55 55-20 20c-10 10-27 9-36 1l-5-7-2-6a26 26 0 0 1 7-23l27-26 48-48a26 26 0 0 1 37 37z" fill="#5546CB" /><path d="M695 819l-13 13-27 26a26 26 0 0 0-7 23l2 6 5 7c9 8 25 9 36-1l20-20 20-20z" fill="#FDCD60" /></svg>
                                    Medicines
                                </NavLink>
                                

                                <NavLink :href="route('cart.index')" :active="route().current('cart.index')">
                                    <svg width="45px" height="45px" viewBox="0 0 1024 1024" class="icon"  version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M776 239l-48-13 48-13 13-49 13 49 48 13-48 13-13 48-13-48zM824 292l-19-5 19-4 4-19 5 19 18 4-18 5-5 18-4-18z" fill="#FDCD60" /><path d="M184 562l-21-5 21-5 5-21 5 21 21 5-21 5-5 21-5-21z" fill="#FDCD60" /><path d="M212 255m-9 0a9 9 0 1 0 18 0 9 9 0 1 0-18 0Z" fill="#5546CB" /><path d="M805 754a25 25 0 1 1 25-25 25 25 0 0 1-25 25z m0-36a10 10 0 1 0 10 10 10 10 0 0 0-10-10z" fill="#5546CB" /><path d="M747.553228 751.6268l51.618795 51.618795-125.942789 125.942788-51.618795-51.618795 125.942789-125.942788Z" fill="#F97744" /><path d="M695 820l-40 40c-10 10-10 27 1 38 9 8 25 9 36-1l40-40zM232 847l-27-6 27-7 6-27 7 27 27 7-27 6-7 27-6-27z" fill="#FDCD60" /><path d="M723 677V460H308v217zM459 559h49v-49h20v49h49v20h-49v49h-20v-49h-49z" fill="#FFFFFF" /><path d="M632 765a47 47 0 0 1 66 0l11 11 5-5 9-7v-67H307v132a34 34 0 0 0 34 34h288a47 47 0 0 1 11-19l3-3-11-11a47 47 0 0 1 0-65zM723 369a34 34 0 0 0-34-34H544v-74h-58v73H341a34 34 0 0 0-34 34v71h416zM508 628h20v-49h49v-20h-49v-49h-20v49h-49v20h49v49z" fill="#FF8859" /><path d="M424 153h19v87.98h-19zM464 153h19v87.98h-19zM385 154v87h19v-88h-19zM645 240v-86h-24v88h23zM582 153h19v87.98h-19zM503 153h19v87.98h-19zM543 153h19v87.98h-19z" fill="#FDCD60" /><path d="M724 893a27 27 0 0 0 37-37zM695 790l-11-11a27 27 0 0 0-38 38l11 11z" fill="#AFBCF3" /><path d="M650 889l-2-6zM748 777a26 26 0 0 0-18 8l-48 48 13-13 37 36-20 20 55-55a26 26 0 0 0-18-44z" fill="#F97744" /><path d="M781 836a47 47 0 0 0-38-80V369a54 54 0 0 0-54-54H564v-54h80a21 21 0 0 0 21-21v-86a21 21 0 0 0-21-21H386a21 21 0 0 0-21 21v87a21 21 0 0 0 21 21h80v54H341a54 54 0 0 0-54 54v459a54 54 0 0 0 54 54h286a47 47 0 0 0 80 28l3-3a47 47 0 0 0 66-66zM621 153h23v87h-23z m-39 0h19v88h-19z m141 307v217H308V460zM543 153h19v88h-19z m-39 0h19v88h-20z m-99 88h-20v-87h19z m39 0h-20v-88h19z m20 0v-88h19v88zM307 369a34 34 0 0 1 34-34h145v-74h59v73h144a34 34 0 0 1 34 34v71H307z m333 475a47 47 0 0 0-11 19H341a34 34 0 0 1-34-34V697h416v66l-9 7-5 5-11-11a47 47 0 0 0-66 66l11 11z m7-65a27 27 0 0 1 38 0l11 11-38 38-11-11a27 27 0 0 1 0-38z m111 112a27 27 0 0 1-34 3l37-37a27 27 0 0 1-3 34z m8-69l-55 55-20 20c-10 10-27 9-36 1l-5-7-2-6a26 26 0 0 1 7-23l27-26 48-48a26 26 0 0 1 37 37z" fill="#5546CB" /><path d="M695 819l-13 13-27 26a26 26 0 0 0-7 23l2 6 5 7c9 8 25 9 36-1l20-20 20-20z" fill="#FDCD60" /></svg>
                                    Cart
                                </NavLink>
                                
                                
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <!-- Settings Dropdown -->
                            <div class="ml-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="ml-2 -mr-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('medicines.index')" :active="route().current('medicines.index')">
                            Medicines
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800 dark:text-gray-200">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')"> Profile </ResponsiveNavLink>
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
