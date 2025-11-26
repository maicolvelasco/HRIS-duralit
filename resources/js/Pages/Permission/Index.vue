<script setup>
import { computed, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PermissionModal from '@/Components/PermissionModal.vue';

const props = defineProps({
    permissions: Object,
    authorizations: Array,
    titulations: Array,
});

const user = computed(() => usePage().props.auth.user);
const showModal = ref(false);

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('es-ES', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric' 
    });
};

const formatTime = (time) => {
    if (!time) return '-';
    return time.substring(0, 5);
};

const getStatusClass = (estado) => {
    const classes = {
        'Pendiente': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'Aprobado': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'Rechazado': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        'Completado': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    };
    return classes[estado] || 'bg-gray-100 text-gray-800';
};

const filteredPermissions = computed(() => props.permissions.data);

const userName = computed(() => {
    return `${user.value.nombre} ${user.value.apellido}`;
});
</script>

<template>
    <Head title="Permisos de Trabajo" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    Permisos de Trabajo
                </h2>
                <PrimaryButton @click="showModal = true">
                    Nuevo Permiso
                </PrimaryButton>
            </div>
        </template>

        <div class="py-6 sm:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Tabla para PC -->
                <div class="hidden sm:block bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-slate-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Usuario
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Período
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Cantidad
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Motivo
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="permission in filteredPermissions" :key="permission.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    <div class="font-medium">{{ permission.user.nombre }} {{ permission.user.apellido }}</div>
                                    <div class="text-xs text-gray-400">{{ permission.user.codigo }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        {{ permission.tipo === 'dias' ? 'Días' : 'Horas' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    <template v-if="permission.tipo === 'dias'">
                                        {{ formatDate(permission.fecha_inicio) }} - {{ formatDate(permission.fecha_fin) }}
                                    </template>
                                    <template v-else>
                                        {{ formatTime(permission.hora_inicio) }} - {{ formatTime(permission.hora_fin) }}
                                    </template>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    <template v-if="permission.tipo === 'dias'">
                                        {{ permission.cantidad_dias }} días
                                    </template>
                                    <template v-else>
                                        {{ permission.cantidad_horas }} horas
                                    </template>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusClass(permission.estado)]">
                                        {{ permission.estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300 max-w-xs truncate">
                                    {{ permission.motivo }}
                                </td>
                            </tr>
                            <tr v-if="filteredPermissions.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No se encontraron solicitudes de permiso.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Lista para móvil -->
                <div class="sm:hidden space-y-4">
                    <div
                        v-for="permission in filteredPermissions"
                        :key="permission.id"
                        class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg p-4"
                    >
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ permission.user.nombre }} {{ permission.user.apellido }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ permission.user.codigo }}
                                </div>
                            </div>
                            <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusClass(permission.estado)]">
                                {{ permission.estado }}
                            </span>
                        </div>
                        
                        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            <div class="flex justify-between">
                                <span class="font-medium">Tipo:</span>
                                <span>{{ permission.tipo === 'dias' ? 'Días' : 'Horas' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Período:</span>
                                <span>
                                    <template v-if="permission.tipo === 'dias'">
                                        {{ formatDate(permission.fecha_inicio) }} - {{ formatDate(permission.fecha_fin) }}
                                    </template>
                                    <template v-else>
                                        {{ formatTime(permission.hora_inicio) }} - {{ formatTime(permission.hora_fin) }}
                                    </template>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Cantidad:</span>
                                <span>
                                    <template v-if="permission.tipo === 'dias'">
                                        {{ permission.cantidad_dias }} días
                                    </template>
                                    <template v-else>
                                        {{ permission.cantidad_horas }} horas
                                    </template>
                                </span>
                            </div>
                            <div>
                                <span class="font-medium">Motivo:</span>
                                <p class="mt-1 text-gray-600 dark:text-gray-400">{{ permission.motivo }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="filteredPermissions.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">
                        No se encontraron solicitudes de permiso.
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <PermissionModal
            :show="showModal"
            :authorizations="authorizations"
            :user-name="userName"
            @close="showModal = false"
        />
    </AuthenticatedLayout>
</template>