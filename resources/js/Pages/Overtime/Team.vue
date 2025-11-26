<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Icon from '@/Components/Icon.vue';

defineProps({
  totalDisponible: Number,
  subordinatesOvertimes: Array,
  isManager: Boolean,
});

function badgeColor(estado) {
  switch (estado) {
    case 'Pendiente':
      return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300';
    case 'Aprobado':
      return 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300';
    case 'Usado':
      return 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300';
    default:
      return 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300';
  }
}
</script>

<template>
  <Head title="S. Tiempo de Equipo" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
        <Icon name="ClockIcon" class="w-6 h-6 text-indigo-600" />
        Sobre Tiempo de Equipo
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        
        <!-- Mensaje si no es manager -->
        <div v-if="!isManager" class="bg-white dark:bg-slate-800 shadow-md rounded-xl p-6">
          <p class="text-gray-600 dark:text-gray-400">No tienes permiso para ver esta sección.</p>
        </div>

        <!-- Sobretiempos del equipo -->
        <div v-else class="bg-white dark:bg-slate-800 shadow-md rounded-xl p-6">
          <!-- ✅ CAMBIADO: Título genérico -->
          <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Sobretiempos de mi Equipo</h3>
          
          <!-- Escritorio -->
          <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-gray-50 dark:bg-slate-900">
                <tr>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Usuario</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Fecha</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Desde</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Hasta</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Horas</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Trabajo</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Acción</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-for="ot in subordinatesOvertimes" :key="ot.id" class="hover:bg-gray-50 dark:hover:bg-slate-700">
                  <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ ot.user.codigo }} - {{ ot.user.nombre }} {{ ot.user.apellido }}</td>
                  <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ ot.fecha }}</td>
                  <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ ot.desde }}</td>
                  <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ ot.hasta }}</td>
                  <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ ot.contador }}</td>
                  <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ ot.trabajo }}</td>
                  <td class="px-4 py-3 text-sm">
                    <!-- ✅ NUEVO: Condición para mostrar botón o texto -->
                    <PrimaryButton 
                      v-if="ot.estado === 'Pendiente'" 
                      size="sm" 
                      @click="$inertia.post(route('overtimes.update-status', ot.id), { estado: 'Aprobado' })"
                    >
                      <Icon name="CheckCircleIcon" class="w-4 h-4" />
                      Aprobar
                    </PrimaryButton>
                    <span 
                      v-else-if="ot.estado === 'Aprobado'" 
                      class="text-green-600 dark:text-green-400 font-medium"
                    >
                      Aprobado
                    </span>
                    <span 
                      v-else 
                      class="text-gray-500 dark:text-gray-400"
                    >
                      {{ ot.estado }}
                    </span>
                  </td>
                </tr>
                <tr v-if="subordinatesOvertimes.length === 0">
                  <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    <!-- ✅ CAMBIADO: Mensaje genérico -->
                    No hay sobretiempos registrados para tu equipo
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Móvil -->
          <div class="md:hidden space-y-4">
            <div v-for="ot in subordinatesOvertimes" :key="ot.id" class="bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow">
              <div class="flex justify-between items-start mb-2">
                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ ot.user.nombre }} {{ ot.user.apellido }}</span>
                <span class="px-2 py-1 text-xs rounded-full" :class="badgeColor(ot.estado)">
                  {{ ot.estado }}
                </span>
              </div>
              <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1 mb-3">
                <div>Fecha: {{ ot.fecha }}</div>
                <div>Horario: {{ ot.desde }} - {{ ot.hasta }}</div>
                <div>Horas: {{ ot.contador }}</div>
                <div>Trabajo: {{ ot.trabajo }}</div>
              </div>
              <!-- ✅ NUEVO: Condición para móvil -->
              <PrimaryButton 
                v-if="ot.estado === 'Pendiente'" 
                size="sm" 
                class="w-full" 
                @click="$inertia.post(route('overtimes.update-status', ot.id), { estado: 'Aprobado' })"
              >
                <Icon name="CheckCircleIcon" class="w-4 h-4 mr-2" />
                Aprobar
              </PrimaryButton>
              <span 
                v-else-if="ot.estado === 'Aprobado'" 
                class="text-green-600 dark:text-green-400 font-medium w-full text-center block py-2"
              >
                Aprobado
              </span>
              <span 
                v-else 
                class="text-gray-500 dark:text-gray-400 w-full text-center block py-2"
              >
                {{ ot.estado }}
              </span>
            </div>
            <div v-if="subordinatesOvertimes.length === 0" class="text-center text-sm text-gray-500 dark:text-gray-400">
              <!-- ✅ CAMBIADO: Mensaje genérico -->
              No hay sobretiempos registrados para tu equipo
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>