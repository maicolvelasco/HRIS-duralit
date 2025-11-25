<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { computed, ref } from 'vue';

const props = defineProps({
  show: Boolean,
  reportSection: String
});

const emit = defineEmits(['close']);
const isGenerating = ref(false);

const form = useForm({
  section: props.reportSection,
  format: 'excel',
  date_range: 'all',
  start_date: '',
  end_date: '',
});

const title = computed(() => {
  const sections = {
    users: 'Personas',
    roles: 'Roles',
    permissions: 'Permisos',
    branches: 'Sucursales',
    groups: 'Grupos',
    sections: 'Secciones',
    authorizations: 'Permisos de Trabajo',
    shifts: 'Turnos',
    holidays: 'Feriados'
  };
  return `Generar Reporte: ${sections[props.reportSection] || ''}`;
});

async function submit() {
  form.section = props.reportSection;
  isGenerating.value = true;
  
  try {
    // ✅ CREAR FORMULARIO TEMPORAL (método más seguro)
    const formElement = document.createElement('form');
    formElement.method = 'GET';
    formElement.action = route('settings.reports.generate');
    formElement.target = '_blank'; // ✅ IMPORTANTE: Evita que Inertia lo maneje
    
    // ✅ AÑADIR TODOS LOS PARÁMETROS
    const params = {
      section: form.section,
      format: form.format,
      date_range: form.date_range,
      start_date: form.start_date,
      end_date: form.end_date,
      _token: document.querySelector('meta[name="csrf-token"]')?.content || ''
    };
    
    Object.keys(params).forEach(key => {
      if (params[key]) { // Solo añadir si tiene valor
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = params[key];
        formElement.appendChild(input);
      }
    });
    
    document.body.appendChild(formElement);
    formElement.submit();
    document.body.removeChild(formElement);
    
    // ✅ Cerrar modal después de iniciar descarga
    setTimeout(() => {
      close();
    }, 1000);
    
  } catch (error) {
    console.error('❌ Error al generar reporte:', error);
    alert('Error al generar el reporte. Revisa la consola para más detalles.');
  } finally {
    setTimeout(() => {
      isGenerating.value = false;
    }, 2000);
  }
}

function close() {
  form.reset();
  isGenerating.value = false;
  emit('close');
}
</script>

<template>
  <Modal :show="show" :closeable="!isGenerating" @close="close" max-width="2xl">
    <form @submit.prevent="submit" class="p-6">
      <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-6 flex items-center gap-2">
        <Icon name="DocumentArrowDownIcon" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
        {{ title }}
      </h3>

      <!-- Formato -->
      <div class="mb-6">
        <InputLabel value="Formato de exportación" />
        <div class="mt-3 grid grid-cols-2 gap-3">
          <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-800"
                 :class="form.format === 'excel' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600'">
            <input type="radio" v-model="form.format" value="excel" class="text-blue-600">
            <Icon name="TableCellsIcon" class="w-5 h-5 text-green-600" />
            <span class="font-medium">Excel</span>
          </label>
          <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-800"
                 :class="form.format === 'pdf' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600'">
            <input type="radio" v-model="form.format" value="pdf" class="text-blue-600">
            <Icon name="DocumentTextIcon" class="w-5 h-5 text-red-600" />
            <span class="font-medium">PDF</span>
          </label>
        </div>
      </div>

      <!-- Rango de fechas SOLO para Turnos -->
      <div v-if="props.reportSection === 'shifts'" class="mb-6">
        <InputLabel value="Rango de fechas" />
        <div class="mt-3 grid grid-cols-2 gap-3">
          <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-800"
                 :class="form.date_range === 'all' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600'">
            <input type="radio" v-model="form.date_range" value="all" class="text-blue-600">
            <Icon name="ListBulletIcon" class="w-5 h-5 text-gray-600" />
            <span class="font-medium">Todos los registros</span>
          </label>
          <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-800"
                 :class="form.date_range === 'between' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600'">
            <input type="radio" v-model="form.date_range" value="between" class="text-blue-600">
            <Icon name="CalendarIcon" class="w-5 h-5 text-purple-600" />
            <span class="font-medium">Entre fechas</span>
          </label>
        </div>
      </div>

      <!-- Fechas -->
      <div v-if="form.date_range === 'between' && props.reportSection === 'shifts'" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div>
          <InputLabel value="Fecha inicio" />
          <TextInput type="date" v-model="form.start_date" class="w-full mt-1" />
          <InputError :message="form.errors.start_date" />
        </div>
        <div>
          <InputLabel value="Fecha fin" />
          <TextInput type="date" v-model="form.end_date" class="w-full mt-1" />
          <InputError :message="form.errors.end_date" />
        </div>
      </div>

      <!-- Acciones -->
      <div class="flex items-center justify-end gap-3">
        <SecondaryButton @click="close" :disabled="isGenerating">Cancelar</SecondaryButton>
        <PrimaryButton :disabled="form.processing || isGenerating" type="submit">
          <Icon v-if="isGenerating" name="ArrowPathIcon" class="w-5 h-5 animate-spin mr-2" />
          <Icon v-else name="DocumentArrowDownIcon" class="w-5 h-5 mr-2" />
          {{ isGenerating ? 'Generando...' : 'Generar Reporte' }}
        </PrimaryButton>
      </div>
    </form>
  </Modal>
</template>