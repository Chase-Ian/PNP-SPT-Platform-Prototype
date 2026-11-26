<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="text-sm text-red-700" role="alert">
            {{ message }}
        </p>
    </div>
</template>
