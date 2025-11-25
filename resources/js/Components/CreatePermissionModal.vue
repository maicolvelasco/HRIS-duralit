<script setup>
import { computed } from 'vue';
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
const emit  = defineEmits(['close']);
const isMobile = computed(() => window.innerWidth < 768);

const form = useForm({
  nombre: '',
  descripcion: '',
  roles: [], // Array de IDs de roles seleccionados
});

function submit() {
  form.post(route('settings.permissions.store'), {
    preserveScroll: true,
    onSuccess: () => close(),
  });
}

function close() { 
  form.reset();
  emit('close');
}
</script>

<template>
  <Modal :show="show" :closeable="true" @close="close" :max-width="isMobile ? 'full' : '3xl'">
    <form @submit.prevent="submit" class="flex flex-col h-full">
      <!-- Sticky header -->
      <div class="sticky top-0 z-10 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
          <Icon name="PlusIcon" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
          Crear nuevo permiso
        </h3>
      </div>

      <!-- Scrollable content -->
      <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
        <!-- Nombre -->
        <div>
          <InputLabel value="Nombre" />
          <TextInput v-model="form.nombre" class="w-full mt-1" placeholder="Ej. usuarios.vista" />
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Usa formato: modulo.accion</p>
          <InputError :message="form.errors.nombre" />
        </div>

        <!-- Descripción -->
        <div>
          <InputLabel value="Descripción" />
          <Textarea v-model="form.descripcion" class="w-full mt-1" placeholder="Explica qué hace este permiso" />
          <InputError :message="form.errors.descripcion" />
        </div>

        <!-- Roles - Botoncitos -->
        <div>
          <InputLabel value="Asignar a roles" />
          <div class="mt-2 flex flex-wrap gap-2">
            <label
              v-for="rol in roles"
              :key="rol.id"
              class="cursor-pointer"
            >
              <input
                type="checkbox"
                :value="rol.id"
                v-model="form.roles"
                class="sr-only"
              />
              <span
                :class="[
                  'inline-flex items-center px-3 py-2 rounded-lg border text-sm font-medium transition',
                  form.roles.includes(rol.id)
                    ? 'bg-indigo-600 text-white border-indigo-600'
                    : 'bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-200 dark:hover:bg-slate-600'
                ]"
              >
                {{ rol.nombre }}
              </span>
            </label>
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Selecciona uno o más roles</p>
          <InputError :message="form.errors.roles" />
        </div>
      </div>

      <!-- Sticky footer -->
      <div class="sticky bottom-0 z-10 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-end gap-2">
        <SecondaryButton type="button" @click="close">Cancelar</SecondaryButton>
        <PrimaryButton :disabled="form.processing">
          <Icon v-if="form.processing" name="ArrowPathIcon" class="w-5 h-5 animate-spin" />
          <span v-else>Crear permiso</span>
        </PrimaryButton>
      </div>
    </form>
  </Modal>
</template>