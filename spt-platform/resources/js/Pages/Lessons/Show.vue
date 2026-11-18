<!-- resources/js/Pages/Lessons/Show.vue -->
<script setup>
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import LessonContentRenderer from '@/Components/LessonContentRenderer.vue';
import { computed, ref, nextTick, onUnmounted, watch } from 'vue';
import axios from 'axios';
import { Bot, Send, X, Maximize2, Minimize2 } from 'lucide-vue-next';
import { marked } from 'marked';
import DOMPurify from 'dompurify';

const props = defineProps({
    module: Object,
    lesson: Object,
    sidebar: Array,
    currentIndex: Number,
    progressPercent: Number,
    nextLesson: Number,
    previousLesson: Number,
});

const question = ref('');
const messages = ref([]);
const asking = ref(false);
const assistantOpen = ref(true);
const assistantExpanded = ref(sessionStorage.getItem('pnp-chat-expanded') === 'true');
const assistantError = ref('');
const loadingMessage = ref('Reading your unlocked lesson material...');
const chatMessages = ref(null);
const messageInput = ref(null);
let loadingTimer;
const isComposing = ref(false);

marked.setOptions({
    breaks: true,
    gfm: true,
});

const renderMarkdown = (text) => DOMPurify.sanitize(marked.parse(text || '', { async: false }));
const canSend = computed(() => question.value.trim().length > 0 && !asking.value);

watch(assistantExpanded, (expanded) => {
    sessionStorage.setItem('pnp-chat-expanded', expanded ? 'true' : 'false');
});

const resizeMessageInput = () => {
    const input = messageInput.value;
    if (!input) return;
    input.style.height = 'auto';
    const panel = input.closest('.assistant-panel');
    const panelLimit = panel ? Math.min(200, panel.clientHeight * 0.4) : 200;
    const maxHeight = Math.max(56, panelLimit);
    input.style.height = `${Math.min(input.scrollHeight, maxHeight)}px`;
    input.style.overflowY = input.scrollHeight > maxHeight ? 'auto' : 'hidden';
};

const resetMessageInput = () => {
    question.value = '';
    nextTick(() => {
        if (!messageInput.value) return;
        messageInput.value.style.height = '52px';
        messageInput.value.style.overflowY = 'hidden';
        messageInput.value.focus();
    });
};

const toggleExpanded = async () => {
    const scrollTop = chatMessages.value?.scrollTop ?? 0;
    assistantExpanded.value = !assistantExpanded.value;
    await nextTick();
    if (chatMessages.value) chatMessages.value.scrollTop = scrollTop;
};

const loadingMessages = [
    'Reading your unlocked lesson material...',
    'Checking the module scope...',
    'Preparing a focused explanation...',
];

const startLoadingMessages = () => {
    let index = 0;
    loadingMessage.value = loadingMessages[index];
    loadingTimer = window.setInterval(() => {
        index = (index + 1) % loadingMessages.length;
        loadingMessage.value = loadingMessages[index];
    }, 1800);
};

const stopLoadingMessages = () => {
    window.clearInterval(loadingTimer);
    loadingTimer = undefined;
};

const scrollChat = async () => {
    await nextTick();
    if (chatMessages.value) {
        chatMessages.value.scrollTo({ top: chatMessages.value.scrollHeight, behavior: 'smooth' });
    }
};

const handleMessageKeydown = (event) => {
    if (isComposing.value || event.isComposing || event.keyCode === 229) return;
    if (event.key === 'Enter' && event.shiftKey) return;

    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();

        const input = event.currentTarget;
        const start = input.selectionStart;
        const end = input.selectionEnd;
        const text = question.value;
        const lineStart = text.lastIndexOf('\n', start - 1);
        const currentLineStart = lineStart === -1 ? 0 : lineStart + 1;
        const currentLine = text.slice(currentLineStart, start);

        const bulletMatch = currentLine.match(/^(\s*)([-*•])\s*(.*)$/);
        if (bulletMatch) {
            const indent = bulletMatch[1];
            const bullet = bulletMatch[2] === '*' ? '-' : bulletMatch[2];
            if (bulletMatch[3].trim() === '') {
                const removalStart = currentLineStart;
                const removalEnd = start;
                question.value = text.slice(0, removalStart) + text.slice(removalEnd);
                nextTick(() => {
                    input.selectionStart = input.selectionEnd = removalStart;
                    resizeMessageInput();
                });
                return;
            }
            const insertion = `\n${indent}${bullet} `;
            question.value = text.slice(0, start) + insertion + text.slice(end);
            nextTick(() => {
                input.selectionStart = input.selectionEnd = start + insertion.length;
                resizeMessageInput();
            });
            return;
        }

        const numberMatch = currentLine.match(/^(\s*)(\d+)\.\s*(.*)$/);
        if (numberMatch) {
            const indent = numberMatch[1];
            if (numberMatch[3].trim() === '') {
                question.value = text.slice(0, currentLineStart) + text.slice(start);
                nextTick(() => {
                    input.selectionStart = input.selectionEnd = currentLineStart;
                    resizeMessageInput();
                });
                return;
            }
            const nextNumber = Number(numberMatch[2]) + 1;
            const insertion = `\n${indent}${nextNumber}. `;
            question.value = text.slice(0, start) + insertion + text.slice(end);
            nextTick(() => {
                input.selectionStart = input.selectionEnd = start + insertion.length;
                resizeMessageInput();
            });
            return;
        }

        if (canSend.value) askAssistant();
        return;
    }

    if (event.key === 'Tab') {
        // Optional indentation support is intentionally left out to preserve
        // native textarea Tab behavior for accessibility.
        return;
    }
};

const askAssistant = async () => {
    const text = question.value.trim();
    if (!text || asking.value) return;

    messages.value.push({ role: 'user', content: text });
    resetMessageInput();
    assistantError.value = '';
    asking.value = true;
    startLoadingMessages();
    await scrollChat();

    try {
        const response = await axios.post(`/lessons/${props.lesson.id}/assistant`, { question: text });
        stopLoadingMessages();
        messages.value.push({ role: 'assistant', content: response.data.answer });
        await scrollChat();
    } catch (error) {
        assistantError.value = error.response?.data?.message ?? 'The lesson assistant is temporarily unavailable.';
    } finally {
        asking.value = false;
        stopLoadingMessages();
        await nextTick();
    }
};

onUnmounted(stopLoadingMessages);
</script>

<template>
    <Head :title="lesson.title" />
    <TraineeLayout>
        <div class="flex gap-6">

            <!-- Progress sidebar -->
            <aside class="w-64 shrink-0">
                <div class="bg-blue-600 text-white rounded-xl p-4 mb-3">
                    <p class="font-bold text-sm">{{ module.title }} Progress</p>
                    <div class="w-full bg-white/20 rounded-full h-1.5 mt-2">
                        <div class="bg-white h-1.5 rounded-full" :style="{ width: progressPercent + '%' }"></div>
                    </div>
                    <p class="text-xs mt-1">{{ progressPercent }}% COMPLETE</p>
                </div>

                <div class="space-y-1">
                    <Link v-for="(item, i) in sidebar" :key="item.id"
                        :href="item.unlocked ? `/lessons/${item.id}` : '#'"
                        :class="[
                            i === currentIndex ? 'bg-blue-600 text-white' : item.unlocked ? 'text-gray-700 hover:bg-gray-100' : 'text-gray-300 cursor-not-allowed',
                        ]"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium">
                        <span v-if="item.passed">✅</span>
                        <span v-else-if="item.unlocked">⭕</span>
                        <span v-else>🔒</span>
                        {{ item.title }}
                    </Link>
                </div>
            </aside>

            <div class="flex-1 min-w-0 flex flex-col lg:flex-row gap-5 items-start">
                <!-- Main content -->
                <div class="flex-1 min-w-0 space-y-4">
                    <div class="bg-white rounded-xl border p-8">
                        <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2 py-1 rounded-full">
                            Module {{ module.id }} of — Lesson {{ currentIndex + 1 }}
                        </span>
                        <h1 class="text-2xl font-bold mt-3 mb-6">{{ lesson.title }}</h1>

                        <LessonContentRenderer :blocks="lesson.content" />

                        <div class="mt-6 pt-4 border-t bg-blue-50 -mx-8 -mb-8 px-8 py-4 rounded-b-xl flex justify-between items-center">
                            <div>
                                <p class="text-sm font-semibold flex items-center gap-1">❓ Module Knowledge Check</p>
                                <p class="text-xs text-gray-500">Have you reviewed the material? Take the quiz to unlock the next lesson.</p>
                            </div>
                            <Link :href="`/lessons/${lesson.id}/quiz`" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                Take Quiz →
                            </Link>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <Link v-if="previousLesson" :href="`/lessons/${previousLesson}`" class="border rounded-lg px-4 py-2 text-sm font-medium text-gray-600">← Previous Lesson</Link>
                        <span v-else class="border rounded-lg px-4 py-2 text-sm font-medium text-gray-300 cursor-not-allowed">← Previous Lesson</span>

                        <p class="text-sm text-gray-400">Lesson {{ currentIndex + 1 }} of {{ sidebar.length }}</p>

                        <span class="border rounded-lg px-4 py-2 text-sm font-medium text-gray-300">🔒 Pass Quiz to Unlock</span>
                    </div>
                </div>

                <!-- Lesson assistant side panel -->
                <aside :class="[
                    assistantOpen ? (assistantExpanded ? 'w-[65vw] h-[86vh] max-h-[86vh]' : 'w-80') : 'w-12',
                    assistantExpanded ? 'assistant-expanded' : 'sticky top-6'
                ]" class="shrink-0 assistant-panel transition-[width,height] duration-300">
                    <button v-if="!assistantOpen" @click="assistantOpen = true"
                        class="w-12 min-h-32 bg-blue-600 text-white rounded-xl shadow-lg shadow-blue-200/50 flex flex-col items-center justify-center gap-2 hover:bg-blue-700 transition-colors"
                        title="Open Lesson Assistant" aria-label="Open Lesson Assistant">
                        <Bot :size="19" />
                        <span class="text-[10px] font-semibold tracking-wider [writing-mode:vertical-rl]">AI ASSISTANT</span>
                    </button>

                    <section v-if="assistantOpen" :class="assistantExpanded ? 'h-full flex flex-col' : ''"
                        class="bg-white rounded-xl border border-blue-100 shadow-lg shadow-blue-100/40 overflow-hidden">
                        <div class="assistant-shimmer relative px-5 py-4 text-white">
                            <div class="absolute -right-6 -top-8 w-24 h-24 rounded-full bg-white/10 blur-xl"></div>
                            <div class="relative flex items-center gap-3">
                                <div class="relative w-10 h-10 rounded-xl bg-white/15 border border-white/25 flex items-center justify-center">
                                    <span class="absolute inset-0 rounded-xl border border-white/30 assistant-ring"></span>
                                    <Bot :size="20" />
                                </div>
                                <div>
                                    <h2 class="font-semibold text-sm">Lesson Assistant</h2>
                                    <p class="text-[11px] text-blue-100">Your guided learning companion</p>
                                </div>
                                <button @click="toggleExpanded" class="ml-auto w-7 h-7 rounded-lg hover:bg-white/15 flex items-center justify-center transition-colors"
                                    :title="assistantExpanded ? 'Collapse Lesson Assistant' : 'Expand Lesson Assistant'"
                                    :aria-label="assistantExpanded ? 'Collapse Lesson Assistant' : 'Expand Lesson Assistant'">
                                    <Minimize2 v-if="assistantExpanded" :size="15" />
                                    <Maximize2 v-else :size="15" />
                                </button>
                                <button @click="assistantOpen = false" class="w-7 h-7 rounded-lg hover:bg-white/15 flex items-center justify-center transition-colors"
                                    title="Minimize Lesson Assistant" aria-label="Minimize Lesson Assistant">
                                    <X :size="15" />
                                </button>
                            </div>
                            <div class="relative flex items-center gap-1.5 mt-3 text-[10px] uppercase tracking-wider text-blue-100">
                                <span :class="asking ? 'bg-amber-300 animate-pulse' : 'bg-emerald-300'" class="w-1.5 h-1.5 rounded-full"></span>
                                {{ asking ? 'Working on your answer' : 'Ready to help' }}
                            </div>
                        </div>

                        <div ref="chatMessages" :class="assistantExpanded ? 'flex-1' : 'h-[420px]'"
                            class="min-h-0 overflow-y-auto p-4 space-y-3 bg-white">
                            <div class="flex gap-2 assistant-message">
                                <div class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                                    <Bot :size="13" />
                                </div>
                                <div class="px-1 py-1 text-sm text-gray-600"
                                    v-html="renderMarkdown('Ask me about this lesson or any material you have already unlocked.')"></div>
                            </div>

                            <TransitionGroup name="message" tag="div" class="space-y-3">
                                <div v-for="(message, index) in messages" :key="index"
                                    :class="message.role === 'user'
                                        ? 'bg-blue-50 text-slate-900 ml-8 rounded-2xl rounded-tr-sm'
                                        : 'text-slate-800 mr-4 px-1'"
                                    class="px-3 py-2 text-sm chat-markdown">
                                    <div v-html="renderMarkdown(message.content)" :class="message.role === 'user' ? 'user-markdown' : ''"></div>
                                </div>
                            </TransitionGroup>

                            <div v-if="asking" class="flex items-center gap-2 bg-blue-50 text-blue-700 rounded-2xl rounded-tl-sm px-3 py-2 text-[11px] mr-4" aria-live="polite">
                                <span class="flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-bounce"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-bounce [animation-delay:120ms]"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-bounce [animation-delay:240ms]"></span>
                                </span>
                                <span>{{ loadingMessage }}</span>
                            </div>
                        </div>

                        <div class="p-3 bg-white">
                            <p v-if="assistantError" class="text-[11px] text-red-600 mb-2">{{ assistantError }}</p>
                            <form @submit.prevent="askAssistant" class="composer">
                                <textarea ref="messageInput" v-model="question" rows="1" maxlength="1000"
                                    placeholder="Ask about this lesson..."
                                    class="chat-textarea"
                                    :disabled="asking"
                                    @input="resizeMessageInput"
                                    @keydown="handleMessageKeydown"
                                    @compositionstart="isComposing = true"
                                    @compositionend="isComposing = false"
                                    aria-label="Message the lesson assistant"></textarea>
                                <div class="composer-toolbar">
                                    <button type="submit" :disabled="!canSend"
                                        aria-label="Send message"
                                        class="send-button">
                                        <Send :size="16" />
                                    </button>
                                </div>
                            </form>
                            <p class="composer-hint">Limited to your current and unlocked lessons.</p>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </TraineeLayout>
</template>

<style scoped>
.assistant-panel {
    animation: assistant-rise 420ms ease-out both;
}

.assistant-expanded {
    position: fixed;
    z-index: 50;
    right: 1.5rem;
    top: 1.5rem;
    bottom: 1.5rem;
}

.assistant-shimmer {
    background: linear-gradient(120deg, #2563eb, #4f46e5, #2563eb);
    background-size: 220% 220%;
    animation: assistant-shimmer 8s ease-in-out infinite;
}

.assistant-ring {
    animation: assistant-ring 2.8s ease-out infinite;
}

.assistant-message {
    animation: assistant-message-in 320ms ease-out both;
}

.chat-textarea {
    display: block;
    width: 100%;
    height: 52px;
    min-height: 52px;
    max-height: 200px;
    box-sizing: border-box;
    padding: 14px 16px 8px;
    border: 0;
    outline: 0;
    resize: none;
    background: transparent;
    color: #111827;
    font-size: 15px;
    line-height: 1.5;
    white-space: pre-wrap;
    overflow-x: hidden;
    overflow-y: hidden;
    scrollbar-width: thin;
}

.chat-textarea::placeholder {
    color: #9ca3af;
}

.composer {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    background: #ffffff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    transition: border-color 160ms ease, box-shadow 160ms ease;
    overflow: hidden;
}

.composer:focus-within {
    border-color: #c7d7f7;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
}

.composer-toolbar {
    display: flex;
    justify-content: flex-end;
    padding: 2px 8px 8px;
}

.send-button {
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 10px;
    background: #2563eb;
    color: #ffffff;
    transition: background-color 160ms ease, transform 160ms ease, opacity 160ms ease;
}

.send-button:not(:disabled):hover {
    background: #1d4ed8;
}

.send-button:not(:disabled):active {
    transform: scale(0.96);
}

.send-button:disabled {
    background: #e5e7eb;
    color: #9ca3af;
    cursor: not-allowed;
}

.send-button:focus-visible,
.chat-textarea:focus-visible {
    outline: 2px solid #93b4ee;
    outline-offset: 2px;
}

.composer-hint {
    margin-top: 8px;
    color: #9ca3af;
    font-size: 12px;
    line-height: 1.25rem;
}

.chat-markdown :deep(p) {
    margin: 0 0 0.45rem;
}

.chat-markdown :deep(p:last-child) {
    margin-bottom: 0;
}

.chat-markdown :deep(ul),
.chat-markdown :deep(ol) {
    margin: 0.35rem 0;
    padding-left: 1.25rem;
}

.chat-markdown :deep(ul) {
    list-style-type: disc;
}

.chat-markdown :deep(ol) {
    list-style-type: decimal;
}

.chat-markdown :deep(li) {
    margin: 0.15rem 0;
}

.chat-markdown :deep(code) {
    border-radius: 0.25rem;
    background: rgba(15, 23, 42, 0.08);
    padding: 0.1rem 0.25rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
}

.chat-markdown :deep(pre) {
    max-width: 100%;
    overflow-x: auto;
    border-radius: 0.5rem;
    background: #0f172a;
    color: #f8fafc;
    padding: 0.75rem;
    margin: 0.5rem 0;
}

.chat-markdown :deep(pre code) {
    background: transparent;
    padding: 0;
}

.user-markdown :deep(code) {
    background: rgba(255, 255, 255, 0.16);
}

.message-enter-active {
    transition: opacity 220ms ease, transform 220ms ease;
}

.message-enter-from {
    opacity: 0;
    transform: translateY(8px) scale(0.98);
}

@keyframes assistant-rise {
    from {
        opacity: 0;
        transform: translateX(12px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes assistant-shimmer {
    0%, 100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

@keyframes assistant-ring {
    0%, 100% {
        opacity: 0.3;
        transform: scale(1);
    }
    50% {
        opacity: 0;
        transform: scale(1.25);
    }
}

@keyframes assistant-message-in {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 1024px) {
    .assistant-panel {
        position: static;
        width: 100%;
    }

    .chat-textarea {
        font-size: 16px;
    }

    .assistant-expanded {
        position: fixed;
        inset: 0;
        width: 100%;
        height: 100%;
        max-height: none;
        border-radius: 0;
    }

    .assistant-expanded section {
        border: 0;
        border-radius: 0;
    }
}
</style>