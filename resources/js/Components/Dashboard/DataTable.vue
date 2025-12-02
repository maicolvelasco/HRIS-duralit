<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: String,
    data: Array,
    columns: Array
});
</script>

<template>
    <div class="bg-white dark:bg-slate-800 overflow-hidden shadow rounded-lg border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                {{ title }}
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-slate-700">
                    <tr>
                        <th v-for="col in columns" 
                            :key="col.key"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ col.label }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="row in data" :key="row.id" class="hover:bg-gray-50 dark:hover:bg-slate-700">
                        <td v-for="col in columns" 
                            :key="col.key"
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            
                            <!-- Badge para estado -->
                            <span v-if="col.type === 'badge' && col.key === 'retraso'"
                                  :class="row.retraso ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'"
                                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                {{ row.retraso ? 'Con Retraso' : 'Puntual' }}
                            </span>
                            
                            <!-- Valor normal -->
                            <span v-else>
                                {{ row[col.key] }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-if="data.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                No hay registros para mostrar
            </div>
        </div>
    </div>
</template>