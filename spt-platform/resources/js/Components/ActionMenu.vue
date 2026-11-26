<!-- Accessible action menu ("Manage" button + list of actions). The list is
     placed on the page body so tables with horizontal scrolling don't clip
     it. Keyboard: Enter/Space or Arrow keys open it, Arrow keys move,
     Escape closes and returns focus to the button. -->
<script setup>
import { nextTick, onBeforeUnmount, ref } from 'vue';
import { ChevronDown } from 'lucide-vue-next';

const props = defineProps({
    // [{ label, icon?, hint?, tone?: 'danger', onSelect: () => void }]
    items: { type: Array, required: true },
    label: { type: String, default: 'Manage' },
    // Accessible name, e.g. "Manage Maria Cruz's account" (keep the visible label in it).
    srLabel: String,
    buttonClass: { type: String, default: 'btn btn-secondary btn-sm' },
    showChevron: { type: Boolean, default: true },
    width: { type: Number, default: 240 },
});

const open = ref(false);
const trigger = ref(null);
const menu = ref(null);
const position = ref({ top: 0, left: 0 });

const menuItems = () => [...(menu.value?.querySelectorAll('[role="menuitem"]') ?? [])];
const focusItem = (index) => {
    const items = menuItems();
    if (items.length) items[(index + items.length) % items.length].focus();
};

const place = () => {
    const rect = trigger.value.getBoundingClientRect();
    const height = menu.value?.offsetHeight ?? 0;
    const below = rect.bottom + 6;
    const top = below + height > window.innerHeight - 8 && rect.top - height - 6 > 8 ? rect.top - height - 6 : below;
    const left = Math.min(Math.max(8, rect.right - props.width), window.innerWidth - props.width - 8);
    position.value = { top, left };
};

const show = async (focusIndex = 0) => {
    open.value = true;
    await nextTick();
    place();
    focusItem(focusIndex);
    document.addEventListener('pointerdown', onOutside);
    window.addEventListener('scroll', close, true);
    window.addEventListener('resize', close);
};

const hide = (returnFocus = true) => {
    if (!open.value) return;
    open.value = false;
    document.removeEventListener('pointerdown', onOutside);
    window.removeEventListener('scroll', close, true);
    window.removeEventListener('resize', close);
    if (returnFocus) trigger.value?.focus();
};
const close = () => hide(false);

const onOutside = (event) => {
    if (!menu.value?.contains(event.target) && !trigger.value?.contains(event.target)) hide(false);
};

const onMenuKeydown = (event) => {
    const items = menuItems();
    const index = items.indexOf(document.activeElement);
    const keys = {
        Escape: () => hide(),
        ArrowDown: () => focusItem(index + 1),
        ArrowUp: () => focusItem(index - 1),
        Home: () => focusItem(0),
        End: () => focusItem(items.length - 1),
    };
    if (keys[event.key]) {
        event.preventDefault();
        keys[event.key]();
    } else if (event.key === 'Tab') {
        hide(false);
    }
};

const select = (item) => {
    hide();
    item.onSelect();
};

onBeforeUnmount(() => hide(false));
</script>

<template>
    <button ref="trigger" type="button" :class="buttonClass" aria-haspopup="menu" :aria-expanded="open" :aria-label="srLabel"
        @click="open ? hide() : show()" @keydown.down.prevent="show(0)" @keydown.up.prevent="show(-1)">
        <slot>{{ label }}</slot>
        <ChevronDown v-if="showChevron" :size="16" aria-hidden="true" />
    </button>

    <Teleport to="body">
        <div v-if="open" ref="menu" role="menu" :aria-label="srLabel || label"
            :style="{ top: `${position.top}px`, left: `${position.left}px`, width: `${width}px` }"
            class="fixed z-50 rounded-lg border border-slate-200 bg-white py-1 shadow-popover" @keydown="onMenuKeydown">
            <button v-for="item in items" :key="item.label" type="button" role="menuitem" tabindex="-1"
                :class="item.tone === 'danger' ? 'text-red-700 hover:bg-red-50 focus:bg-red-50' : 'text-slate-800 hover:bg-slate-50 focus:bg-slate-100'"
                class="flex w-full items-start gap-3 px-3 py-2 text-left text-[15px] focus:outline-none" @click="select(item)">
                <component :is="item.icon" v-if="item.icon" :size="18" class="mt-0.5 shrink-0" aria-hidden="true" />
                <span class="min-w-0">
                    <span class="block font-medium">{{ item.label }}</span>
                    <span v-if="item.hint" class="block text-xs text-slate-500">{{ item.hint }}</span>
                </span>
            </button>
        </div>
    </Teleport>
</template>
