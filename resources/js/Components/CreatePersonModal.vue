<script setup>
import { computed, ref } from 'vue';
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
  show: { type: Boolean, default: false },
  branches: Array,
  groups: Array,
  sections: Array,
  roles: Array,
});

const emit = defineEmits(['close']);

const isMobile = computed(() => window.innerWidth < 768);

const form = useForm({
  nombre: '',
  apellido: '',
  codigo: '',
  password: '',
  foto: null,
  is_active: true,
  branch_id: null,
  group_id: null,
  section_id: null,
  rol_id: null,
});

const fotoPreview = ref(null);

function onFotoChange(e) {
  const file = e.target.files[0];
  if (file) {
    form.foto = file;
    fotoPreview.value = URL.createObjectURL(file);
  }
}

function submit() {
  form.post(route('settings.personas.store'), {
    preserveScroll: true,
    onSuccess: () => close(),
  });
}

function close() {
  form.reset();
  fotoPreview.value = null;
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
          <Icon name="UserPlusIcon" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
          Crear nueva persona
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
            <TextInput v-model="form.nombre" class="w-full mt-1" placeholder="Ej. Juan" />
            <InputError :message="form.errors.nombre" />
          </div>
          <div>
            <InputLabel value="Apellido" />
            <TextInput v-model="form.apellido" class="w-full mt-1" placeholder="Ej. Pérez" />
            <InputError :message="form.errors.apellido" />
          </div>
        </div>

        <!-- Fila 2: Código y Contraseña -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <InputLabel value="Código único" />
            <TextInput v-model="form.codigo" class="w-full mt-1" placeholder="Ej. JP001" />
            <InputError :message="form.errors.codigo" />
          </div>
          <div>
            <InputLabel value="Contraseña temporal" />
            <TextInput type="password" v-model="form.password" class="w-full mt-1" />
            <InputError :message="form.errors.password" />
          </div>
        </div>

        <!-- Foto -->
        <div>
          <InputLabel value="Foto (opcional)" />
          <label
            class="mt-1 flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700"
          >
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

        <!-- Grupo y Sección en la misma fila en PC -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <SearchSelect
            label="Grupo"
            :options="groups"
            v-model="form.group_id"
            :error="form.errors.group_id"
            placeholder="Buscar grupo..."
            clearable
          />
          <SearchSelect
            label="Sección"
            :options="sections"
            v-model="form.section_id"
            :error="form.errors.section_id"
            placeholder="Buscar sucursal..."
            clearable
          />
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
          <span v-else>Crear persona</span>
        </PrimaryButton>
      </div>
    </form>
  </Modal>
</template>