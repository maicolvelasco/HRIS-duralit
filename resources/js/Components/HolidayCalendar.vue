<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import CreateHolidayModal from '@/Components/CreateHolidayModal.vue';
import EditHolidayModal from '@/Components/EditHolidayModal.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    branches: Array,
    permissions: Object,
    locations: Object,
});

// Fecha actual y selectores
const currentDate = ref(new Date());
const selectedMonth = ref(new Date().getMonth());
const selectedYear = ref(new Date().getFullYear());

// Feriados y modales
const holidays = ref([]);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedDate = ref(null);
const selectedHoliday = ref(null);

// Días de la semana y meses
const weekDays = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
const monthNames = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

// Opciones de año (±10 años)
const yearOptions = computed(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let i = currentYear - 10; i <= currentYear + 10; i++) {
        years.push(i);
    }
    return years;
});

// Label del mes/año actual
const monthLabel = computed(() => `${monthNames[selectedMonth.value]} ${selectedYear.value}`);

// Navegación con selectores
watch([selectedMonth, selectedYear], ([newMonth, newYear]) => {
    currentDate.value = new Date(newYear, newMonth, 1);
    loadHolidays();
});

// Botones de navegación
const previousMonth = () => {
    if (selectedMonth.value === 0) {
        selectedMonth.value = 11;
        selectedYear.value--;
    } else {
        selectedMonth.value--;
    }
};

const nextMonth = () => {
    if (selectedMonth.value === 11) {
        selectedMonth.value = 0;
        selectedYear.value++;
    } else {
        selectedMonth.value++;
    }
};

// Cargar feriados del mes
const loadHolidays = async () => {
    try {
        const response = await fetch(route('settings.holidays.index', {
            month: selectedMonth.value + 1,
            year: selectedYear.value
        }));
        holidays.value = await response.json();
        console.log('✅ Feriados cargados:', holidays.value);
    } catch (error) {
        console.error('❌ Error cargando feriados:', error);
    }
};

// Generar días del mes
const calendarDays = computed(() => {
    const year = selectedYear.value;
    const month = selectedMonth.value;
    const firstDay = new Date(year, month, 1);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());

    const days = [];
    for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);
        const isCurrentMonth = date.getMonth() === month;
        const dateStr = date.toISOString().split('T')[0];
        
        days.push({
            date: date,
            dateStr: dateStr,
            isCurrentMonth: isCurrentMonth,
            dayNumber: date.getDate(),
            holidays: holidays.value.filter(h => h.fecha === dateStr)
        });
    }
    return days;
});

// Abrir modal de creación
const openCreateModal = (dateStr) => {
    if (!props.permissions['Crear Feriados']) return;
    selectedDate.value = dateStr;
    showCreateModal.value = true;
};

// Abrir modal de edición
const openEditModal = (holiday) => {
    if (!props.permissions['Modificar Feriados']) return;
    console.log('🔍 Abriendo modal de edición con:', holiday);
    selectedHoliday.value = { ...holiday }; // ✅ Clonar para evitar mutaciones
    showEditModal.value = true;
};

// ✅ Manejar creación exitosa y recargar feriados
const handleHolidayCreated = async (holiday) => {
    console.log('✅ Feriado creado, recargando lista...', holiday);
    await loadHolidays(); // Recargar feriados del servidor
    showCreateModal.value = false;
};

// ✅ Manejar actualización exitosa y recargar feriados
const handleHolidayUpdated = async (holiday) => {
    console.log('✅ Feriado actualizado, recargando lista...', holiday);
    await loadHolidays(); // Recargar feriados del servidor
    showEditModal.value = false;
};

// ✅ Manejar eliminación exitosa y recargar feriados
const handleHolidayDeleted = async (holidayId) => {
    console.log('✅ Feriado eliminado, recargando lista...', holidayId);
    await loadHolidays(); // Recargar feriados del servidor
    showEditModal.value = false;
};

onMounted(() => {
    loadHolidays();
});
</script>

<template>
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Calendario de Feriados</h3>
            
            <!-- Selectores de Mes y Año -->
            <div class="flex items-center gap-3">
                <select 
                    v-model.number="selectedMonth"
                    class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option v-for="(month, index) in monthNames" :key="index" :value="index">
                        {{ month }}
                    </option>
                </select>
                
                <select 
                    v-model.number="selectedYear"
                    class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option v-for="year in yearOptions" :key="year" :value="year">
                        {{ year }}
                    </option>
                </select>
                
                <!-- Botones de navegación -->
                <button @click="previousMonth" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition">
                    <Icon name="ChevronLeftIcon" class="w-5 h-5" />
                </button>
                <button @click="nextMonth" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition">
                    <Icon name="ChevronRightIcon" class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- Calendario -->
        <div class="grid grid-cols-7 gap-1">
            <!-- Días de la semana -->
            <div v-for="day in weekDays" :key="day" class="text-center py-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                {{ day }}
            </div>

            <!-- Días del mes -->
            <div v-for="day in calendarDays" :key="day.dateStr" 
                 class="min-h-[80px] border border-gray-200 dark:border-gray-600 p-1 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700 transition"
                 :class="{
                     'bg-gray-50 dark:bg-slate-700': !day.isCurrentMonth,
                     'bg-white dark:bg-slate-800': day.isCurrentMonth
                 }"
                 @click="openCreateModal(day.dateStr)">
                
                <!-- Número del día -->
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ day.dayNumber }}
                </div>

                <!-- Feriados -->
                <div class="space-y-1">
                    <div v-for="holiday in day.holidays" :key="holiday.id"
                         class="text-xs px-1 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 truncate cursor-pointer hover:bg-blue-200 dark:hover:bg-blue-800"
                         @click.stop="openEditModal(holiday)">
                        {{ holiday.nombre }}
                    </div>
                </div>

                <!-- Botón + para crear -->
                <div v-if="permissions['Crear Feriados'] && day.holidays.length === 0" 
                     class="opacity-0 hover:opacity-100 transition-opacity text-center">
                    <button class="text-xs text-gray-400 hover:text-blue-600">
                        +
                    </button>
                </div>
            </div>
        </div>

        <!-- Leyenda -->
        <div class="mt-4 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
            <div class="flex items-center gap-1">
                <div class="w-4 h-4 bg-blue-100 dark:bg-blue-900 rounded"></div>
                <span>Feriado</span>
            </div>
            <span>Haz clic en un día para crear feriado</span>
        </div>
    </div>

    <!-- Modal Crear -->
    <CreateHolidayModal 
        v-if="permissions['Crear Feriados']"
        :show="showCreateModal" 
        :date="selectedDate"
        :branches="branches"
        :locations="props.locations"
        @close="showCreateModal = false"
        @created="handleHolidayCreated" />

    <!-- Modal Editar -->
    <EditHolidayModal 
        v-if="permissions['Modificar Feriados'] || permissions['Eliminar Feriados']"
        :show="showEditModal"
        :holiday="selectedHoliday"
        :branches="branches"
        :locations="props.locations"
        :permissions="permissions"
        @close="showEditModal = false"
        @updated="handleHolidayUpdated"
        @deleted="handleHolidayDeleted" />
</template>