<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import ModalRegistrarEntradaManual from '@/Components/ModalRegistrarEntradaManual.vue';
import ModalRegistrarSalidaManual from '@/Components/ModalRegistrarSalidaManual.vue';

const props = defineProps({
  assistances: Object,
  users: Array,
  permissions: Object,
});

const showEntrada = ref(false);
const salidaModal = ref(false);
const selectedAssistance = ref(null);

function openSalidaModal(ass) {
  selectedAssistance.value = ass;
  salidaModal.value = true;
}
</script>

<template>
  <Head title="Asistencias" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Asistencias
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm dark:bg-slate-800 sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="mb-4 flex items-center justify-between">
              <span class="text-lg font-medium">Listado de asistencias</span>

                            <!-- Botones de descarga -->
              <div class="flex items-center gap-2">
                <a
                  :href="route('assistance.export', { format: 'excel' })"
                  class="rounded-md bg-emerald-600 px-4 py-2 text-sm text-white hover:bg-emerald-500"
                >
                  Excel
                </a>
                <a
                  :href="route('assistance.export', { format: 'pdf' })"
                  class="rounded-md bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-500"
                >
                  PDF
                </a>
              </div>

              <button
                v-if="permissions['Registro de Entrada'] || permissions['Control de Asistencia']"
                @click="showEntrada = true"
                class="rounded-md bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-500"
              >
                Registrar entrada
              </button>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-700">
                  <tr>
                    <th class="px-4 py-2 text-left">Usuario</th>
                    <th class="px-4 py-2 text-left">Fecha entrada</th>
                    <th class="px-4 py-2 text-left">Hora entrada</th>
                    <th class="px-4 py-2 text-left">Fecha salida</th>
                    <th class="px-4 py-2 text-left">Hora salida</th>
                    <th class="px-4 py-2 text-left">Entrada</th>
                    <th class="px-4 py-2 text-left">Salida</th>
                    <th class="px-4 py-2 text-left">Retraso</th>
                    <th class="px-4 py-2 text-left">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="ass in assistances.data"
                    :key="ass.id"
                    class="border-t dark:border-gray-700"
                  >
                    <td class="px-4 py-2">
                      {{ ass.user.nombre }} {{ ass.user.apellido }}
                    </td>
                    <td class="px-4 py-2">{{ ass.fecha_entrada ? ass.fecha_entrada.split('T')[0] : '-' }}</td>
                    <td class="px-4 py-2">{{ ass.hora_entrada ?? '-' }}</td>
                    <td class="px-4 py-2">{{ ass.fecha_salida ?? '-' }}</td>
                    <td class="px-4 py-2">{{ ass.hora_salida ?? '-' }}</td>
                    <td class="px-4 py-2">
                      <span
                        :class="ass.entrada ? 'text-green-600' : 'text-red-600'"
                        class="font-semibold"
                      >
                        {{ ass.entrada ? 'Entrada' : '' }}
                      </span>
                    </td>
                    <td class="px-4 py-2">
                      <span
                        :class="ass.salida ? 'text-green-600' : 'text-red-600'"
                        class="font-semibold"
                      >
                        {{ ass.salida ? 'Salida' : '' }}
                      </span>
                    </td>
                    <td class="px-4 py-2">
                      <span
                        :class="ass.affirmation?.retraso ? 'text-red-600' : 'text-green-600'"
                        class="font-semibold"
                      >
                        {{ ass.affirmation ? (ass.affirmation.retraso ? 'Retraso' : 'Cumplido') : '-' }}
                      </span>
                    </td>
                    <td class="px-4 py-2">
                      <button
                        v-if="(permissions['Registro de Salida'] || permissions['Control de Asistencia']) && ass && ass.salida === false"
                        @click="openSalidaModal(ass)"
                        class="rounded-md bg-orange-600 px-3 py-1 text-xs text-white hover:bg-orange-500"
                      >
                        Registrar salida
                      </button>
                      <span v-else-if="ass && ass.salida === true" class="text-xs text-gray-500">Completado</span>
                      <span v-else class="text-xs text-gray-400">-</span>
                    </td>
                  </tr>
                  <tr v-if="assistances.data.length === 0">
                    <td colspan="9" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                      Sin registros
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="mt-4 flex items-center justify-between">
              <div class="text-sm text-gray-600 dark:text-gray-400">
                Mostrando {{ assistances.from }} - {{ assistances.to }} de {{ assistances.total }}
              </div>
              <div class="flex gap-2">
                <Link
                  v-for="link in assistances.links"
                  :key="link.label"
                  :href="link.url || '#'"
                  class="px-3 py-1 text-sm rounded"
                  :class="{
                    'bg-indigo-600 text-white': link.active,
                    'bg-gray-200 dark:bg-slate-700 text-gray-700 dark:text-gray-300': !link.active,
                    'opacity-50 cursor-not-allowed': !link.url
                  }"
                  v-html="link.label"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ModalRegistrarEntradaManual
      :show="showEntrada"
      :users="users"
      @close="showEntrada = false"
    />

    <ModalRegistrarSalidaManual
      :show="salidaModal"
      :assistance="selectedAssistance"
      @close="salidaModal = false"
    />
  </AuthenticatedLayout>
</template>