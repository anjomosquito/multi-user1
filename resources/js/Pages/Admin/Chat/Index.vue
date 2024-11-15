<script setup>
import { ref, computed } from 'vue';
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    users: Array,
    totalUnread: Number
});

const searchQuery = ref('');
const filteredUsers = computed(() => {
    if (!searchQuery.value) return props.users;
    const query = searchQuery.value.toLowerCase();
    return props.users.filter(user => 
        user.name.toLowerCase().includes(query) || 
        user.email.toLowerCase().includes(query)
    );
});
</script>

<template>
    <AdminAuthenticatedLayout>
        <Head title="Chat Management" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-semibold">
                                Chat Management
                                <span v-if="totalUnread > 0" 
                                      class="ml-2 bg-red-500 text-white text-sm px-2 py-1 rounded-full">
                                    {{ totalUnread }} new
                                </span>
                            </h2>
                            <div class="w-1/3">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search users..."
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div v-if="filteredUsers.length === 0" class="text-center py-8">
                                <div class="text-gray-400 mb-4">
                                    <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No conversations found</h3>
                                <p class="text-gray-500">
                                    {{ searchQuery ? 'No users match your search criteria.' : 'Wait for users to start conversations.' }}
                                </p>
                            </div>

                            <div v-for="user in filteredUsers" :key="user.id" 
                                 class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-medium">{{ user.name }}</h3>
                                        <span v-if="user.unread_count > 0" 
                                              class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                                            {{ user.unread_count }} new
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        {{ user.email }}
                                    </p>
                                    <div class="text-sm text-gray-500 mt-1">
                                        <span v-if="user.chats && user.chats.length > 0">
                                            Last message: {{ user.chats[0].message }}
                                            <br>
                                            {{ new Date(user.chats[0].created_at).toLocaleString() }}
                                        </span>
                                        <span v-else class="text-gray-400 italic">
                                            No messages yet
                                        </span>
                                    </div>
                                </div>
                                <Link :href="route('admin.chat.show', user.id)"
                                      class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                                    {{ user.chats && user.chats.length > 0 ? 'View Chat' : 'Start Chat' }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminAuthenticatedLayout>
</template> 