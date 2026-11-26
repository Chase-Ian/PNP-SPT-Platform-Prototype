<!-- Dropdown field that draws its own option list instead of the browser's
     built-in one, so the list always opens directly under (or above) the
     field, stays inside the window, isn't cut off by cards or scrolling
     tables, and looks like the app's other menus.
     Keyboard: Arrow keys, Home/End and Page Up/Down move; Enter or Space
     chooses; Escape closes; typing letters jumps to a matching option. -->
<script>
let nextId = 0;
</script>

<script setup>
import { computed, nextTick, onBeforeUnmount, ref } from 'vue';
import { Check, ChevronDown } from 'lucide-vue-next';

const model = defineModel({ default: '' });

const props = defineProps({
    // Strings, or { value, label } objects.
    options: { type: Array, required: true },
    // The field's name for screen readers, e.g. "Rank" or "Filter by status".
    label: { type: String, required: true },
    // Put on the button so a visible <label for="..."> points at it.
    id: String,
    placeholder: { type: String, default: 'Select…' },
    disabled: Boolean,
    invalid: Boolean,
    describedby: String,
    // md = form field, sm = compact (editor blocks)
    size: { type: String, default: 'md' },
});

const uid = `select-${++nextId}`;
const listId = `${uid}-list`;

const items = computed(() => props.options.map((o) => (o !== null && typeof o === 'object'
    ? { value: o.value, label: o.label ?? String(o.value) }
    : { value: o, label: String(o) })));
const selectedIndex = computed(() => items.value.findIndex((item) => item.value === model.value));
const selectedLabel = computed(() => items.value[selectedIndex.value]?.label ?? props.placeholder);

const open = ref(false);
const active = ref(-1);
const trigger = ref(null);
const list = ref(null);
const position = ref({ top: 0, left: 0, width: 0, maxHeight: 288 });

const place = () => {
    const rect = trigger.value.getBoundingClientRect();
    const width = Math.max(rect.width, 180);
    const below = window.innerHeight - rect.bottom - 12;
    const above = rect.top - 12;
    const wanted = Math.min(288, list.value?.scrollHeight ?? 288);
    const openUp = below < Math.min(wanted, 200) && above > below;
    const maxHeight = Math.max(120, Math.min(288, openUp ? above : below));
    const height = Math.min(wanted, maxHeight);
    position.value = {
        top: openUp ? rect.top - height - 4 : rect.bottom + 4,
        left: Math.min(Math.max(8, rect.left), window.innerWidth - width - 8),
        width,
        maxHeight,
    };
};

// Scrolls only the list (never the page, which would close it).
const scrollToActive = () => nextTick(() => {
    const box = list.value;
    const option = box?.querySelector(`[data-index="${active.value}"]`);
    if (!option) return;
    if (option.offsetTop < box.scrollTop) {
        box.scrollTop = option.offsetTop;
    } else if (option.offsetTop + option.offsetHeight > box.scrollTop + box.clientHeight) {
        box.scrollTop = option.offsetTop + option.offsetHeight - box.clientHeight;
    }
});

const onOutside = (event) => {
    if (!list.value?.contains(event.target) && !trigger.value?.contains(event.target)) hide(false);
};
// Scrolling the page would leave the list behind; scrolling the list itself is fine.
const onScroll = (event) => {
    if (!list.value?.contains(event.target)) hide(false);
};
const onResize = () => hide(false);

const show = async () => {
    if (props.disabled || open.value) return;
    // Give the list its final width before measuring its height.
    const rect = trigger.value.getBoundingClientRect();
    position.value = { top: rect.bottom + 4, left: rect.left, width: Math.max(rect.width, 180), maxHeight: 288 };
    open.value = true;
    active.value = selectedIndex.value >= 0 ? selectedIndex.value : 0;
    await nextTick();
    place();
    list.value?.focus({ preventScroll: true });
    scrollToActive();
    document.addEventListener('pointerdown', onOutside, true);
    window.addEventListener('scroll', onScroll, true);
    window.addEventListener('resize', onResize);
};

function hide(returnFocus = true) {
    if (!open.value) return;
    open.value = false;
    document.removeEventListener('pointerdown', onOutside, true);
    window.removeEventListener('scroll', onScroll, true);
    window.removeEventListener('resize', onResize);
    if (returnFocus) trigger.value?.focus();
}

const choose = (index) => {
    const item = items.value[index];
    if (item) model.value = item.value;
    hide();
};

const moveTo = (index) => {
    const last = items.value.length - 1;
    active.value = Math.min(Math.max(index, 0), last);
    scrollToActive();
};

// Type-to-find, like the browser's own dropdowns.
let typed = '';
let typedTimer = null;
const findByTyping = (key) => {
    typed += key.toLowerCase();
    clearTimeout(typedTimer);
    typedTimer = setTimeout(() => { typed = ''; }, 700);
    const n = items.value.length;
    const from = typed.length === 1 ? active.value + 1 : Math.max(active.value, 0);
    for (let k = 0; k < n; k++) {
        const i = (from + k) % n;
        if (items.value[i].label.toLowerCase().startsWith(typed)) return i;
    }
    return -1;
};
const isTypingKey = (event) => event.key.length === 1 && event.key !== ' ' && !event.ctrlKey && !event.metaKey && !event.altKey;

const onTriggerKeydown = (event) => {
    if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
        event.preventDefault();
        show();
    } else if (isTypingKey(event)) {
        const i = findByTyping(event.key);
        if (i >= 0) {
            active.value = i;
            model.value = items.value[i].value;
        }
    }
};

const onListKeydown = (event) => {
    const keys = {
        ArrowDown: () => moveTo(active.value + 1),
        ArrowUp: () => moveTo(active.value - 1),
        Home: () => moveTo(0),
        End: () => moveTo(items.value.length - 1),
        PageDown: () => moveTo(active.value + 6),
        PageUp: () => moveTo(active.value - 6),
        Enter: () => choose(active.value),
        ' ': () => choose(active.value),
        Escape: () => hide(),
    };
    if (event.key === 'Tab') {
        hide(); // focus goes back to the field, then Tab moves on from there
    } else if (keys[event.key]) {
        event.preventDefault();
        keys[event.key]();
    } else if (isTypingKey(event)) {
        const i = findByTyping(event.key);
        if (i >= 0) moveTo(i);
    }
};

onBeforeUnmount(() => {
    clearTimeout(typedTimer);
    hide(false);
});
</script>

<template>
    <div class="relative">
        <button :id="id" ref="trigger" type="button" role="combobox" aria-haspopup="listbox"
            :aria-expanded="open" :aria-controls="listId" :aria-label="`${label}: ${selectedLabel}`"
            :aria-describedby="describedby" :aria-invalid="invalid ? 'true' : undefined" :disabled="disabled"
            :class="[
                size === 'sm' ? 'py-1.5 pl-2.5 pr-2 text-[14px]' : 'py-2 pl-3 pr-2.5 text-[15px]',
                invalid ? 'border-red-600' : open ? 'border-blue-600 ring-1 ring-blue-600' : 'border-slate-300 hover:border-slate-400',
            ]"
            class="flex w-full items-center justify-between gap-2 rounded-lg border bg-white text-left text-slate-900 shadow-sm disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500"
            @click="open ? hide() : show()" @keydown="onTriggerKeydown">
            <span :class="selectedIndex < 0 ? 'text-slate-500' : ''" class="truncate">{{ selectedLabel }}</span>
            <ChevronDown :size="18" class="shrink-0 text-slate-500 transition-transform" :class="open ? 'rotate-180' : ''" aria-hidden="true" />
        </button>

        <Teleport to="body">
            <ul v-if="open" :id="listId" ref="list" role="listbox" tabindex="-1" :aria-label="label"
                :aria-activedescendant="active >= 0 ? `${listId}-${active}` : undefined"
                :style="{ top: `${position.top}px`, left: `${position.left}px`, width: `${position.width}px`, maxHeight: `${position.maxHeight}px` }"
                class="fixed z-50 overflow-y-auto overscroll-contain rounded-lg border border-slate-200 bg-white py-1 shadow-popover focus:outline-none"
                @keydown="onListKeydown">
                <li v-for="(item, i) in items" :id="`${listId}-${i}`" :key="`${i}-${item.value}`" :data-index="i"
                    role="option" :aria-selected="i === selectedIndex"
                    :class="[i === active ? 'bg-slate-100' : '', i === selectedIndex ? 'font-semibold text-blue-800' : 'text-slate-800']"
                    class="flex cursor-pointer items-start gap-2 px-3 py-2 text-[15px]"
                    @mousemove="active = i" @click="choose(i)">
                    <Check v-if="i === selectedIndex" :size="18" class="mt-0.5 shrink-0 text-blue-600" aria-hidden="true" />
                    <span :class="i === selectedIndex ? '' : 'pl-[26px]'" class="min-w-0 break-words">{{ item.label }}</span>
                </li>
                <li v-if="!items.length" class="px-3 py-2 text-sm text-slate-500" role="presentation">No options</li>
            </ul>
        </Teleport>
    </div>
</template>
