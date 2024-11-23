<script setup>
import AuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios'; // Import axios
import Swal from 'sweetalert2'; // Import SweetAlert2

const props = defineProps({
    medicines: Array,
    categories: Array,
    errors: Object,
    success: String,
});

const showAddModal = ref(false);
const showEditModal = ref(false);
const selectedMedicine = ref(null);

const form = useForm({
    name: '',
    lprice: '',
    mprice: '',
    hprice: '',
    quantity: '',
    dosage: '',
    expdate: '',
    category_id: '',
    isExistingMedicine: false
});

// Add watcher for medicine name
watch(() => form.name, async (newName) => {
    if (newName && newName.length >= 2) {
        try {
            const response = await axios.get(route('medicines.details', { name: newName }));
            if (response.data.exists) {
                // Auto-populate fields for existing medicine
                form.isExistingMedicine = true;
                form.lprice = response.data.lprice;
                form.mprice = response.data.mprice;
                form.hprice = response.data.hprice;
                form.dosage = response.data.dosage;
            } else {
                // Clear fields for new medicine
                form.isExistingMedicine = false;
                form.lprice = '';
                form.mprice = '';
                form.hprice = '';
                form.dosage = '';
            }
        } catch (error) {
            console.error('Error fetching medicine details:', error);
        }
    }
});

const editForm = useForm({
    name: '',
    lprice: '',
    mprice: '',
    hprice: '',
    quantity: '',
    dosage: '',
    expdate: '',
    category_id: '',
});

function openAddModal() {
    showAddModal.value = true;
    form.reset();
}

function closeAddModal() {
    showAddModal.value = false;
    form.reset();
}

function openEditModal(medicine) {
    selectedMedicine.value = medicine;
    editForm.name = medicine.name;
    editForm.lprice = medicine.lprice;
    editForm.mprice = medicine.mprice;
    editForm.hprice = medicine.hprice;
    editForm.quantity = medicine.quantity;
    editForm.dosage = medicine.dosage;
    editForm.expdate = medicine.expdate;
    editForm.category_id = medicine.category_id;
    showEditModal.value = true;
}

function closeEditModal() {
    showEditModal.value = false;
    selectedMedicine.value = null;
    editForm.reset();
}

function addMedicine() {
    form.post(route('admin.inventory.store'), {
        onSuccess: () => {
            closeAddModal();
        },
    });
}

function updateMedicine() {
    editForm.put(route('admin.inventory.update', selectedMedicine.value.id), {
        onSuccess: () => {
            closeEditModal();
        },
    });
}

function confirmDelete(medicineId) {
    if (!medicineId) return;

    // Use SweetAlert for confirmation
    Swal.fire({
        title: 'Are you sure?',
        text: 'You will not be able to recover this medicine!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, cancel!',
    }).then((result) => {
        if (result.isConfirmed) {
            // Perform delete action via router
            router.delete(route('admin.medicines.destroy', medicineId), {
                onSuccess: () => {
                    // Show success notification
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The medicine has been deleted.',
                        icon: 'success',
                        confirmButtonColor: '#3085d6',
                    });

                    // Optionally redirect to the index page
                    router.get(route('admin.admininventory.index'));
                },
                onError: () => {
                    // Show error notification if delete fails
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to delete the medicine.',
                        icon: 'error',
                        confirmButtonColor: '#3085d6',
                    });
                },
            });
        } else {
            // Navigate to the medicines index page
            router.get(route('admin.admininventory'));
        }
    });
}


function toggleStatus(medicine) {
    router.put(route('admin.inventory.toggle-status', medicine.id));
}
</script>

<template>
    <Head title="Inventory Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Inventory Management</h2>
                <div class="flex gap-4">
                    <Link :href="route('admin.inventory.report')" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        View Reports
                    </Link>
                    <button @click="openAddModal"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Add Medicine
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <!-- Success Message -->
                        <div v-if="$page.props.flash.success" 
                            class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" 
                            role="alert">
                            <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                        </div>

                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Name</th>
                                    <th scope="col" class="px-6 py-3">Category</th>
                                    <th scope="col" class="px-6 py-3">Low Price</th>
                                    <th scope="col" class="px-6 py-3">Median Price</th>
                                    <th scope="col" class="px-6 py-3">Highest Price</th>
                                    <th scope="col" class="px-6 py-3">Quantity</th>
                                    <th scope="col" class="px-6 py-3">Dosage</th>
                                    <th scope="col" class="px-6 py-3">Exp Date</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="medicine in medicines" :key="medicine.id" 
                                    :class="{
                                        'bg-red-100 dark:bg-red-900': medicine.status === 'disabled',
                                        'bg-white dark:bg-gray-900': medicine.status !== 'disabled'
                                    }">
                                    <th scope="row" class="px-6 py-4 font-medium whitespace-nowrap">
                                        {{ medicine.name }}
                                    </th>
                                    <td class="px-6 py-4">
                                        {{ medicine.category ? medicine.category.name : 'Uncategorized' }}
                                    </td>
                                    <td class="px-6 py-4">₱{{ medicine.lprice }}</td>
                                    <td class="px-6 py-4">₱{{ medicine.mprice }}</td>
                                    <td class="px-6 py-4">₱{{ medicine.hprice }}</td>
                                    <td class="px-6 py-4">{{ medicine.quantity }}</td>
                                    <td class="px-6 py-4">{{ medicine.dosage }}</td>
                                    <td class="px-6 py-4">{{ medicine.expdate }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="{
                                            'text-red-600 dark:text-red-400': medicine.status === 'disabled',
                                            'text-green-600 dark:text-green-400': medicine.status !== 'disabled'
                                        }">
                                            {{ medicine.status }}
                                            <span v-if="medicine.status_reason" class="block text-sm text-gray-500">
                                                ({{ medicine.status_reason }})
                                            </span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 space-x-2">
                                        <button @click="openEditModal(medicine)"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            Edit
                                        </button>
                                        <button @click="confirmDelete(medicine.id)"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            Delete
                                        </button>
                                        <button v-if="medicine.status === 'disabled' && medicine.quantity > 0"
                                            @click="toggleStatus(medicine)"
                                            class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                            Enable
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Medicine Modal -->
        <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-medium mb-4">Add New Medicine</h3>
                <form @submit.prevent="addMedicine" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input v-model="form.name" type="text" class="w-full rounded-md" required>
                        <div v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Category</label>
                        <select v-model="form.category_id" class="w-full rounded-md">
                            <option value="">Select Category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Low Price</label>
                            <input v-model="form.lprice" type="number" step="0.01" 
                                   :class="['w-full rounded-md', {'bg-gray-100': form.isExistingMedicine}]" 
                                   :readonly="form.isExistingMedicine" required>
                            <div v-if="form.errors.lprice" class="text-red-500 text-sm">{{ form.errors.lprice }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Median Price</label>
                            <input v-model="form.mprice" type="number" step="0.01" 
                                   :class="['w-full rounded-md', {'bg-gray-100': form.isExistingMedicine}]" 
                                   :readonly="form.isExistingMedicine" required>
                            <div v-if="form.errors.mprice" class="text-red-500 text-sm">{{ form.errors.mprice }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">High Price</label>
                            <input v-model="form.hprice" type="number" step="0.01" 
                                   :class="['w-full rounded-md', {'bg-gray-100': form.isExistingMedicine}]" 
                                   :readonly="form.isExistingMedicine" required>
                            <div v-if="form.errors.hprice" class="text-red-500 text-sm">{{ form.errors.hprice }}</div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Quantity</label>
                        <input v-model="form.quantity" type="number" class="w-full rounded-md" required>
                        <div v-if="form.errors.quantity" class="text-red-500 text-sm">{{ form.errors.quantity }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Dosage</label>
                        <input v-model="form.dosage" type="text" 
                               :class="['w-full rounded-md', {'bg-gray-100': form.isExistingMedicine}]" 
                               :readonly="form.isExistingMedicine" required>
                        <div v-if="form.errors.dosage" class="text-red-500 text-sm">{{ form.errors.dosage }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Expiry Date</label>
                        <input v-model="form.expdate" type="date" class="w-full rounded-md" required>
                        <div v-if="form.errors.expdate" class="text-red-500 text-sm">{{ form.errors.expdate }}</div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="closeAddModal"
                            class="px-4 py-2 border rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Add Medicine
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Medicine Modal -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-medium mb-4">Edit Medicine</h3>
                <form @submit.prevent="updateMedicine" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input v-model="editForm.name" type="text" class="w-full rounded-md" required>
                        <div v-if="editForm.errors.name" class="text-red-500 text-sm">{{ editForm.errors.name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Category</label>
                        <select v-model="editForm.category_id" class="w-full rounded-md">
                            <option value="">Select Category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Low Price</label>
                            <input v-model="editForm.lprice" type="number" step="0.01" class="w-full rounded-md" required>
                            <div v-if="editForm.errors.lprice" class="text-red-500 text-sm">{{ editForm.errors.lprice }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Median Price</label>
                            <input v-model="editForm.mprice" type="number" step="0.01" class="w-full rounded-md" required>
                            <div v-if="editForm.errors.mprice" class="text-red-500 text-sm">{{ editForm.errors.mprice }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">High Price</label>
                            <input v-model="editForm.hprice" type="number" step="0.01" class="w-full rounded-md" required>
                            <div v-if="editForm.errors.hprice" class="text-red-500 text-sm">{{ editForm.errors.hprice }}</div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Quantity</label>
                        <input v-model="editForm.quantity" type="number" class="w-full rounded-md" required>
                        <div v-if="editForm.errors.quantity" class="text-red-500 text-sm">{{ editForm.errors.quantity }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Dosage</label>
                        <input v-model="editForm.dosage" type="text" class="w-full rounded-md" required>
                        <div v-if="editForm.errors.dosage" class="text-red-500 text-sm">{{ editForm.errors.dosage }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Expiry Date</label>
                        <input v-model="editForm.expdate" type="date" class="w-full rounded-md" required>
                        <div v-if="editForm.errors.expdate" class="text-red-500 text-sm">{{ editForm.errors.expdate }}</div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="closeEditModal"
                            class="px-4 py-2 border rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Update Medicine
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
