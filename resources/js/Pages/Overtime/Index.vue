<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Icon from '@/Components/Icon.vue';
import CreateOvertimeModal from '@/Components/CreateOvertimeModal.vue';

defineProps({
  overtimes: Array, // [{id, fecha, desde, hasta, contador, trabajo, estado}, ...]
});

const showCreateOvertime = ref(false);

function badgeColor(estado) {
  switch (estado) {
    case 'Pendiente':
      return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300';
    case 'Aprobado':
      return 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300';
    case 'Usado':
      return 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300';
    default:
      return 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300';
  }
}
</script>

<template>
  <Head title="Sobretiempos" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
        <Icon name="ClockIcon" class="w-6 h-6 text-indigo-600" />
        Mis Sobretiempos
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
        <!-- Botón Registrar -->
        <div class="flex justify-end">
            <PrimaryButton @click="showCreateOvertime = true">
            <Icon name="PlusIcon" class="w-5 h-5 mr-2" />
            Registrar Sobre Tiempo
            </PrimaryButton>
        </div>

        <!-- Escritorio: tabla -->
        <div class="hidden md:block overflow-x-auto">
          <table class="min-w-full bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow">
            <thead class="bg-gray-50 dark:bg-slate-900">
              <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Fecha</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Desde</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Hasta</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Horas</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Trabajo</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Estado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-for="ot in overtimes" :key="ot.id" class="hover:bg-gray-50 dark:hover:bg-slate-700">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ ot.fecha }}</td>
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ ot.desde }}</td>
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ ot.hasta }}</td>
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ ot.contador }}</td>
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ ot.trabajo }}</td>
                <td class="px-4 py-3 text-sm">
                  <span class="inline-flex px-2 py-1 text-xs rounded-full" :class="badgeColor(ot.estado)">
                    {{ ot.estado }}
                  </span>
                </td>
              </tr>
              <tr v-if="overtimes.length === 0">
                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                  No hay sobretiempos registrados
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Móvil: lista card -->
        <div class="md:hidden space-y-4">
          <div
            v-for="ot in overtimes"
            :key="ot.id"
            class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow"
          >
            <div class="flex justify-between items-start mb-2">
              <span class="font-semibold text-gray-900 dark:text-gray-100">{{ ot.fecha }}</span>
              <span class="px-2 py-1 text-xs rounded-full" :class="badgeColor(ot.estado)">
                {{ ot.estado }}
              </span>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
              <div>Horario: {{ ot.desde }} - {{ ot.hasta }}</div>
              <div>Horas: {{ ot.contador }}</div>
              <div>Trabajo: {{ ot.trabajo }}</div>
            </div>
          </div>
          <div v-if="overtimes.length === 0" class="text-center text-sm text-gray-500 dark:text-gray-400">
            No hay sobretiempos registrados
          </div>
        </div>
      </div>
    </div>

    <CreateOvertimeModal :show="showCreateOvertime" @close="showCreateOvertime = false" />
  </AuthenticatedLayout>
</template>