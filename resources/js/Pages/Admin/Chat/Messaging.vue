<script setup>
import AuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';



 /*props: {
    medicine: {
      type: Object,
      required: true
    }
}*/


</script>

<template>
    <Head title="Chat" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Chat Index</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="w-full px-6 py-3">
                                        Name
                                    </th>
                                    <!--<th scope="col" class="px-6 py-3">
                                        Price
                                    </th>-->
                                    <th scope="col" class="px-6 py-3">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tr>
                                <th scope="col" class="w-full px-6 py-3">
                                    Name
                                </th>
                                
                                <th scope="col" class="px-6 py-3">
                                    Actions
                                </th>
                            </tr>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
