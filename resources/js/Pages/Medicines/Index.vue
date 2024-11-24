<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';

const props = defineProps({
    medicines: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    medicine_id: null,
    name: '',
    lprice: 0,
    mprice: 0,
    hprice: 0,
    quantity: 1,
    dosage: '',
    expdate: '',
});

function handleAddToCart(medicine) {
    if (!medicine) return;

    Swal.fire({
        title: 'Add to Cart',
        text: `Are you sure you want to add ${medicine.name || 'this medicine'} (Quantity: ${medicine.quantity || 1}) to your cart?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Add to Cart',
        confirmButtonColor: '#28a745',
        cancelButtonColor: 'gray',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Set the form data based on the selected medicine
            form.medicine_id = medicine.id;
            form.name = medicine.name;
            form.lprice = medicine.lprice;
            form.mprice = medicine.mprice;
            form.hprice = medicine.hprice;
            form.quantity = medicine.quantity;
            form.dosage = medicine.dosage;
            form.expdate = medicine.expdate;

            form.post(route('cart.store'), {
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Added!',
                        text: 'Medicine added to your cart successfully.',
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end',
                        background: '#B5C99A',
                        color: '#000'
                    });
                },
            });
        }
    });
}

const showingNavigationDropdown = ref(false);

const searchQuery = ref('');

const performSearch = debounce((query) => {
    router.get(route('medicines.index'),
        { search: query },
        { preserveState: true, preserveScroll: true }
    );
}, 300);

watch(searchQuery, (newValue) => {
    performSearch(newValue);
});
</script>


<template>
    <AuthenticatedLayout>

        <Head title="Medicines" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">List of Medicines</h2>
        </template>
        <main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="mb-6">
                        <div class="relative">
                            <input v-model="searchQuery" type="text" placeholder="Search medicines by name or dosage..."
                                class="w-4/12 px-10 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600" />
                            <div class="absolute inset-y-0 left-3 flex items-center">
                                <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Name</th>
                                    <th scope="col" class="px-6 py-3">Category</th>
                                    <th scope="col" class="px-6 py-3">Low Price</th>
                                    <th scope="col" class="px-6 py-3">Median Price</th>
                                    <th scope="col" class="px-6 py-3">Highest Price</th>
                                    <th scope="col" class="px-6 py-3">Quantity</th>
                                    <th scope="col" class="px-6 py-3">Dosage</th>
                                    <th scope="col" class="px-6 py-3">Exp Date</th>
                                    <th scope="col" class="px-6 py-3">Reserve</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!medicines || medicines.length === 0">
                                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                        No medicines found
                                    </td>
                                </tr>
                                <tr v-for="medicine in medicines" :key="medicine?.id || index"
                                    class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                    <th scope="row"
                                        class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ medicine?.name || 'N/A' }}
                                    </th>
                                    <td class="px-6 py-4">{{ medicine?.category?.name || 'N/A' }}</td>
                                    <td class="px-6 py-4">₱{{ medicine?.lprice || '0' }}</td>
                                    <td class="px-6 py-4">₱{{ medicine?.mprice || '0' }}</td>
                                    <td class="px-6 py-4">₱{{ medicine?.hprice || '0' }}</td>
                                    <td class="px-6 py-4">
                                        <!-- Quantity Selector -->
                                        <input type="number" v-model.number="medicine.quantity" min="1"
                                            :max="medicine?.quantity || 1"
                                            class="w-20 p-1 border border-gray-300 rounded dark:bg-gray-800 dark:text-white" />
                                    </td>
                                    <td class="px-6 py-4">{{ medicine?.dosage || 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ medicine?.expdate || 'N/A' }}</td>
                                    <td class="flex px-6 py-4">
                                        <Link v-if="medicine"
                                            class="px-4 py-2 ml-4 bg-[#28a745] hover:bg-[#D2E0BE] text-white rounded"
                                            @click.prevent="handleAddToCart(medicine)" href="#">
                                        Add
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
