<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
  show:   Boolean,
  branch: Object, // {id,nombre,departamento,provincia,localidad}
});

const emit = defineEmits(['close']);

const isMobile = computed(() => window.innerWidth < 768);

const form = useForm({
  id:           '',
  nombre:       '',
  departamento: '',
  provincia:    '',
  localidad:    '',
});

// Cargar datos SIEMPRE que se abra el modal
watch(() => props.show, (val) => {
  if (val && props.branch) {
    form.id           = props.branch.id;
    form.nombre       = props.branch.nombre;
    form.departamento = props.branch.departamento;
    form.provincia    = props.branch.provincia;
    form.localidad    = props.branch.localidad;
  }
});

function submit() {
  form.put(route('settings.branches.update', props.branch.id), {
    preserveScroll: true,
    onSuccess: () => close(),
  });
}

function close() {
  emit('close');
  // ❌ NO hagas form.reset() aquí
}
</script>

<template>
  <Modal
    :show="show"
    :closeable="true"
    @close="close"
    :max-width="isMobile ? 'full' : '3xl'"
  >
    <form @submit.prevent="submit" class="p-6">
      <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
        <Icon name="PencilIcon" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
        Editar sucursal
      </h3>

      <!-- Nombre -->
      <div class="mb-4">
        <InputLabel value="Nombre" />
        <TextInput v-model="form.nombre" class="w-full mt-1" />
        <InputError :message="form.errors.nombre" />
      </div>

      <!-- Departamento -->
      <div class="mb-4">
        <InputLabel value="Departamento" />
        <TextInput v-model="form.departamento" class="w-full mt-1" />
        <InputError :message="form.errors.departamento" />
      </div>

      <!-- Provincia -->
      <div class="mb-4">
        <InputLabel value="Provincia" />
        <TextInput v-model="form.provincia" class="w-full mt-1" />
        <InputError :message="form.errors.provincia" />
      </div>

      <!-- Localidad -->
      <div class="mb-6">
        <InputLabel value="Localidad" />
        <TextInput v-model="form.localidad" class="w-full mt-1" />
        <InputError :message="form.errors.localidad" />
      </div>

      <!-- Botones -->
      <div class="flex items-center justify-end gap-2">
        <SecondaryButton type="button" @click="close">Cancelar</SecondaryButton>
        <PrimaryButton :disabled="form.processing">
          <Icon v-if="form.processing" name="ArrowPathIcon" class="w-5 h-5 animate-spin" />
          <span v-else>Guardar cambios</span>
        </PrimaryButton>
      </div>
    </form>
  </Modal>
</template>