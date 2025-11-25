<script setup>
import { computed, watch, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Icon from '@/Components/Icon.vue';
import SearchSelect from '@/Components/SearchSelect.vue';

const props = defineProps({
  show: Boolean,
  shift: Object,
  users: Array,
  branches: Array,
  groups: Array,
  sections: Array,
  roles: Array,
});

const emit = defineEmits(['close']);

const isMobile = computed(() => window.innerWidth < 768);

const diasSemana = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];

const form = useForm({
  nombre: '',
  jornada: 8,
  semanal: 40,
  desde: '',
  hasta: '',
  user_id: null,
  branch_id: null,
  group_id: null,
  section_id: null,
  rol_id: null,
  schedules: [],
});

// FILTROS COMPUTADOS
const usersFiltered = computed(() => {
  let u = props.users || [];
  if (form.branch_id) u = u.filter(us => us.branch_id === form.branch_id);
  if (form.group_id) u = u.filter(us => us.group_id === form.group_id);
  if (form.section_id) u = u.filter(us => us.section_id === form.section_id);
  if (form.rol_id) u = u.filter(us => us.rol_id === form.rol_id);
  if (form.user_id && !u.find(us => us.id === form.user_id)) form.user_id = null;
  return u;
});

const branchesFiltered = computed(() => {
  if (form.user_id) {
    const user = props.users.find(u => u.id === form.user_id);
    return user?.branch_id ? props.branches.filter(b => b.id === user.branch_id) : [];
  }
  return props.branches || [];
});

const groupsFiltered = computed(() => {
  if (form.user_id) {
    const user = props.users.find(u => u.id === form.user_id);
    return user?.group_id ? props.groups.filter(g => g.id === user.group_id) : [];
  }
  return props.groups || [];
});

const sectionsFiltered = computed(() => {
  if (form.user_id) {
    const user = props.users.find(u => u.id === form.user_id);
    return user?.section_id ? props.sections.filter(s => s.id === user.section_id) : [];
  }
  return props.sections || [];
});

const rolesFiltered = computed(() => {
  if (form.user_id) {
    const user = props.users.find(u => u.id === form.user_id);
    return user?.rol_id ? props.roles.filter(r => r.id === user.rol_id) : [];
  }
  return props.roles || [];
});

// HORARIOS
function addSchedule() {
  form.schedules.push({ hora_inicio:'08:00', hora_fin:'12:00', dias:[], incluye_feriados:false });
}
function removeSchedule(i) {
  form.schedules.splice(i,1);
}

// FUNCIÓN PARA CONVERTIR HORAS A FORMATO 24h (HH:MM) - VERSIÓN DEFINITIVA
function convertTo24Hour(timeStr) {
  console.log('Convirtiendo hora:', timeStr);
  
  // Si es null/undefined, retornar '00:00'
  if (!timeStr) {
    console.log('Hora vacía, usando 00:00');
    return '00:00';
  }
  
  // Limpiar espacios
  timeStr = timeStr.toString().trim();
  
  // Si ya está en formato HH:MM exacto, retornar tal cual
  if (/^\d{2}:\d{2}$/.test(timeStr)) {
    console.log('Hora ya en formato 24h:', timeStr);
    return timeStr;
  }
  
  // Si viene con segundos HH:MM:SS, extraer HH:MM
  const secondsMatch = timeStr.match(/^(\d{2}):\d{2}:\d{2}$/);
  if (secondsMatch) {
    const result = `${secondsMatch[1]}:${timeStr.substring(3,5)}`;
    console.log('Removidos segundos:', result);
    return result;
  }
  
  // Si viene con AM/PM, convertir
  const match = timeStr.match(/(\d{1,2}):(\d{2})\s*(AM|PM|am|pm)/);
  if (match) {
    let hours = parseInt(match[1]);
    const minutes = match[2];
    const period = match[3].toUpperCase();
    
    if (period === 'PM' && hours !== 12) {
      hours += 12;
    } else if (period === 'AM' && hours === 12) {
      hours = 0;
    }
    
    const result = `${hours.toString().padStart(2, '0')}:${minutes}`;
    console.log('Convertido de 12h a 24h:', result);
    return result;
  }
  
  // Último intento: extraer cualquier cosa que parezca HH:MM
  const fallbackMatch = timeStr.match(/(\d{2}):(\d{2})/);
  if (fallbackMatch) {
    const result = `${fallbackMatch[1]}:${fallbackMatch[2]}`;
    console.log('Usando fallback:', result);
    return result;
  }
  
  console.log('Formato no reconocido, usando 00:00 para:', timeStr);
  return '00:00';
}

// SUBMIT usando form.put() con transformación definitiva
function submit() {
  console.log('=== INICIANDO SUBMIT ===');
  
  // Crear copia profunda para transformar SIN mutar el estado original
  const dataToSend = {
    nombre: form.nombre,
    jornada: form.jornada,
    semanal: form.semanal,
    desde: form.desde,
    hasta: form.hasta,
    user_id: form.user_id,
    branch_id: form.branch_id,
    group_id: form.group_id,
    section_id: form.section_id,
    rol_id: form.rol_id,
    schedules: form.schedules.map((s, i) => {
      const hi = convertTo24Hour(s.hora_inicio);
      const hf = convertTo24Hour(s.hora_fin);
      
      console.log(`Horario ${i}:`, {
        original: { inicio: s.hora_inicio, fin: s.hora_fin },
        convertido: { inicio: hi, fin: hf }
      });
      
      return {
        hora_inicio: hi,
        hora_fin: hf,
        dias: s.dias,
        incluye_feriados: s.incluye_feriados
      };
    })
  };
  
  console.log('Datos FINALES a enviar:', JSON.stringify(dataToSend, null, 2));
  
  // Usar transform con form.put
  form.transform(() => dataToSend)
     .put(route('settings.shifts.update', props.shift.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
          console.log('✅ Éxito:', page);
          close();
        },
        onError: (errors) => {
          console.error('❌ Errores de validación:', errors);
        },
        onFinish: () => {
          console.log('🏁 Finalizado');
        }
      });
}

function close() {
  console.log('Cerrando modal');
  emit('close');
}

// WATCH para cargar datos
watch(() => props.show, async (val) => {
  if (val && props.shift) {
    console.log('Cargando shift:', props.shift);
    
    await nextTick();
    
    form.nombre = props.shift.nombre;
    form.jornada = props.shift.jornada;
    form.semanal = props.shift.semanal;
    form.desde = props.shift.desde;
    form.hasta = props.shift.hasta;
    form.schedules = JSON.parse(JSON.stringify(props.shift.schedules || []));
    
    // CARGAR RELACIONES
    form.user_id = props.shift.users?.[0]?.id ?? null;
    form.branch_id = props.shift.branches?.[0]?.id ?? null;
    form.group_id = props.shift.groups?.[0]?.id ?? null;
    form.section_id = props.shift.sections?.[0]?.id ?? null;
    form.rol_id = props.shift.roles?.[0]?.id ?? null;
    
    console.log('Formulario cargado:', form.data());
  } else {
    form.reset();
  }
}, { immediate: true });
</script>

<template>
  <Modal :show="show" :closeable="true" @close="close" :max-width="isMobile ? 'full' : '5xl'" :scrollable="true">
    <form @submit.prevent="submit" class="flex flex-col h-full">
      <!-- HEADER -->
      <div class="sticky top-0 z-10 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
          <Icon name="PencilIcon" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
          Editar turno
        </h3>
      </div>

      <!-- BODY -->
      <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
        <!-- nombre, jornada, semanal -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <InputLabel value="Nombre del turno" />
            <TextInput 
              v-model="form.nombre" 
              class="w-full mt-1"
              placeholder="Ej. Turno mañana"
            />
            <InputError :message="form.errors.nombre" />
          </div>
          <div>
            <InputLabel value="Jornada (horas)" />
            <TextInput type="number" v-model.number="form.jornada" class="w-full mt-1" min="1" max="24" />
            <InputError :message="form.errors.jornada" />
          </div>
          <div>
            <InputLabel value="Semanal (horas)" />
            <TextInput type="number" v-model.number="form.semanal" class="w-full mt-1" min="1" max="168" />
            <InputError :message="form.errors.semanal" />
          </div>
        </div>

        <!-- desde / hasta -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <InputLabel value="Desde" />
            <TextInput type="date" v-model="form.desde" class="w-full mt-1" />
            <InputError :message="form.errors.desde" />
          </div>
          <div>
            <InputLabel value="Hasta" />
            <TextInput type="date" v-model="form.hasta" class="w-full mt-1" />
            <InputError :message="form.errors.hasta" />
          </div>
        </div>

        <!-- selects -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <SearchSelect 
            label="Usuario" 
            :options="usersFiltered" 
            v-model="form.user_id" 
            :error="form.errors.user_id"
            placeholder="Buscar usuario..."
            :clearable="true"
          />
          <SearchSelect 
            label="Sucursal" 
            :options="branchesFiltered" 
            v-model="form.branch_id" 
            :error="form.errors.branch_id"
            placeholder="Buscar sucursal..."
            :clearable="true"
          />
          <SearchSelect 
            label="Grupo" 
            :options="groupsFiltered" 
            v-model="form.group_id" 
            :error="form.errors.group_id"
            placeholder="Buscar grupo..."
            :clearable="true"
          />
          <SearchSelect 
            label="Sección" 
            :options="sectionsFiltered" 
            v-model="form.section_id" 
            :error="form.errors.section_id"
            placeholder="Buscar sección..."
            :clearable="true"
          />
          <SearchSelect 
            label="Rol" 
            :options="rolesFiltered" 
            v-model="form.rol_id" 
            :error="form.errors.rol_id"
            placeholder="Buscar rol..."
            :clearable="true"
          />
        </div>

        <!-- horarios -->
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300">Horarios</h4>
            <SecondaryButton type="button" @click="addSchedule">Agregar horario</SecondaryButton>
          </div>

          <div v-for="(s,i) in form.schedules" :key="i" class="border dark:border-slate-700 rounded-lg p-4 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <InputLabel value="Hora inicio" />
                <TextInput type="time" v-model="s.hora_inicio" class="w-full mt-1" />
              </div>
              <div>
                <InputLabel value="Hora fin" />
                <TextInput type="time" v-model="s.hora_fin" class="w-full mt-1" />
              </div>
            </div>

            <div>
              <InputLabel value="Días" />
              <div class="flex flex-wrap gap-2 mt-2">
                <label v-for="dia in diasSemana" :key="dia" class="inline-flex items-center gap-2 text-sm">
                  <input type="checkbox" v-model="s.dias" :value="dia" class="rounded" />
                  {{ dia.charAt(0).toUpperCase() + dia.slice(1) }}
                </label>
                <label class="inline-flex items-center gap-2 text-sm ml-auto">
                  <input type="checkbox" v-model="s.incluye_feriados" class="rounded" />
                  Incluye feriados
                </label>
              </div>
            </div>

            <div class="text-right">
              <SecondaryButton type="button" @click="removeSchedule(i)">Quitar horario</SecondaryButton>
            </div>
          </div>
        </div>
      </div>

      <!-- FOOTER -->
      <div class="sticky bottom-0 z-10 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-end gap-2">
        <SecondaryButton type="button" @click="close">Cancelar</SecondaryButton>
        <PrimaryButton :disabled="form.processing">
          <Icon v-if="form.processing" name="ArrowPathIcon" class="w-5 h-5 animate-spin" />
          <span v-else>Guardar cambios</span>
        </PrimaryButton>
      </div>
    </form>
  </Modal>
</template>