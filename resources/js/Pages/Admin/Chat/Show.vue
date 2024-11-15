<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminAuthenticatedLayout from '@/Layouts/AdminAuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    chats: Array
});

const form = useForm({
    message: ''
});

const chatContainer = ref(null);

const scrollToBottom = () => {
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const sendMessage = () => {
    if (!form.message.trim()) return;
    
    form.post(route('admin.chat.store', props.user.id), {
        onSuccess: () => {
            form.reset();
            nextTick(() => {
                scrollToBottom();
            });
        }
    });
};

onMounted(() => {
    scrollToBottom();
});
</script>

<template>
    <AdminAuthenticatedLayout>
        <Head :title="`Chat with ${user.name}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-2xl font-semibold">Chat with {{ user.name }}</h2>
                            <Link 
                                :href="route('admin.chat.index')"
                                class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
                            >
                                Back to Chats
                            </Link>
                        </div>
                        
                        <!-- Chat Messages -->
                        <div ref="chatContainer" class="space-y-4 mb-4 h-[calc(100vh-400px)] overflow-y-auto p-4 bg-gray-50 rounded-lg">
                            <div v-if="chats.length === 0" class="text-center text-gray-500 py-4">
                                No messages yet
                            </div>
                            <div v-for="chat in chats" :key="chat.id" 
                                 :class="[
                                     'p-4 rounded-lg max-w-[80%]',
                                     chat.is_admin ? 'ml-auto bg-blue-100' : 'bg-white border border-gray-200'
                                 ]">
                                <p class="whitespace-pre-wrap">{{ chat.message }}</p>
                                <span class="text-xs text-gray-500 block mt-1">
                                    {{ new Date(chat.created_at).toLocaleString() }}
                                </span>
                            </div>
                        </div>

                        <!-- Message Input -->
                        <form @submit.prevent="sendMessage" class="flex gap-2">
                            <input
                                v-model="form.message"
                                type="text"
                                placeholder="Type your message..."
                                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                @keyup.enter="sendMessage"
                            >
                            <button
                                type="submit"
                                class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50"
                                :disabled="form.processing || !form.message.trim()"
                            >
                                Send
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminAuthenticatedLayout>
</template> 