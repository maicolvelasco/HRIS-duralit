<script setup>
import { Head, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    permissions: Array,
    isHrManager: Boolean,
    isGroupManager: Boolean,
});

const user = computed(() => usePage().props.auth.user);

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const formatTime = (time) => {
    if (!time) return '';
    return time.substring(0, 5);
};

const formatPeriod = (permission) => {
    const parts = [];

    if (permission.fecha_inicio) {
        const fechaInicio = formatDate(permission.fecha_inicio);
        const fechaFin = permission.fecha_fin ? formatDate(permission.fecha_fin) : null;
        parts.push(fechaFin ? `${fechaInicio} - ${fechaFin}` : `Desde ${fechaInicio}`);
    }

    if (permission.hora_inicio) {
        const horaInicio = formatTime(permission.hora_inicio);
        const horaFin = permission.hora_fin ? formatTime(permission.hora_fin) : null;
        parts.push(horaFin ? `${horaInicio} - ${horaFin}` : `Desde ${horaInicio}`);
    }

    return parts.join(' | ') || '-';
};

const getStatusClass = (estado) => {
    const classes = {
        Pendiente: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        Aprobado: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        Rechazado: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        Completado: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    };
    return classes[estado] || 'bg-gray-100 text-gray-800';
};

const goToSign = (permissionId) => {
    router.visit(route('permissions.sign', permissionId));
};

const updateStatus = (permissionId, newStatus) => {
    if (newStatus === 'Rechazado') {
        const motivo = prompt('Por favor, ingrese el motivo del rechazo:');
        if (!motivo || motivo.trim() === '') {
            alert('Debe ingresar un motivo para rechazar el permiso.');
            return;
        }

        if (confirm(`¿Está seguro de rechazar este permiso con el motivo: "${motivo.trim()}"?`)) {
            router.post(route('permissions.update-status', permissionId), {
                estado: 'Rechazado',
                observaciones: motivo.trim(),
            });
        }
    }
};
</script>

<template>
    <Head :title="isHrManager ? 'Permisos HR' : 'P. de Grupo'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <Icon name="UserGroupIcon" class="w-6 h-6 text-indigo-600" />
                {{ isHrManager ? 'Permisos HR - Aprobados' : 'Permisos de Grupo' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 shadow-md rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">
                        {{ isHrManager ? 'Todos los Permisos Aprobados' : 'Permisos Pendientes de mi Equipo' }}
                    </h3>

                    <!-- Versión Escritorio -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 dark:bg-slate-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Usuario</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Tipo</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Período</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Cantidad</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Motivo</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Autorización</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Observaciones</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Estado</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="perm in permissions" :key="perm.id" class="hover:bg-gray-50 dark:hover:bg-slate-700">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ perm.user.codigo }} - {{ perm.user.nombre }} {{ perm.user.apellido }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ perm.tipo === 'dias' ? 'Días' : 'Horas' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ formatPeriod(perm) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ perm.tipo === 'dias' ? `${perm.cantidad_dias} días` : `${perm.cantidad_horas} horas` }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 max-w-xs truncate" :title="perm.motivo">
                                        {{ perm.motivo }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ perm.authorization?.nombre || '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 max-w-xs truncate" :title="perm.observaciones">
                                        {{ perm.observaciones || '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusClass(perm.estado)]">
                                            {{ perm.estado }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <!-- HR MANAGERS: Completar o Rechazar -->
                                        <template v-if="isHrManager">
                                            <div class="flex gap-2" v-if="perm.estado === 'Aprobado'">
                                                <PrimaryButton 
                                                    size="sm" 
                                                    @click="goToSign(perm.id)"
                                                    class="bg-green-600 hover:bg-green-700"
                                                >
                                                    <Icon name="CheckCircleIcon" class="w-4 h-4" />
                                                    Completar
                                                </PrimaryButton>
                                                <PrimaryButton 
                                                    size="sm" 
                                                    @click="updateStatus(perm.id, 'Rechazado')"
                                                    class="bg-red-600 hover:bg-red-700"
                                                >
                                                    <Icon name="XCircleIcon" class="w-4 h-4" />
                                                    Rechazar
                                                </PrimaryButton>
                                            </div>
                                            <span v-else :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusClass(perm.estado)]">
                                                {{ perm.estado }}
                                            </span>
                                        </template>

                                        <!-- JEFES DE GRUPO: Aprobar o Rechazar -->
                                        <template v-else>
                                            <div class="flex gap-2" v-if="perm.estado === 'Pendiente'">
                                                <PrimaryButton 
                                                    size="sm" 
                                                    @click="goToSign(perm.id)"
                                                    class="bg-green-600 hover:bg-green-700"
                                                >
                                                    <Icon name="CheckCircleIcon" class="w-4 h-4" />
                                                    Aprobar
                                                </PrimaryButton>
                                                <PrimaryButton 
                                                    size="sm" 
                                                    @click="updateStatus(perm.id, 'Rechazado')"
                                                    class="bg-red-600 hover:bg-red-700"
                                                >
                                                    <Icon name="XCircleIcon" class="w-4 h-4" />
                                                    Rechazar
                                                </PrimaryButton>
                                            </div>
                                            <span v-else :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusClass(perm.estado)]">
                                                {{ perm.estado }}
                                            </span>
                                        </template>
                                    </td>
                                </tr>
                                <tr v-if="permissions.length === 0">
                                    <td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No hay permisos para mostrar.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Versión Móvil -->
                    <div class="md:hidden space-y-4">
                        <div v-for="perm in permissions" :key="perm.id" class="bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-semibold text-gray-900 dark:text-gray-100">
                                    {{ perm.user.codigo }} - {{ perm.user.nombre }} {{ perm.user.apellido }}
                                </span>
                                <span :class="['px-2 py-1 text-xs rounded-full', getStatusClass(perm.estado)]">
                                    {{ perm.estado }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1 mb-3">
                                <div>Tipo: {{ perm.tipo === 'dias' ? 'Días' : 'Horas' }}</div>
                                <div>Período: {{ formatPeriod(perm) }}</div>
                                <div>Cantidad: {{ perm.tipo === 'dias' ? `${perm.cantidad_dias} días` : `${perm.cantidad_horas} horas` }}</div>
                                <div>Motivo: {{ perm.motivo }}</div>
                                <div>Autorización: {{ perm.authorization?.nombre || '-' }}</div>
                                <div>Observaciones: {{ perm.observaciones || '-' }}</div>
                            </div>
                            <!-- HR MANAGERS: Completar o Rechazar (móvil) -->
                            <template v-if="isHrManager">
                                <div class="grid grid-cols-2 gap-2 mt-3" v-if="perm.estado === 'Aprobado'">
                                    <PrimaryButton 
                                        size="sm" 
                                        @click="goToSign(perm.id)"
                                        class="bg-green-600 hover:bg-green-700"
                                    >
                                        <Icon name="CheckCircleIcon" class="w-4 h-4" />
                                        Completar
                                    </PrimaryButton>
                                    <PrimaryButton 
                                        size="sm" 
                                        @click="updateStatus(perm.id, 'Rechazado')"
                                        class="bg-red-600 hover:bg-red-700"
                                    >
                                        <Icon name="XCircleIcon" class="w-4 h-4" />
                                        Rechazar
                                    </PrimaryButton>
                                </div>
                            </template>
                            <!-- JEFES DE GRUPO: Aprobar o Rechazar (móvil) -->
                            <template v-else>
                                <div class="grid grid-cols-2 gap-2 mt-3" v-if="perm.estado === 'Pendiente'">
                                    <PrimaryButton 
                                        size="sm" 
                                        @click="goToSign(perm.id)"
                                        class="bg-green-600 hover:bg-green-700"
                                    >
                                        <Icon name="CheckCircleIcon" class="w-4 h-4" />
                                        Aprobar
                                    </PrimaryButton>
                                    <PrimaryButton 
                                        size="sm" 
                                        @click="updateStatus(perm.id, 'Rechazado')"
                                        class="bg-red-600 hover:bg-red-700"
                                    >
                                        <Icon name="XCircleIcon" class="w-4 h-4" />
                                        Rechazar
                                    </PrimaryButton>
                                </div>
                            </template>
                        </div>
                        <div v-if="permissions.length === 0" class="text-center text-sm text-gray-500 dark:text-gray-400">
                            No hay permisos para mostrar.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>