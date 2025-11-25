<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <!-- fondo gris-100 en claro / slate-900 en oscuro -->
  <div
    class="flex min-h-screen flex-col items-center bg-gray-100 dark:bg-slate-900 pt-6 sm:justify-center sm:pt-0"
  >
    <div>
      <Link href="/">
        <!-- logo gris-500 claro / gris-300 oscuro -->
        <ApplicationLogo class="h-20 w-20 fill-current text-gray-500 dark:text-gray-300" />
      </Link>
    </div>

    <!-- recuadro blanco en claro / slate-800 en oscuro -->
    <div
      class="mt-6 w-full overflow-hidden bg-white dark:bg-slate-800 px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
    >
      <slot />
    </div>
  </div>
</template>