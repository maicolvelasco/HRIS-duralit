<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import Icon from '@/Components/Icon.vue';
import Badge from '@/Components/Badge.vue';
import Button from '@/Components/Button.vue';
import SearchInput from '@/Components/SearchInput.vue';
import CreatePayrollModal from '@/Components/CreatePayrollModal.vue';
import EditPayrollModal from '@/Components/EditPayrollModal.vue';
import GenerateReportModal from '@/Components/GenerateReportModal.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { useTooltip } from '@/Composables/useTooltip.js';

const props = defineProps({
    payrolls: Object,
    users: Array,
    permissions: Object,
});

const search = ref('');
const showSection = ref('payrolls');
const isMobile = () => window.innerWidth < 1024;

// Tooltip setup
const tooltipRef = ref(null);
const { tooltipData, show, onMouseEnter, onMouseLeave, onTouchStart, onTouchEnd, onTouchMove, hideTooltip } = useTooltip();
onMounted(() => { window.tooltipComponent = tooltipRef.value; });

// Computed: filtered payrolls
const filteredPayrolls = computed(() => {
    if (!search.value) return props.payrolls.data;
    
    const s = search.value.toLowerCase();
    return props.payrolls.data.filter(p => 
        p.user.nombre.toLowerCase().includes(s) ||
        p.user.apellido.toLowerCase().includes(s) ||
        p.user.codigo.toLowerCase().includes(s) ||
        p.periodo.toLowerCase().includes(s)
    );
});

// Tooltip data
const getPayrollTooltipData = (payroll) => ({
    type: 'payroll',
    data: {
        ...payroll,
        userName: `${payroll.user.nombre} ${payroll.user.apellido}`,
        descuentoTotal: payroll.total_descuentos_retraso + payroll.total_descuentos_falta,
        detalles: payroll.details || []
    }
});

// Modales
const showCreatePayroll = ref(false);
const showEditPayroll = ref(false);
const showReportModal = ref(false);
const selectedPayroll = ref({});
const selectedReport = ref('');

// Estados badge
const getBadgeType = (estado) => {
    switch(estado) {
        case 'Pagado': return 'success';
        case 'Aprobado': return 'warning';
        case 'Calculado': return 'info';
        default: return 'secondary';
    }
};

// Acciones
const calculatePayroll = (payroll) => {
    if (confirm('¿Calcular descuentos para esta nómina?')) {
        router.post(route('payroll.calculate', payroll.id), {}, { preserveScroll: true });
    }
};

const approvePayroll = (payroll) => {
    if (confirm('¿Aprobar esta nómina? No se podrán hacer más cambios.')) {
        router.post(route('payroll.approve', payroll.id), {}, { preserveScroll: true });
    }
};

const payPayroll = (payroll) => {
    const metodo = prompt('Método de pago:\n1. Efectivo\n2. Transferencia\n3. Cheque', 'Transferencia');
    const metodos = { '1': 'Efectivo', '2': 'Transferencia', '3': 'Cheque' };
    
    if (metodo && metodos[metodo]) {
        const fecha = prompt('Fecha de pago (YYYY-MM-DD)', new Date().toISOString().split('T')[0]);
        if (fecha) {
            router.post(route('payroll.pay', payroll.id), {
                metodo_pago: metodos[metodo],
                fecha_pago: fecha,
            }, { preserveScroll: true });
        }
    }
};

const editPayroll = (payroll) => {
    selectedPayroll.value = payroll;
    showEditPayroll.value = true;
};

const deletePayroll = (payroll) => {
    if (confirm('¿Eliminar esta nómina? Se perderán todos los datos.')) {
        router.delete(route('payroll.destroy', payroll.id), { preserveScroll: true });
    }
};

const generateReceipt = (payroll) => {
    window.open(route('payroll.receipt', payroll.id), '_blank');
};

const openReportModal = (reportType) => {
    selectedReport.value = reportType;
    showReportModal.value = true;
};

// Resize handler
const handleResize = () => {
    if (window.innerWidth >= 1024 && showSection.value === null) {
        showSection.value = 'payrolls';
    }
};

onMounted(() => {
    window.addEventListener('resize', handleResize);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', handleResize);
});
</script>

<template>
  <Head title="Gestión de Nóminas" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
        <Icon name="BanknotesIcon" class="w-6 h-6 text-green-600 dark:text-green-400" />
        Gestión de Nóminas
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-6">

          <!-- ========= MENÚ LATERAL ========= -->
          <aside :class="['w-full lg:w-1/4', { hidden: isMobile() && showSection !== null }]">
            <nav class="space-y-2">
              <button
                @click="showSection = 'payrolls'"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-2 rounded-lg text-left transition',
                  showSection === 'payrolls'
                    ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 font-semibold border-l-4 border-green-600 dark:border-green-400'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
                ]"
              >
                <Icon name="ListBulletIcon" class="w-5 h-5" />
                Nóminas
              </button>

              <button
                v-if="permissions['Calcular Nóminas']"
                @click="showSection = 'calculate'"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-2 rounded-lg text-left transition',
                  showSection === 'calculate'
                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 font-semibold border-l-4 border-blue-600 dark:border-blue-400'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
                ]"
              >
                <Icon name="CalculatorIcon" class="w-5 h-5" />
                Cálculo Automático
              </button>

              <button
                v-if="permissions['Generar Reportes']"
                @click="showSection = 'reports'"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-2 rounded-lg text-left transition',
                  showSection === 'reports'
                    ? 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 font-semibold border-l-4 border-purple-600 dark:border-purple-400'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
                ]"
              >
                <Icon name="DocumentChartBarIcon" class="w-5 h-5" />
                Reportes
              </button>
            </nav>
          </aside>

          <!-- ========= CONTENIDO ========= -->
          <main :class="['w-full lg:w-3/4', { hidden: isMobile() && showSection === null }]">

            <!-- --------------- NÓMINAS --------------- -->
            <div v-if="showSection === 'payrolls' && permissions['Ver Nóminas']">
              <div v-if="isMobile()" class="flex items-center gap-2 mb-4">
                <button @click="showSection = null" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                  <Icon name="ArrowLeftIcon" class="w-5 h-5" /> Volver
                </button>
              </div>

              <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 ml-2">Listado de Nóminas</h4>
                <Button v-if="permissions['Crear Nóminas']" variant="primary" @click="showCreatePayroll = true">
                  <Icon name="PlusIcon" class="w-5 h-5" /> Nueva Nómina
                </Button>
              </div>

              <SearchInput v-model="search" placeholder="Buscar por empleado, código o período..." />

              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-slate-800">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Empleado</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Período</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Salario Base</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Descuentos</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Neto a Pagar</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="payroll in filteredPayrolls" :key="payroll.id" 
                        class="hover:bg-gray-50 dark:hover:bg-slate-800 transition cursor-pointer"
                        @mouseenter="(e) => onMouseEnter(e, getPayrollTooltipData(payroll))"
                        @mouseleave="onMouseLeave"
                        @touchstart="(e) => onTouchStart(e, getPayrollTooltipData(payroll))"
                        @touchend="onTouchEnd"
                        @touchmove="onTouchMove">
                      
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                          <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                              {{ payroll.user.nombre }} {{ payroll.user.apellido }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ payroll.user.codigo }}</p>
                          </div>
                        </div>
                      </td>
                      
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                        {{ payroll.periodo }}<br>
                        <span class="text-xs text-gray-500">{{ payroll.fecha_inicio }} al {{ payroll.fecha_fin }}</span>
                      </td>
                      
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        ${{ Number(payroll.salario_base).toFixed(2) }}
                      </td>
                      
                      <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="text-red-600 dark:text-red-400">
                          -${{ (Number(payroll.total_descuentos_retraso) + Number(payroll.total_descuentos_falta)).toFixed(2) }}
                        </div>
                        <div class="text-xs text-gray-500">
                          Retraso: ${{ Number(payroll.total_descuentos_retraso).toFixed(2) }}<br>
                          Falta: ${{ Number(payroll.total_descuentos_falta).toFixed(2) }}
                        </div>
                      </td>
                      
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600 dark:text-green-400">
                        ${{ Number(payroll.neto_a_pagar).toFixed(2) }}
                      </td>
                      
                      <td class="px-6 py-4 whitespace-nowrap">
                        <Badge :type="getBadgeType(payroll.estado)">
                          {{ payroll.estado }}
                        </Badge>
                      </td>
                      
                      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <!-- Calcular -->
                        <button v-if="permissions['Calcular Nóminas'] && payroll.estado === 'Borrador'"
                                @click.stop="calculatePayroll(payroll)"
                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 mr-3"
                                title="Calcular descuentos">
                          <Icon name="CalculatorIcon" class="w-5 h-5" />
                        </button>
                        
                        <!-- Aprobar -->
                        <button v-if="permissions['Aprobar Nóminas'] && payroll.estado === 'Calculado'"
                                @click.stop="approvePayroll(payroll)"
                                class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-900 mr-3"
                                title="Aprobar nómina">
                          <Icon name="CheckCircleIcon" class="w-5 h-5" />
                        </button>
                        
                        <!-- Pagar -->
                        <button v-if="permissions['Registrar Pagos'] && payroll.estado === 'Aprobado'"
                                @click.stop="payPayroll(payroll)"
                                class="text-green-600 dark:text-green-400 hover:text-green-900 mr-3"
                                title="Registrar pago">
                          <Icon name="CreditCardIcon" class="w-5 h-5" />
                        </button>
                        
                        <!-- Recibo -->
                        <button v-if="permissions['Generar Recibos'] && payroll.estado === 'Pagado'"
                                @click.stop="generateReceipt(payroll)"
                                class="text-purple-600 dark:text-purple-400 hover:text-purple-900 mr-3"
                                title="Generar recibo">
                          <Icon name="DocumentTextIcon" class="w-5 h-5" />
                        </button>
                        
                        <!-- Editar -->
                        <button v-if="permissions['Editar Nóminas'] && payroll.estado === 'Borrador'"
                                @click.stop="editPayroll(payroll)"
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 mr-3"
                                title="Editar">
                          <Icon name="PencilIcon" class="w-5 h-5" />
                        </button>
                        
                        <!-- Eliminar -->
                        <button v-if="permissions['Eliminar Nóminas'] && (payroll.estado === 'Borrador' || payroll.estado === 'Calculado')"
                                @click.stop="deletePayroll(payroll)"
                                class="text-red-600 dark:text-red-400 hover:text-red-900"
                                title="Eliminar">
                          <Icon name="TrashIcon" class="w-5 h-5" />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Paginación -->
              <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                  Mostrando {{ payrolls.from }}-{{ payrolls.to }} de {{ payrolls.total }}
                </div>
                <div class="flex gap-2">
                  <Button v-if="payrolls.prev_page_url" @click="router.visit(payrolls.prev_page_url)" variant="secondary">
                    Anterior
                  </Button>
                  <Button v-if="payrolls.next_page_url" @click="router.visit(payrolls.next_page_url)" variant="secondary">
                    Siguiente
                  </Button>
                </div>
              </div>
            </div>

            <!-- --------------- CÁLCULO AUTOMÁTICO --------------- -->
            <div v-else-if="showSection === 'calculate' && permissions['Calcular Nóminas']">
              <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
                <h4 class="text-lg font-semibold mb-4">Cálculo Automático de Nóminas</h4>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                  Selecciona un período para calcular todas las nóminas automáticamente.
                </p>
                
                <form @submit.prevent="router.post(route('payroll.calculate-all'), { periodo: periodo })">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Período (Ej: 2025-11-Q1)
                      </label>
                      <input v-model="periodo" type="text" 
                             class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-slate-900"
                             placeholder="2025-11-Q1" required>
                    </div>
                    <div class="flex items-end">
                      <Button type="submit" variant="primary" class="w-full">
                        <Icon name="CalculatorIcon" class="w-5 h-5 mr-2" />
                        Calcular Todas
                      </Button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <!-- --------------- REPORTES --------------- -->
            <div v-else-if="showSection === 'reports' && permissions['Generar Reportes']">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div @click="openReportModal('payroll-summary')"
                     class="bg-white dark:bg-slate-800 rounded-xl shadow-md p-6 cursor-pointer hover:shadow-lg transition-all hover:scale-105 border border-gray-100 dark:border-gray-700">
                  <div class="flex items-center gap-4">
                    <Icon name="DocumentChartBarIcon" class="w-10 h-10 text-purple-600 dark:text-purple-400" />
                    <div>
                      <h5 class="font-semibold text-gray-900 dark:text-gray-100">Resumen de Nóminas</h5>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Reporte general por período</p>
                    </div>
                  </div>
                </div>

                <div @click="openReportModal('payroll-details')"
                     class="bg-white dark:bg-slate-800 rounded-xl shadow-md p-6 cursor-pointer hover:shadow-lg transition-all hover:scale-105 border border-gray-100 dark:border-gray-700">
                  <div class="flex items-center gap-4">
                    <Icon name="DocumentTextIcon" class="w-10 h-10 text-blue-600 dark:text-blue-400" />
                    <div>
                      <h5 class="font-semibold text-gray-900 dark:text-gray-100">Detalle de Descuentos</h5>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Retrasos y faltas por empleado</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </main>
        </div>
      </div>
    </div>

    <!-- ==================== MODALES ==================== -->
    <!-- 🔴 CLAVE: v-show en lugar de v-if para evitar desmontaje -->
    <CreatePayrollModal 
      v-show="permissions['Crear Nóminas']"
      :show="showCreatePayroll"
      :users="users"
      @close="showCreatePayroll = false" 
    />

    <EditPayrollModal 
      v-show="permissions['Editar Nóminas']"
      :show="showEditPayroll"
      :payroll="selectedPayroll"
      :users="users"
      @close="showEditPayroll = false" 
    />

    <GenerateReportModal 
      :show="showReportModal"
      :report-section="selectedReport"
      @close="showReportModal = false" 
    />

    <!-- ==================== TOOLTIP ==================== -->
    <Tooltip ref="tooltipRef" :show="show" @close="hideTooltip">
      <div v-if="tooltipData?.type === 'payroll'" class="space-y-2">
        <h4 class="font-bold text-gray-900 dark:text-gray-100 pb-2 border-b border-gray-200 dark:border-gray-700">
          {{ tooltipData.data.userName }} - {{ tooltipData.data.periodo }}
        </h4>
        
        <div class="grid grid-cols-2 gap-2 text-sm">
          <div>
            <span class="text-gray-500 dark:text-gray-400">Salario Base:</span>
            <span class="ml-2 font-medium">${{ Number(tooltipData.data.salario_base).toFixed(2) }}</span>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Neto a Pagar:</span>
            <span class="ml-2 font-medium text-green-600">${{ Number(tooltipData.data.neto_a_pagar).toFixed(2) }}</span>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Días Trabajados:</span>
            <span class="ml-2">{{ tooltipData.data.dias_trabajados }}/{{ tooltipData.data.dias_laborables }}</span>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Minutos Retraso:</span>
            <span class="ml-2">{{ tooltipData.data.minutos_retraso }}</span>
          </div>
        </div>

        <div v-if="tooltipData.data.detalles.length" class="border-t pt-2">
          <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Detalles de Descuentos:</p>
          <div class="space-y-1">
            <div v-for="detalle in tooltipData.data.detalles" :key="detalle.id" 
                 class="text-xs flex justify-between">
              <span>{{ detalle.concepto }}</span>
              <span class="text-red-600">-${{ Number(detalle.total).toFixed(2) }}</span>
            </div>
          </div>
        </div>
      </div>
    </Tooltip>
  </AuthenticatedLayout>
</template>