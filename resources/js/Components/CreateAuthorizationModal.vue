<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import InputError from '@/Components/InputError.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({ 
  show: Boolean, 
  roles: Array 
});
const emit = defineEmits(['close']);

const isMobile = computed(() => window.innerWidth < 768);
const crearTitulation = ref(false);

// Lista de títulos nuevos
const titulosNuevos = ref([
  { nombre: '', descripcion: '', roles: [] }
]);

const form = useForm({
  nombre: '',
  descripcion: '',
  roles: [],
  titulations_nuevos: []
});

// Agregar nuevo título
function addTitulation() {
  titulosNuevos.value.push({ nombre: '', descripcion: '', roles: [] });
}

// Quitar título
function removeTitulation(index) {
  titulosNuevos.value.splice(index, 1);
}

function submit() {
  // ✅ Preparar datos solo si se seleccionó crear títulos
  if (crearTitulation.value) {
    form.titulations_nuevos = titulosNuevos.value;
  } else {
    form.titulations_nuevos = [];
  }

  form.post(route('settings.authorizations.store'), {
    preserveScroll: true,
    onSuccess: () => {
      close();
    },
    onError: (errors) => {
      // ✅ Muestra errores en consola para debugging
      console.error('Errores de validación:', errors);
      // Los errores se mostrarán automáticamente en los <InputError>
    },
    onFinish: () => {
      // ✅ Restablece el estado del processing
      form.processing = false;
    }
  });
}

function close() {
  form.reset();
  form.clearErrors(); // ✅ Limpia errores previos
  crearTitulation.value = false;
  titulosNuevos.value = [{ nombre: '', descripcion: '', roles: [] }];
  emit('close');
}
</script>

<template>
  <Modal :show="show" :closeable="true" @close="close" :max-width="isMobile ? 'full' : '3xl'" :scrollable="true">
    <form @submit.prevent="submit" class="flex flex-col h-full">
      <!-- HEADER -->
      <div class="sticky top-0 z-10 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
          <Icon name="PlusIcon" class="w-6 h-6 text-indigo-600" />
          Crear permiso de trabajo
        </h3>
      </div>

      <!-- BODY -->
      <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
        <!-- Mensaje de error general -->
        <div v-if="form.errors.general" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-3">
          <p class="text-sm text-red-600 dark:text-red-400">{{ form.errors.general }}</p>
        </div>

        <!-- Nombre -->
        <div>
          <InputLabel value="Nombre del permiso" />
          <TextInput v-model="form.nombre" class="w-full mt-1" />
          <InputError :message="form.errors.nombre" />
        </div>

        <!-- Descripción -->
        <div>
          <InputLabel value="Descripción" />
          <Textarea v-model="form.descripcion" class="w-full mt-1" />
          <InputError :message="form.errors.descripcion" />
        </div>

        <!-- Roles -->
        <div>
          <InputLabel value="Roles para este permiso" />
          <div class="flex flex-wrap gap-2 mt-2">
            <label v-for="rol in roles" :key="rol.id" class="cursor-pointer">
              <input type="checkbox" :value="rol.id" v-model="form.roles" class="sr-only" />
              <span :class="[
                'inline-flex items-center px-3 py-2 rounded-lg border text-sm font-medium transition',
                form.roles.includes(rol.id)
                  ? 'bg-indigo-600 text-white border-indigo-600'
                  : 'bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-200 dark:hover:bg-slate-600'
              ]">
                {{ rol.nombre }}
              </span>
            </label>
          </div>
          <InputError :message="form.errors.roles" />
        </div>

        <hr />

        <!-- Checkbox para crear títulos -->
        <div>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="crearTitulation" class="rounded" />
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
              También crear títulos relacionados
            </span>
          </label>
        </div>

        <!-- Formularios de títulos -->
        <div v-if="crearTitulation" class="space-y-4">
          <div v-for="(tit, index) in titulosNuevos" :key="index" class="border dark:border-slate-700 rounded-lg p-4 space-y-3">
            <div class="flex items-center justify-between">
              <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Título {{ index + 1 }}</h4>
              <SecondaryButton type="button" @click="removeTitulation(index)">Quitar</SecondaryButton>
            </div>

            <div>
              <InputLabel value="Nombre del título" />
              <TextInput v-model="tit.nombre" class="w-full mt-1" />
              <InputError :message="form.errors[`titulations_nuevos.${index}.nombre`]" />
            </div>

            <div>
              <InputLabel value="Descripción del título" />
              <Textarea v-model="tit.descripcion" class="w-full mt-1" />
              <InputError :message="form.errors[`titulations_nuevos.${index}.descripcion`]" />
            </div>

            <div>
              <InputLabel value="Roles para este título" />
              <div class="flex flex-wrap gap-2 mt-2">
                <label v-for="rol in roles" :key="rol.id" class="cursor-pointer">
                  <input type="checkbox" :value="rol.id" v-model="tit.roles" class="sr-only" />
                  <span :class="[
                    'inline-flex items-center px-3 py-2 rounded-lg border text-sm font-medium transition',
                    tit.roles.includes(rol.id)
                      ? 'bg-emerald-600 text-white border-emerald-600'
                      : 'bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-200 dark:hover:bg-slate-600'
                  ]">
                    {{ rol.nombre }}
                  </span>
                </label>
              </div>
              <InputError :message="form.errors[`titulations_nuevos.${index}.roles`]" />
            </div>
          </div>

          <SecondaryButton type="button" @click="addTitulation">Agregar otro título</SecondaryButton>
        </div>
      </div>

      <!-- FOOTER -->
      <div class="sticky bottom-0 z-10 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-end gap-2">
        <SecondaryButton type="button" @click="close">Cancelar</SecondaryButton>
        <PrimaryButton :disabled="form.processing" type="submit">
          <Icon v-if="form.processing" name="ArrowPathIcon" class="w-5 h-5 animate-spin" />
          <span v-else>Crear Permiso de Trabajo</span>
        </PrimaryButton>
      </div>
    </form>
  </Modal>
</template>