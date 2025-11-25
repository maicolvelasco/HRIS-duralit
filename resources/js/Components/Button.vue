<script setup>
defineProps({ variant: String });
</script>

<template>
    <button
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition"
        :class="{
            'bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600': variant === 'primary',
            'bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700': variant === 'secondary',
        }"
    >
        <slot />
    </button>
</template>