<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import InputError from '@/Components/InputError.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({ show: Boolean, section: Object });
const emit = defineEmits(['close']);
const isMobile = computed(() => window.innerWidth < 768);

const form = useForm({
  id: '',
  nombre: '',
  descripcion: '',
});

watch(() => props.show, (val) => {
  if (val && props.section) {
    form.id = props.section.id;
    form.nombre = props.section.nombre;
    form.descripcion = props.section.descripcion;
  }
});

function submit() {
  form.put(route('settings.sections.update', props.section.id), {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  });
}
function close() { emit('close'); }
</script>

<template>
  <Modal :show="show" :closeable="true" @close="close" :max-width="isMobile?'full':'3xl'">
    <form @submit.prevent="submit" class="p-6">
      <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
        <Icon name="PencilIcon" class="w-6 h-6 text-indigo-600" />
        Editar sección
      </h3>

      <div class="mb-4">
        <InputLabel value="Nombre" />
        <TextInput v-model="form.nombre" class="w-full mt-1" />
        <InputError :message="form.errors.nombre" />
      </div>

      <div class="mb-6">
        <InputLabel value="Descripción" />
        <Textarea v-model="form.descripcion" class="w-full mt-1" />
        <InputError :message="form.errors.descripcion" />
      </div>

      <div class="flex items-center justify-end gap-2">
        <SecondaryButton @click="close">Cancelar</SecondaryButton>
        <PrimaryButton :disabled="form.processing">
          <Icon v-if="form.processing" name="ArrowPathIcon" class="w-5 h-5 animate-spin" />
          <span v-else>Guardar cambios</span>
        </PrimaryButton>
      </div>
    </form>
  </Modal>
</template>