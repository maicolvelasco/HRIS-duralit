<script setup>
import { computed, ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Icon from '@/Components/Icon.vue';
import SearchSelect from '@/Components/SearchSelect.vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  authorizations: Array,
  userName: String,
});

const emit = defineEmits(['close']);

const isMobile = computed(() => window.innerWidth < 768);

// Formulario
const form = useForm({
  authorization_id: '',
  titulation_id: '',
  tipo: 'horas', // Valor por defecto: horas
  fecha_inicio: '',
  fecha_fin: '',
  hora_inicio: '',
  hora_fin: '',
  motivo: '',
});

// Títulos disponibles según autorización seleccionada
const availableTitulations = ref([]);

// Cargar titulaciones cuando cambia la autorización
watch(() => form.authorization_id, async (newAuthId) => {
  if (!newAuthId) {
    availableTitulations.value = [];
    form.titulation_id = '';
    return;
  }

  try {
    const response = await fetch(route('permissions.authorizations.titulations', newAuthId));
    const data = await response.json();
    availableTitulations.value = data.titulations || [];
    
    // Si no hay titulaciones, resetear el campo
    if (availableTitulations.value.length === 0) {
      form.titulation_id = '';
    }
  } catch (error) {
    console.error('Error al cargar titulaciones:', error);
    availableTitulations.value = [];
  }
});

// Filtrar campos según tipo
const isDias = computed(() => form.tipo === 'dias');

// Calcular cantidades automáticamente
watch([() => form.fecha_inicio, () => form.fecha_fin], ([fechaInicio, fechaFin]) => {
  if (isDias.value && fechaInicio && fechaFin) {
    const start = new Date(fechaInicio);
    const end = new Date(fechaFin);
    const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
    form.cantidad_dias = diff > 0 ? diff : 1;
  }
});

watch([() => form.hora_inicio, () => form.hora_fin], ([horaInicio, horaFin]) => {
  if (!isDias.value && horaInicio && horaFin) {
    const [h1, m1] = horaInicio.split(':').map(Number);
    const [h2, m2] = horaFin.split(':').map(Number);
    const totalMinutes = (h2 * 60 + m2) - (h1 * 60 + m1);
    form.cantidad_horas = totalMinutes > 0 ? (totalMinutes / 60).toFixed(2) : 0;
  }
});

function submit() {
  form.post(route('permissions.store'), {
    preserveScroll: true,
    onSuccess: () => close(),
  });
}

function close() {
  form.reset();
  form.tipo = 'horas'; // Resetear al valor por defecto
  availableTitulations.value = [];
  emit('close');
}
</script>

<template>
  <Modal
    :show="show"
    :closeable="true"
    @close="close"
    :max-width="isMobile ? 'full' : '3xl'"
  >
    <form @submit.prevent="submit" class="flex flex-col h-full">
      <!-- Sticky header -->
      <div class="sticky top-0 z-10 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
          <Icon name="DocumentPlusIcon" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
          Solicitar Permiso
        </h3>
        <div class="text-sm text-gray-500 dark:text-gray-400">
          {{ props.userName }}
        </div>
      </div>

      <!-- Scrollable content -->
      <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
        <!-- Autorización -->
        <div>
          <SearchSelect
            label="Tipo de Autorización"
            :options="authorizations"
            v-model="form.authorization_id"
            :error="form.errors.authorization_id"
            placeholder="Buscar autorización..."
            required
          />
        </div>

        <!-- Titulación (solo si hay titulaciones disponibles) -->
        <div v-if="availableTitulations.length > 0">
          <SearchSelect
            label="Titulación (Opcional)"
            :options="availableTitulations"
            v-model="form.titulation_id"
            :error="form.errors.titulation_id"
            placeholder="Buscar titulación..."
            clearable
          />
        </div>

        <!-- Tipo (primero, por defecto horas) -->
        <div>
          <SearchSelect
            label="Tipo de Permiso"
            :options="[
              { id: 'horas', nombre: 'Por Horas' },
              { id: 'dias', nombre: 'Por Días' }
            ]"
            v-model="form.tipo"
            :error="form.errors.tipo"
            placeholder="Seleccione tipo..."
            required
          />
        </div>

        <!-- Campos para HORAS -->
        <template v-if="!isDias">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <InputLabel value="Fecha Inicio" />
              <TextInput type="date" v-model="form.fecha_inicio" class="w-full" required />
              <InputError :message="form.errors.fecha_inicio" />
            </div>
            <div>
              <InputLabel value="Hora Inicio" />
              <TextInput type="time" v-model="form.hora_inicio" class="w-full" required />
              <InputError :message="form.errors.hora_inicio" />
            </div>
            <div>
              <InputLabel value="Hora Fin" />
              <TextInput type="time" v-model="form.hora_fin" class="w-full" required />
              <InputError :message="form.errors.hora_fin" />
            </div>
          </div>
        </template>

        <!-- Campos para DÍAS -->
        <template v-else>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel value="Fecha Inicio" />
              <TextInput type="date" v-model="form.fecha_inicio" class="w-full" required />
              <InputError :message="form.errors.fecha_inicio" />
            </div>
            <div>
              <InputLabel value="Fecha Fin" />
              <TextInput type="date" v-model="form.fecha_fin" class="w-full" required />
              <InputError :message="form.errors.fecha_fin" />
            </div>
          </div>
        </template>

        <!-- Motivo -->
        <div>
          <InputLabel value="Motivo" />
          <textarea
            v-model="form.motivo"
            class="w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-slate-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            rows="3"
            required
          ></textarea>
          <InputError :message="form.errors.motivo" />
        </div>
      </div>

      <!-- Sticky footer -->
      <div class="sticky bottom-0 z-10 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-end gap-2">
        <SecondaryButton type="button" @click="close">Cancelar</SecondaryButton>
        <PrimaryButton :disabled="form.processing">
          <Icon
            v-if="form.processing"
            name="ArrowPathIcon"
            class="w-5 h-5 animate-spin"
          />
          <span v-else>Solicitar Permiso</span>
        </PrimaryButton>
      </div>
    </form>
  </Modal>
</template>