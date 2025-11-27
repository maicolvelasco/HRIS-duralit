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
import SearchSelect from '@/Components/SearchSelect.vue';

const props = defineProps({
  show: Boolean,
  user: Object,
  branches: Array,
  groups: Array,
  sections: Array,
  roles: Array,
});

const emit = defineEmits(['close']);

const isMobile = computed(() => window.innerWidth < 768);

const form = useForm({
  id: '',
  nombre: '',
  apellido: '',
  codigo: '',
  password: '',
  foto: null,
  salario_base: '',
  frecuencia_pago: 'mensual',
  is_active: true,
  branch_id: null,
  group_id: null,
  section_id: null,
  rol_id: null,
});

const fotoPreview = ref(null);
const fotoFile = ref(null);

watch(() => props.show, (val) => {
  if (val && props.user) {
    form.id = props.user.id;
    form.nombre = props.user.nombre;
    form.apellido = props.user.apellido;
    form.codigo = props.user.codigo;
    form.password = '';
    form.salario_base = props.user.salario_base ?? '';
    form.frecuencia_pago = props.user.frecuencia_pago ?? 'mensual';
    form.is_active = Boolean(props.user.is_active);
    form.branch_id = props.user.branch_id ?? null;
    form.group_id = props.user.group_id ?? null;
    form.section_id = props.user.section_id ?? null;
    form.rol_id = props.user.rol_id ?? null;
    form.foto = null;
    fotoPreview.value = props.user.foto ? `/storage/${props.user.foto}` : null;
  }
});

function onFotoChange(e) {
  const file = e.target.files[0];
  if (file) {
    form.foto = file;
    fotoPreview.value = URL.createObjectURL(file);
  }
}

function submit() {
  form.transform((data) => ({ ...data, _method: 'PUT' }))
    .post(route('settings.personas.update', props.user.id), {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => close(),
    });
}

function close() {
  emit('close');
}
</script>

<template>
  <Modal
    :show="show"
    :closeable="true"
    @close="close"
    :max-width="isMobile ? 'full' : '3xl'"
    :scrollable="true"
  >
    <form @submit.prevent="submit" class="flex flex-col h-full">
      <!-- Sticky header -->
      <div class="sticky top-0 z-10 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
          <Icon name="PencilIcon" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
          Editar persona
        </h3>
        <label class="inline-flex items-center cursor-pointer">
          <input type="checkbox" v-model="form.is_active" class="sr-only peer" />
          <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
          <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Activo</span>
        </label>
      </div>

      <!-- Scrollable content -->
      <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
        <!-- Fila 1: Nombre y Apellido -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <InputLabel value="Nombre" />
            <TextInput 
              v-model="form.nombre" 
              class="w-full mt-1" 
              placeholder="Ej. Juan"
            />
            <InputError :message="form.errors.nombre" />
          </div>
          <div>
            <InputLabel value="Apellido" />
            <TextInput 
              v-model="form.apellido" 
              class="w-full mt-1"
              placeholder="Ej. Pérez"
            />
            <InputError :message="form.errors.apellido" />
          </div>
        </div>

        <!-- Fila 2: Código y Contraseña -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <InputLabel value="Código único" />
            <TextInput 
              v-model="form.codigo" 
              class="w-full mt-1"
              placeholder="Ej. JP001"
            />
            <InputError :message="form.errors.codigo" />
          </div>
          <div>
            <InputLabel value="Nueva contraseña (dejar vacío para mantener)" />
            <TextInput 
              type="password" 
              v-model="form.password" 
              class="w-full mt-1"
              placeholder="••••••••"
            />
            <InputError :message="form.errors.password" />
          </div>
        </div>

        <!-- Fila 3: Salario Base y Frecuencia de Pago -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <InputLabel value="Salario Base (Bs)" />
            <TextInput 
              type="number" 
              step="0.01" 
              min="0" 
              v-model="form.salario_base" 
              class="w-full mt-1" 
              placeholder="Ej. 5500.00"
            />
            <InputError :message="form.errors.salario_base" />
          </div>
          <div>
            <InputLabel value="Frecuencia de Pago" />
            <select v-model="form.frecuencia_pago" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-800 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
              <option value="mensual">Mensual</option>
              <option value="quincenal">Quincenal</option>
            </select>
            <InputError :message="form.errors.frecuencia_pago" />
          </div>
        </div>

        <!-- Foto -->
        <div>
          <InputLabel value="Foto (opcional)" />
          <label class="mt-1 flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700">
            <img v-if="fotoPreview" :src="fotoPreview" class="h-full object-cover rounded" />
            <div v-else class="text-center text-sm text-gray-500 dark:text-gray-400">
              <Icon name="PhotoIcon" class="w-8 h-8 mx-auto mb-1" />
              Haz clic o arrastra una imagen
            </div>
            <input type="file" accept="image/*" @change="onFotoChange" class="hidden" />
          </label>
          <InputError :message="form.errors.foto" />
        </div>

        <!-- Fila: Sucursal y Rol -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <SearchSelect
            label="Sucursal"
            :options="branches"
            v-model="form.branch_id"
            :error="form.errors.branch_id"
            placeholder="Buscar sucursal..."
            clearable
          />
          <SearchSelect
            label="Rol"
            :options="roles"
            v-model="form.rol_id"
            :error="form.errors.rol_id"
            placeholder="Buscar rol..."
            clearable
          />
        </div>

        <!-- Grupo y Sección -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <SearchSelect
            label="Grupo"
            :options="groups"
            v-model="form.group_id"
            :error="form.errors.group_id"
            placeholder="Buscar grupo..."
            :clearable="true"
          />
          <SearchSelect
            label="Sección"
            :options="sections"
            v-model="form.section_id"
            :error="form.errors.section_id"
            placeholder="Buscar sección..."
            :clearable="true"
          />
        </div>
      </div>

      <!-- Sticky footer -->
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