<script setup>
import * as icons from '@heroicons/vue/24/outline';
defineProps({ name: String, class: String });
</script>

<template>
  <component :is="icons[name]" :class="$props.class" />
</template>