<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  permission: { type: Object, default: null },
  horasDisponibles: { type: Number, default: 0 }, // ← NUEVO PROP
  authorizations: Array, // ← NUEVO PROP
});

const emit = defineEmits(['close']);

const isMobile = computed(() => window.innerWidth < 768);

const form = useForm({
  tipo: 'horas',
  fecha_inicio: '',
  fecha_fin: '',
  hora_inicio: '',
  hora_fin: '',
  motivo: '',
  authorization_id: null,  // ← Agrega esto
  titulation_id: null,      // ← Agrega esto
});

// Estados para compensaciones
const isCompensacion = ref(false);
const horasSolicitadas = computed(() => {
  if (!isCompensacion.value) return 0;
  if (form.tipo !== 'horas' || !form.hora_inicio || !form.hora_fin) return 0;
  
  const [h1, m1] = form.hora_inicio.split(':').map(Number);
  const [h2, m2] = form.hora_fin.split(':').map(Number);
  const totalMinutes = (h2 * 60 + m2) - (h1 * 60 + m1);
  return totalMinutes > 0 ? (totalMinutes / 60).toFixed(2) : 0;
});

const tieneHorasSuficientes = computed(() => {
  if (!isCompensacion.value) return true;
  return parseFloat(horasSolicitadas.value) <= props.horasDisponibles;
});

const isDias = computed(() => form.tipo === 'dias');

// Cargar datos cuando se abre el modal
watch(() => props.show, (newVal) => {
  if (newVal && props.permission) {
    // Detectar si es compensación
    isCompensacion.value = props.permission.authorization?.nombre === 'Compensacion';
    
    // Si es compensación, forzar tipo horas
    if (isCompensacion.value) {
      form.tipo = 'horas';
    } else {
      form.tipo = props.permission.tipo || 'horas';
    }
    
    // Cargar fechas
    const loadDate = (date) => {
      if (!date) return '';
      if (typeof date === 'object' && date.date) date = date.date;
      return typeof date === 'string' ? date.split(' ')[0] : '';
    };
    
    form.fecha_inicio = loadDate(props.permission.fecha_inicio);
    form.fecha_fin = loadDate(props.permission.fecha_fin);
    form.hora_inicio = props.permission.hora_inicio ? props.permission.hora_inicio.substring(0, 5) : '';
    form.hora_fin = props.permission.hora_fin ? props.permission.hora_fin.substring(0, 5) : '';
    form.motivo = props.permission.motivo || '';
    form.authorization_id = props.permission.authorization_id; // ← Agrega esto
    form.titulation_id = props.permission.titulation_id;
  } else {
    form.reset();
    form.tipo = 'horas';
    isCompensacion.value = false;
  }
});

// Calcular cantidad mostrada
const cantidadCalculada = computed(() => {
  if (isDias.value && form.fecha_inicio && form.fecha_fin) {
    const start = new Date(form.fecha_inicio);
    const end = new Date(form.fecha_fin);
    const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
    return diff > 0 ? `${diff} días` : '-';
  } else if (!isDias.value && form.hora_inicio && form.hora_fin) {
    return `${horasSolicitadas.value} horas`;
  }
  return '-';
});

// Formatear datos de solo lectura
const permissionData = computed(() => {
  if (!props.permission) return null;
  
  let rejectedBy = 'N/A';
  if (props.permission.report) {
    rejectedBy = `${props.permission.report.nombre} ${props.permission.report.apellido}`;
    if (props.permission.report.codigo) {
      rejectedBy += ` (${props.permission.report.codigo})`;
    }
  }
  
  const formatDate = (date) => {
    if (!date) return '-';
    let dateStr = date;
    if (typeof date === 'object' && date.date) dateStr = date.date;
    if (typeof dateStr === 'string') {
      const [year, month, day] = dateStr.split(' ')[0].split('-');
      return `${day}/${month}/${year}`;
    }
    return '-';
  };
  
  return {
    id: props.permission.id,
    authorization: props.permission.authorization?.nombre || 'N/A',
    titulation: props.permission.titulation?.nombre || 'Sin titulación',
    estado: props.permission.estado,
    rejected_by: rejectedBy,
    observaciones: props.permission.observaciones || 'Sin observaciones',
    fecha_inicio: formatDate(props.permission.fecha_inicio),
  };
});

function submit() {
  // Validación adicional para compensaciones
  if (isCompensacion.value && !tieneHorasSuficientes.value) {
    form.setError('hora_fin', `No tienes suficientes horas disponibles. Tienes ${props.horasDisponibles} hrs, solicitas ${horasSolicitadas.value} hrs.`);
    return;
  }

  // Preparar datos adicionales que faltan
  const formData = {
    ...form.data(),
    // Asegurar que se envíen los IDs si existen
    authorization_id: props.permission?.authorization_id,
    titulation_id: props.permission?.titulation_id,
  };

  // Enviar la petición PUT al servidor
  form.put(route('permissions.update', props.permission.id), {
    data: formData,
    onSuccess: () => {
      close(); // ✅ Cerrar modal al tener éxito
    },
    onError: (errors) => {
      console.error('Error al reenviar:', errors);
      // Los errores se mostrarán automáticamente en los componentes InputError
    }
  });
}

function close() {
  form.reset();
  form.tipo = 'horas';
  isCompensacion.value = false;
  emit('close');
}
</script>

<template>
  <Modal
    :show="show"
    :closeable="true"
    @close="close"
    :max-width="isMobile ? 'full' : '4xl'"
  >
    <form @submit.prevent="submit" class="flex flex-col h-full max-h-[90vh]">
      <!-- Header -->
      <div class="sticky top-0 z-10 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 px-6 py-4">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
            <Icon name="PencilSquareIcon" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
            Editar Permiso Rechazado
          </h3>
        </div>
      </div>

      <!-- Scrollable content -->
      <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
        <template v-if="permissionData">
          <!-- Cuadro de Horas Disponibles (solo para Compensación) -->
          <div v-if="isCompensacion" class="bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-400 p-4 rounded-lg">
            <div class="flex items-center gap-2 mb-2">
              <Icon name="ClockIcon" class="w-5 h-5 text-blue-600 dark:text-blue-300" />
              <h4 class="font-semibold text-blue-800 dark:text-blue-200">Horas de Compensación Disponibles</h4>
            </div>
            <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">
              {{ props.horasDisponibles.toFixed(2) }} horas
            </p>
            <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
              Las horas se restarán automáticamente de tus compensaciones disponibles.
            </p>
          </div>

          <!-- Alerta de rechazo -->
          <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-400 p-4 rounded">
            <div class="flex">
              <div class="flex-shrink-0">
                <Icon name="XCircleIcon" class="h-5 w-5 text-red-400" />
              </div>
              <div class="ml-3 w-full space-y-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                  <div>
                    <span class="font-medium text-red-800 dark:text-red-300">Rechazado por:</span>
                    <span class="text-red-700 dark:text-red-200 ml-1">{{ permissionData.rejected_by }}</span>
                  </div>
                  <div>
                    <span class="font-medium text-red-800 dark:text-red-300">Fecha Inicio:</span>
                    <span class="text-red-700 dark:text-red-200 ml-1">{{ permissionData.fecha_inicio }}</span>
                  </div>
                </div>
                <div>
                  <span class="font-medium text-red-800 dark:text-red-300 block mb-1">Motivo de Rechazo:</span>
                  <p class="text-red-700 dark:text-red-200">{{ permissionData.observaciones }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Info solo lectura -->
          <div class="bg-gray-50 dark:bg-slate-700/50 rounded-lg p-4 space-y-3">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-slate-600 pb-2">
              Información del Permiso Original
            </h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
              <div>
                <span class="font-medium text-gray-600 dark:text-gray-400">Autorización:</span>
                <p class="text-gray-900 dark:text-gray-100 mt-1">{{ permissionData.authorization }}</p>
              </div>
              <div>
                <span class="font-medium text-gray-600 dark:text-gray-400">Titulación:</span>
                <p class="text-gray-900 dark:text-gray-100 mt-1">{{ permissionData.titulation }}</p>
              </div>
              <div>
                <span class="font-medium text-gray-600 dark:text-gray-400">Estado:</span>
                <span class="inline-block mt-1 px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                  {{ permissionData.estado }}
                </span>
              </div>
            </div>
          </div>

          <!-- TIPO -->
          <div>
            <InputLabel value="Tipo de Permiso *" />
            <select
              v-model="form.tipo"
              class="w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-slate-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              required
              :disabled="isCompensacion"
            >
              <option value="horas">Por Horas</option>
              <option value="dias">Por Días</option>
            </select>
            <InputError :message="form.errors.tipo" class="mt-1" />
            <p v-if="isCompensacion" class="text-xs text-blue-600 dark:text-blue-400 mt-1">
              Los permisos de compensación solo pueden ser por horas.
            </p>
          </div>

          <!-- Campos HORAS -->
          <template v-if="!isDias">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <InputLabel value="Fecha *" />
                <TextInput type="date" v-model="form.fecha_inicio" class="w-full" required />
                <InputError :message="form.errors.fecha_inicio" class="mt-1" />
              </div>
              <div>
                <InputLabel value="Hora Inicio *" />
                <TextInput type="time" v-model="form.hora_inicio" class="w-full" required />
                <InputError :message="form.errors.hora_inicio" class="mt-1" />
              </div>
              <div>
                <InputLabel value="Hora Fin *" />
                <TextInput type="time" v-model="form.hora_fin" class="w-full" required />
                <InputError :message="form.errors.hora_fin" class="mt-1" />
              </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              La fecha de inicio será usada como fecha del permiso por horas
            </p>
            
            <!-- Mensaje de error de horas insuficientes -->
            <div v-if="isCompensacion && !tieneHorasSuficientes" class="text-sm text-red-600 dark:text-red-400">
              <Icon name="ExclamationCircleIcon" class="w-4 h-4 inline mr-1" />
              No tienes suficientes horas disponibles.
            </div>
          </template>

          <!-- Campos DÍAS -->
          <template v-else>
            <div class="space-y-4">
              <div>
                <InputLabel value="Fecha Inicio *" />
                <TextInput type="date" v-model="form.fecha_inicio" class="w-full" required />
                <InputError :message="form.errors.fecha_inicio" class="mt-1" />
              </div>
              
              <!-- Solo mostrar fecha fin si existe en el permiso original -->
              <div v-if="permission.fecha_fin">
                <InputLabel value="Fecha Fin *" />
                <TextInput type="date" v-model="form.fecha_fin" class="w-full" required />
                <InputError :message="form.errors.fecha_fin" class="mt-1" />
              </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              Las horas no se usan en permisos por días
            </p>
          </template>

          <!-- Cantidad calculada -->
          <div v-if="cantidadCalculada !== '-'" class="text-sm text-indigo-600 dark:text-indigo-400 font-medium">
            📊 Cantidad calculada: {{ cantidadCalculada }}
          </div>

          <!-- MOTIVO -->
          <div>
            <InputLabel value="Motivo del Permiso *" />
            <textarea
              v-model="form.motivo"
              class="w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-slate-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              rows="4"
              required
              placeholder="Describe el motivo de tu permiso..."
            ></textarea>
            <InputError :message="form.errors.motivo" class="mt-1" />
          </div>
        </template>

        <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
          Cargando datos...
        </div>
      </div>

      <!-- Footer -->
      <div class="sticky bottom-0 z-10 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-end gap-3">
        <SecondaryButton type="button" @click="close">
          Cancelar
        </SecondaryButton>
        <PrimaryButton 
          :disabled="form.processing || (isCompensacion && !tieneHorasSuficientes)" 
          class="bg-orange-500 hover:bg-orange-600 dark:bg-orange-600 dark:hover:bg-orange-700"
        >
          <Icon
            v-if="form.processing"
            name="ArrowPathIcon"
            class="w-5 h-5 animate-spin mr-2"
          />
          <span>{{ form.processing ? 'Enviando...' : 'Reenviar Permiso' }}</span>
        </PrimaryButton>
      </div>
    </form>
  </Modal>
</template>