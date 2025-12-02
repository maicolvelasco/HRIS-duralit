<script setup>
import { Link } from '@inertiajs/vue3';
import {
  PlusIcon, DocumentArrowUpIcon, ClockIcon,
  BanknotesIcon, UserPlusIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  permissions: Object
});
</script>

<template>
  <div class="bg-white dark:bg-slate-800 shadow rounded-lg border border-gray-100 dark:border-gray-700 p-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Acciones Rápidas</h3>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
      <Link v-if="$page.props.auth.permissions?.['Crear Usuarios']" :href="route('settings.index') + '?section=users'" class="flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-700 hover:bg-gray-50 dark:hover:bg-slate-600">
        <UserPlusIcon class="h-5 w-5 mr-2" />
        Nuevo Usuario
      </Link>
      <Link v-if="$page.props.auth.permissions?.['Crear Permisos de Trabajo']" :href="route('permissions.create')" class="flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-700 hover:bg-gray-50 dark:hover:bg-slate-600">
        <DocumentArrowUpIcon class="h-5 w-5 mr-2" />
        Solicitar Permiso
      </Link>
      <Link v-if="$page.props.auth.permissions?.['Registro de Entrada']" :href="route('assistance.store')" method="post" :data="{ type: 'entrada' }" as="button" class="flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-700 hover:bg-gray-50 dark:hover:bg-slate-600">
        <ClockIcon class="h-5 w-5 mr-2" />
        Marcar Entrada
      </Link>
      <Link v-if="$page.props.auth.permissions?.['Crear Nóminas']" :href="route('payroll.create')" class="flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-700 hover:bg-gray-50 dark:hover:bg-slate-600">
        <BanknotesIcon class="h-5 w-5 mr-2" />
        Nueva Nómina
      </Link>
      <Link v-if="$page.props.auth.permissions?.['Crear Sobretiempos']" :href="route('overtimes.create')" class="flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-700 hover:bg-gray-50 dark:hover:bg-slate-600">
        <PlusIcon class="h-5 w-5 mr-2" />
        Registrar Horas Extras
      </Link>
    </div>
  </div>
</template>