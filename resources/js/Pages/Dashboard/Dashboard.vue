<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import StatCard from '@/Components/Dashboard/StatCard.vue';
import ChartCard from '@/Components/Dashboard/ChartCard.vue';
import DataTable from '@/Components/Dashboard/DataTable.vue';
import QuickActions from '@/Components/Dashboard/QuickActions.vue';
import {
  UsersIcon, ClockIcon, DocumentTextIcon, CurrencyDollarIcon,
  ArrowTrendingUpIcon, BuildingOfficeIcon, CalendarIcon,
  ChartBarIcon, BanknotesIcon, CheckCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  stats: Object,
  charts: Object,
  recentData: Object,
  filters: Object
});

const formatCurrency = (v) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(v || 0);
const formatNumber = (v) => new Intl.NumberFormat('es-BO').format(v || 0);
const formatHours = (v) => `${(v || 0).toFixed(1)} hrs`;
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold text-gray-800 dark:text-white">Dashboard</h2>
        <div class="text-sm text-gray-500 dark:text-gray-400">
          {{ new Date().toLocaleDateString('es-BO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
        </div>
      </div>
    </template>

    <!-- KPIs Principales -->
    <div class="py-6">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
          <StatCard v-if="stats.users" title="Usuarios Activos" :value="formatNumber(stats.users.active)" :total="formatNumber(stats.users.total)" icon="UsersIcon" color="blue" />
          <StatCard v-if="stats.attendance" title="Asistencias Hoy" :value="formatNumber(stats.attendance.today)" :trend="stats.attendance.delays + ' retrasos'" icon="ClockIcon" color="green" />
          <StatCard v-if="stats.permissions" title="Permisos Pendientes" :value="formatNumber(stats.permissions.pending)" :trend="stats.permissions.approved + ' aprobados'" icon="DocumentTextIcon" color="yellow" />
          <StatCard v-if="stats.payrolls" title="Total Nóminas Aprobadas" :value="formatCurrency(stats.payrolls.total_amount)" :trend="stats.payrolls.approved + ' aprobadas'" icon="CurrencyDollarIcon" color="purple" />
          <StatCard v-if="stats.overtimes" title="Horas Extras Disponibles" :value="formatHours(stats.overtimes.available)" :trend="stats.overtimes.pending + ' pendientes'" icon="ArrowTrendingUpIcon" color="orange" />
          <StatCard v-if="stats.branches" title="Sucursales Activas" :value="formatNumber(stats.branches.active)" :total="formatNumber(stats.branches.total)" icon="BuildingOfficeIcon" color="indigo" />
          <StatCard v-if="stats.groups" title="Grupos Activos" :value="formatNumber(stats.groups.with_users)" :total="formatNumber(stats.groups.total)" icon="ChartBarIcon" color="pink" />
          <StatCard v-if="stats.sections" title="Secciones Activas" :value="formatNumber(stats.sections.with_users)" :total="formatNumber(stats.sections.total)" icon="CheckCircleIcon" color="teal" />
        </div>

        <!-- Gráficos Principales -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <ChartCard v-if="charts.attendanceTrend" title="Tendencia de Asistencias (Últimos 7 días)" type="line" :data="charts.attendanceTrend" class="lg:col-span-2" />
          <ChartCard v-if="charts.usersByBranch" title="Distribución de Usuarios por Sucursal" type="doughnut" :data="charts.usersByBranch" />
          <ChartCard v-if="charts.payrollStatus" title="Estado de Nóminas" type="doughnut" :data="charts.payrollStatus" />
        </div>

        <!-- Segunda Fila de Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <ChartCard v-if="charts.usersByGroup" title="Usuarios por Grupo" type="bar" :data="charts.usersByGroup" />
          <ChartCard v-if="charts.usersBySection" title="Usuarios por Sección" type="bar" :data="charts.usersBySection" />
          <ChartCard v-if="charts.usersByRole" title="Usuarios por Rol" type="doughnut" :data="charts.usersByRole" />
        </div>

        <!-- Tendencias Mensuales -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <ChartCard v-if="charts.payrollByMonth" title="Nóminas por Mes" type="line" :data="charts.payrollByMonth" />
          <ChartCard v-if="charts.overtimeByMonth" title="Horas Extras por Mes" type="bar" :data="charts.overtimeByMonth" />
        </div>

        <!-- Tablas de Datos Recientes -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <DataTable v-if="recentData.recentAssistances" title="Últimas Asistencias" :data="recentData.recentAssistances" :columns="[
            { key: 'codigo', label: 'Código' },
            { key: 'user', label: 'Empleado' },
            { key: 'fecha', label: 'Fecha' },
            { key: 'entrada', label: 'Entrada' },
            { key: 'salida', label: 'Salida' },
            { key: 'retraso', label: 'Estado', type: 'badge' }
          ]" />
          <DataTable v-if="recentData.pendingPermissions" title="Permisos Pendientes de Aprobación" :data="recentData.pendingPermissions" :columns="[
            { key: 'codigo', label: 'Código' },
            { key: 'user', label: 'Solicitante' },
            { key: 'tipo', label: 'Tipo' },
            { key: 'motivo', label: 'Motivo' },
            { key: 'fecha', label: 'Fecha' }
          ]" />
        </div>

        <!-- Segunda Fila de Tablas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <DataTable v-if="recentData.recentUsers" title="Usuarios Recién Registrados" :data="recentData.recentUsers" :columns="[
            { key: 'codigo', label: 'Código' },
            { key: 'nombre', label: 'Nombre' },
            { key: 'sucursal', label: 'Sucursal' },
            { key: 'rol', label: 'Rol' },
            { key: 'fecha', label: 'Registro' }
          ]" />
          <DataTable v-if="recentData.pendingOvertimes" title="Horas Extras Pendientes" :data="recentData.pendingOvertimes" :columns="[
            { key: 'codigo', label: 'Código' },
            { key: 'user', label: 'Empleado' },
            { key: 'fecha', label: 'Fecha' },
            { key: 'horas', label: 'Horas' },
            { key: 'estado', label: 'Estado', type: 'badge' }
          ]" />
        </div>

        <!-- Tercera Fila de Tablas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <DataTable v-if="recentData.recentPayrolls" title="Nóminas Recientes" :data="recentData.recentPayrolls" :columns="[
            { key: 'codigo', label: 'Código' },
            { key: 'user', label: 'Empleado' },
            { key: 'periodo', label: 'Período' },
            { key: 'neto', label: 'Neto a Pagar', type: 'currency' },
            { key: 'estado', label: 'Estado', type: 'badge' }
          ]" />
          <DataTable v-if="recentData.recentOvertimes" title="Horas Extras Aprobadas" :data="recentData.recentOvertimes" :columns="[
            { key: 'codigo', label: 'Código' },
            { key: 'user', label: 'Empleado' },
            { key: 'fecha', label: 'Fecha' },
            { key: 'horas', label: 'Horas' },
            { key: 'monto', label: 'Monto', type: 'currency' }
          ]" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>