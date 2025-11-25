<script setup>
import { computed, watch, ref } from 'vue';
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
  authorization: Object,
  roles: Array,
  titulations: Array,
});

const emit = defineEmits(['close']);
const isMobile = computed(() => window.innerWidth < 768);

// Estado para crear nuevos títulos
const crearTitulation = ref(false);
const titulosNuevos = ref([{ nombre: '', descripcion: '', roles: [] }]);

const form = useForm({
  id: '',
  nombre: '',
  descripcion: '',
  roles: [],
  titulations: [], // IDs para sincronizar relación
  titulations_a_actualizar: [], // Títulos existentes con datos completos
  titulations_nuevos: [] // Nuevos títulos para crear
});

// Cargar datos cuando se abre el modal
watch(() => props.show, (showVal) => {
  if (showVal && props.authorization) {
    form.id = props.authorization.id;
    form.nombre = props.authorization.nombre;
    form.descripcion = props.authorization.descripcion;
    form.roles = props.authorization.roles?.map(r => r.id) || [];
    form.titulations = props.authorization.titulations?.map(t => t.id) || [];
    
    // Cargar títulos existentes con datos completos para editar
    form.titulations_a_actualizar = props.authorization.titulations?.map(t => ({
      id: t.id,
      nombre: t.nombre,
      descripcion: t.descripcion,
      roles: t.roles?.map(r => r.id) || []
    })) || [];
  } else if (!showVal) {
    close();
  }
}, { immediate: true });

// Funciones para TÍTULOS NUEVOS
function addTitulation() {
  titulosNuevos.value.push({ nombre: '', descripcion: '', roles: [] });
}

function removeTitulation(index) {
  titulosNuevos.value.splice(index, 1);
}

// Funciones para TÍTULOS EXISTENTES
function removeTitulationExistente(index) {
  const titId = form.titulations_a_actualizar[index].id;
  // Remover de ambos arrays
  form.titulations = form.titulations.filter(id => id !== titId);
  form.titulations_a_actualizar.splice(index, 1);
}

function submit() {
  // Preparar datos de nuevos títulos
  form.crear_titulation = crearTitulation.value;
  form.titulations_nuevos = crearTitulation.value ? titulosNuevos.value : [];
  
  form.put(route('settings.authorizations.update', form.id), {
    preserveScroll: true,
    onSuccess: () => {
      close();
    },
    onError: (errors) => {
      console.error('❌ Errores al actualizar:', errors);
    }
  });
}

function close() {
  form.reset();
  form.clearErrors();
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
          <Icon name="PencilIcon" class="w-6 h-6 text-indigo-600" />
          Editar permiso de trabajo
        </h3>
      </div>

      <!-- BODY SCROLL -->
      <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
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
          <InputLabel value="Roles asignados" />
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

        <hr class="border-gray-200 dark:border-gray-700" />

        <!-- ========== TÍTULOS EXISTENTES (EDITABLES) ========== -->
        <div v-if="form.titulations_a_actualizar.length > 0">
          <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-3">Títulos relacionados</h4>
          
          <div class="space-y-4">
            <div v-for="(tit, index) in form.titulations_a_actualizar" :key="'ex-' + index" 
                 class="border dark:border-slate-700 rounded-lg p-4 space-y-3 relative">
              <div class="flex items-center justify-between">
                <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Título</h5>
                <SecondaryButton type="button" @click="removeTitulationExistente(index)">
                  <Icon name="TrashIcon" class="w-4 h-4" /> Quitar
                </SecondaryButton>
              </div>

              <div>
                <InputLabel value="Nombre del título" />
                <TextInput v-model="tit.nombre" class="w-full mt-1" />
              </div>

              <div>
                <InputLabel value="Descripción del título" />
                <Textarea v-model="tit.descripcion" class="w-full mt-1" />
              </div>

              <div>
                <InputLabel value="Roles para este título" />
                <div class="flex flex-wrap gap-2 mt-2">
                  <label v-for="rol in roles" :key="'rol-ex-'+rol.id" class="cursor-pointer">
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
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-sm text-gray-500 dark:text-gray-400">
          No hay títulos relacionados. Puedes crear nuevos abajo.
        </div>

        <hr class="border-gray-200 dark:border-gray-700" />

        <!-- ========== CREAR NUEVOS TÍTULOS ========== -->
        <div>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="crearTitulation" class="rounded" />
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
              También crear nuevos títulos relacionados
            </span>
          </label>
        </div>

        <div v-if="crearTitulation" class="space-y-4">
          <div v-for="(tit, index) in titulosNuevos" :key="'new-'+index" 
               class="border dark:border-slate-700 rounded-lg p-4 space-y-3">
            <div class="flex items-center justify-between">
              <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Nuevo título {{ index + 1 }}</h4>
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
                <label v-for="rol in roles" :key="'rol-new-'+rol.id" class="cursor-pointer">
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

      <!-- FOOTER STICKY -->
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