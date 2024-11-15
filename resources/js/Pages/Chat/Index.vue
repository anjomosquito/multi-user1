<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    chats: Array,
    unreadCount: Number,
    hasActiveChat: Boolean
});

const form = useForm({
    message: ''
});

const chatContainer = ref(null);
const showChatInput = ref(props.hasActiveChat);

const scrollToBottom = () => {
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const startNewChat = () => {
    showChatInput.value = true;
    nextTick(() => {
        const messageInput = document.querySelector('input[name="message"]');
        if (messageInput) {
            messageInput.focus();
        }
    });
};

const sendMessage = () => {
    if (!form.message.trim()) return;
    
    form.post(route('chat.store'), {
        onSuccess: () => {
            form.reset();
            showChatInput.value = true;
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
    <AuthenticatedLayout>
        <Head title="Chat Support" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-2xl font-semibold mb-4">
                            Chat Support
                            <span v-if="unreadCount > 0" 
                                  class="ml-2 bg-red-500 text-white text-sm px-2 py-1 rounded-full">
                                {{ unreadCount }} new
                            </span>
                        </h2>
                        
                        <!-- Chat Messages -->
                        <div ref="chatContainer" class="space-y-4 mb-4 h-[calc(100vh-400px)] overflow-y-auto p-4 bg-gray-50 rounded-lg">
                            <!-- Welcome Message when no chats -->
                            <div v-if="!hasActiveChat && !showChatInput" class="text-center py-10">
                                <h3 class="text-xl font-medium text-gray-900 mb-4">Welcome to Chat Support</h3>
                                <p class="text-gray-600 mb-6">Our support team is here to help you. Start a conversation with us!</p>
                                <button
                                    @click="startNewChat"
                                    class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                                >
                                    Start New Conversation
                                </button>
                            </div>

                            <!-- Chat Messages -->
                            <template v-if="hasActiveChat || showChatInput">
                                <div v-for="chat in chats" :key="chat.id" 
                                     :class="[
                                         'p-4 rounded-lg max-w-[80%]',
                                         chat.is_admin ? 'bg-blue-100' : 'ml-auto bg-white border border-gray-200'
                                     ]">
                                    <p class="whitespace-pre-wrap">{{ chat.message }}</p>
                                    <span class="text-xs text-gray-500 block mt-1">
                                        {{ new Date(chat.created_at).toLocaleString() }}
                                    </span>
                                </div>
                            </template>
                        </div>

                        <!-- Message Input -->
                        <form v-if="showChatInput" @submit.prevent="sendMessage" class="flex gap-2">
                            <input
                                v-model="form.message"
                                type="text"
                                name="message"
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
    </AuthenticatedLayout>
</template> 