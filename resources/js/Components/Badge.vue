<script setup>
defineProps({ type: String });
</script>

<template>
    <span
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
        :class="{
            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300': type === 'success',
            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300': type === 'danger',
        }"
    >
        <slot />
    </span>
</template>