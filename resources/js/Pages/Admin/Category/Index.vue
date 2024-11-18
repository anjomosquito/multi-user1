<template>
    <Head title="Medicine Categories" />

    <AdminAuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Medicine Categories</h2>
                <button @click="openAddModal" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Add Category
                </button>
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
                                    <th scope="col" class="px-6 py-3">Description</th>
                                    <th scope="col" class="px-6 py-3">Medicines Count</th>
                                    <th scope="col" class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="category in categories" :key="category.id" 
                                    class="bg-white dark:bg-gray-900">
                                    <th scope="row" class="px-6 py-4 font-medium whitespace-nowrap">
                                        {{ category.name }}
                                    </th>
                                    <td class="px-6 py-4">{{ category.description }}</td>
                                    <td class="px-6 py-4">{{ category.medicines_count }}</td>
                                    <td class="px-6 py-4 space-x-2">
                                        <button @click="openEditModal(category)"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            Edit
                                        </button>
                                        <button @click="confirmDelete(category.id)"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Category Modal -->
        <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-medium mb-4">Add New Category</h3>
                <form @submit.prevent="addCategory" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input v-model="form.name" type="text" class="w-full rounded-md" required>
                        <div v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea v-model="form.description" class="w-full rounded-md" rows="3"></textarea>
                        <div v-if="form.errors.description" class="text-red-500 text-sm">{{ form.errors.description }}</div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="closeAddModal"
                            class="px-4 py-2 border rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Add Category
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-medium mb-4">Edit Category</h3>
                <form @submit.prevent="updateCategory" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input v-model="editForm.name" type="text" class="w-full rounded-md" required>
                        <div v-if="editForm.errors.name" class="text-red-500 text-sm">{{ editForm.errors.name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea v-model="editForm.description" class="w-full rounded-md" rows="3"></textarea>
                        <div v-if="editForm.errors.description" class="text-red-500 text-sm">{{ editForm.errors.description }}</div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="closeEditModal"
                            class="px-4 py-2 border rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminAuthenticatedLayout>
</template>

<script setup>
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Array,
    errors: Object,
    success: String,
});

const showAddModal = ref(false);
const showEditModal = ref(false);
const selectedCategory = ref(null);

const form = useForm({
    name: '',
    description: '',
});

const editForm = useForm({
    name: '',
    description: '',
});

function openAddModal() {
    showAddModal.value = true;
    form.reset();
}

function closeAddModal() {
    showAddModal.value = false;
    form.reset();
}

function openEditModal(category) {
    selectedCategory.value = category;
    editForm.name = category.name;
    editForm.description = category.description;
    showEditModal.value = true;
}

function closeEditModal() {
    showEditModal.value = false;
    selectedCategory.value = null;
    editForm.reset();
}

function addCategory() {
    form.post(route('admin.categories.store'), {
        onSuccess: () => {
            closeAddModal();
        },
    });
}

function updateCategory() {
    editForm.put(route('admin.categories.update', selectedCategory.value.id), {
        onSuccess: () => {
            closeEditModal();
        },
    });
}

function confirmDelete(categoryId) {
    if (confirm('Are you sure you want to delete this category? Medicines in this category will become uncategorized.')) {
        router.delete(route('admin.categories.destroy', categoryId));
    }
}
</script>
